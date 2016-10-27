<?php

namespace Multiple\Frontend\Controllers;
use Phalcon\Mvc\Controller;
use Multiple\Frontend\Validator\RegisterValidator;
use Multiple\Frontend\Models\User;
use Multiple\Library\ReCaptcha;
use Multiple\Library\Exceptions\MessageException;
use Multiple\Library\Exceptions\FieldException;
use Phalcon\Security\Random;

class RegisterController extends Controller
{
    public function indexAction(){
        try{
            if($this->request->isPost()) {
                $random = new Random();
                $data = $this->request->getPost();
                $this->checkUser( $data );

                $user = new User();
                $user->email = trim($data['email']);
                $user->password = sha1($data['password']);
                $user->hashreg = $random->uuid();
                $user->status = 2;
                $user->date_registration = date('Y-m-d H:i:s');
                $user->save();

                $message = $this->mailer->createMessageFromView('../views/emails/register', array(
                                'hashreg'   => $user->hashreg,
                                'host'      => $this->request->getHttpHost()
                            ))
                    ->to($user->email)
                    ->subject('Регистрация в интеллектуальной системе ФАС');
                $message->send();
                echo json_encode(array(
                    'status' => 'ok',
                    'email' => $user->email
                ));
                exit;
            }
        } catch (MessageException $messages){
            $temp_err = array();
            foreach ($messages->getArrErrors() as $message) {
                $temp_err[$message->getField()][] = $message->getMessage();
            }
            echo json_encode(array('error' => $temp_err));
        } catch(FieldException $message){
            echo json_encode(array('error' => array($message->getField() => $message->getMessage())));
        }
        exit;
    }

    public function confirmAction(){
        try{
            $data = $this->request->get();
            if(empty($data['hashreg']) || trim($data['hashreg']) == '') throw new FieldException('Нет хэша подтверждения регистрации', 'hash');
            $user = User::findFirst("hashreg='{$data['hashreg']}'");
            if(!$user) throw new FieldException('Такого пользователя нет или он уже активирован!', 'user');

            $user->hashreg = null;
            $user->status = 1;
            $user->save();

            $message = $this->mailer->createMessageFromView('../views/emails/confirm', array(
                'host'  => $this->request->getHttpHost(),
                'login' => $user->email,
                'name'  => $user->firstname
            ))
                ->to($user->email)
                ->subject('Подтверждение в интеллектуальной системе ФАС');
            $message->send();
            $this->response->redirect('/?success=confirm');
        } catch (FieldException $e){
            $this->flashSession->error($e->getMessage());
            //echo $e->getMessage();
            $this->response->redirect('/');
            //exit;
        }
    }

    public function recoverypassAction(){
        try{
            $random = new Random();
            if ($this->request->isPost()) {
                $email = $this->request->getPost('email');
                if(empty($email) || trim($email) == '') throw new FieldException('error email', 'email');
                $user = User::findFirst(array("email='{$email}'"));
                if(!$user) throw new FieldException('Пользователя с таким email нет');

                $user->hashrecovery = $random->uuid();
                $user->save();

                $message = $this->mailer->createMessageFromView('../views/emails/recovery', array(
                    'hashrecovery'   => $user->hashrecovery,
                    'host'      => $this->request->getHttpHost()
                ))
                    ->to($user->email)
                    ->subject('Восстановление пароля в системе ФАС');
                $message->send();
                echo json_encode(array(
                    'status' => 'ok',
                    'email' => $email
                ));
                exit;
            } else if($this->request->isGet()){
                $hashrecoverypass = $this->request->get('recovery');
                if(!isset($hashrecoverypass) || trim($hashrecoverypass) == '') throw new FieldException('hash error', 'hash');
                $user = User::findFirst(array("hashrecovery='{$hashrecoverypass}'"));
                if(!$user) throw new FieldException('error user', 'user');

                $password = $random->hex(8);
                $user->hashrecovery = null;
                $user->password = sha1($password);
                $user->save();

                $message = $this->mailer->createMessageFromView('../views/emails/new_password', array(
                    'host'      => $this->request->getHttpHost(),
                    'password'  => $password
                ))
                    ->to($user->email)
                    ->subject('Восстановление пароля в системе ФАС');
                $message->send();
                $this->response->redirect('/?success=recovery');
            }
        } catch (FieldException $e){
            $this->flashSession->error($e->getMessage());
            //echo $e->getMessage();
            $this->response->redirect('/');
        }
        //$this->response->redirect('/');
    }

    public function callback(){
        if ($this->request->isPost()) {
            $phone = $this->request->getPost('phone');
            if(preg_match('/^((8|\+7)[\- ]?)?(\(?\d{3}\)?[\- ]?)?[\d\- ]{7,10}$/', $phone)){
                $message = $this->mailer->createMessageFromView('../views/emails/callback', array(
                    'host'      => $this->request->getHttpHost(),
                    'password'  => $phone
                ))
                    ->to($this->adminsEmails['order'])
                    ->subject('Обратный звонок');
                $message->send();
                echo json_encode(array('status' => 'ok'));
            } else {
                echo json_encode(array('error' => 'Некорректный телефон'));
            }
        }
        exit;
    }

    private function checkUser( $data ){
        $validation = new RegisterValidator();
        if(empty($data['offerta'])) throw new FieldException('Не подтвердили условия оферты', 'offerta');
        if (empty($data['g-recaptcha-response'])) throw new FieldException('Не ввели каптчу', 'captcha');

        $captcha = ReCaptcha::chechCaptcha($data);
        if (empty($captcha) && !$captcha->success) throw new FieldException('Ошибка проверки каптчи', 'captcha');

        $messages = $validation->validate($data);
        if(count($messages))  throw new MessageException($messages);
        if($data['password'] != $data['confpassword']) throw new FieldException('Пароли не совпадают', 'confpassword');

        $user = User::find("email = '{$data['email']}'");
        if (count($user)) throw new FieldException('Пользователь с таким email уже есть', 'email');

    }
}
