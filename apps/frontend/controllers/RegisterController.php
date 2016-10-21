<?php

namespace Multiple\Frontend\Controllers;
use Phalcon\Mvc\Controller;
use Multiple\Frontend\Validator\RegisterValidator;
use Multiple\Frontend\Models\User;
use Multiple\Library\ReCaptcha;
use Multiple\Library\MessageException;
use Multiple\Library\Exceptions\RegisterException;
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
                echo json_encode(array('status' => 'ok'));
                exit;
            }
        } catch (MessageException $messages){
            $temp_err = array();
            foreach ($messages->getArrErrors() as $message) {
                $temp_err[$message->getField()][] = $message->getMessage();
            }
            echo json_encode(array('error' => $temp_err));
        } catch(RegisterException $message){
            echo json_encode(array('error' => array($message->getField() => $message->getMessage())));
        }
        exit;
    }

    public function confirmAction(){
        try{
            $data = $this->request->get();
            if(empty($data['hashreg']) || trim($data['hashreg']) == '') throw new RegisterException('error hash registration', 'hash');
            $user = User::findFirst("hashreg='{$data['hashreg']}'");
            if(!$user) throw new RegisterException('Error does not exists user or user already activate', 'user');

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
            $this->response->redirect('/');
        } catch (RegisterException $e){
            echo $e->getMessage();
            $this->response->redirect('/');
            exit;
        }
    }

    public function recoverypassAction(){
        try{
            $random = new Random();
            if ($this->request->isPost()) {
                $email = $this->request->getPost('email');
                if(empty($email) || trim($email) == '') throw new RegisterException('error email', 'email');
                $user = User::findFirst(array("email='{$email}'"));
                if(!$user) throw new UserException('error user');

                $user->hashrecovery = $random->uuid();
                $user->save();

                $message = $this->mailer->createMessageFromView('../views/emails/recovery', array(
                    'hashrecovery'   => $user->hashrecovery,
                    'host'      => $this->request->getHttpHost()
                ))
                    ->to($user->email)
                    ->subject('Восстановление пароля в системе ФАС');
                $message->send();
                echo json_encode(array('status' => 'ok'));
                exit;
            } else if($this->request->isGet()){
                $hashrecoverypass = $this->request->get('recovery');
                if(!isset($hashrecoverypass) || trim($hashrecoverypass) == '') throw new RegisterException('hash error', 'hash');
                $user = User::findFirst(array("hashrecovery='{$hashrecoverypass}'"));
                if(!$user) throw new RegisterException('error user', 'user');

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
            }
        } catch (RegisterException $e){
            echo json_encode(array('status' => $e->getMessage()));
            exit;
        }
        $this->response->redirect('/');
    }

    private function checkUser( $data ){
        $validation = new RegisterValidator();
        if(empty($data['offerta'])) throw new RegisterException('Не подтвердили условия оферты', 'offerta');
        if (empty($data['g-recaptcha-response'])) throw new RegisterException('Не ввели каптчу', 'captcha');

        $captcha = ReCaptcha::chechCaptcha($data);
        if (empty($captcha) && !$captcha->success) throw new RegisterException('Ошибка проверки каптчи', 'captcha');

        $messages = $validation->validate($data);
        if(count($messages))  throw new MessageException($messages);
        if($data['password'] != $data['confpassword']) throw new RegisterException('Пароли не совпадают', 'confpassword');

        $user = User::find("email = '{$data['email']}'");
        if (count($user)) throw new RegisterException('Пользователь с таким email уже есть', 'user');

    }
}
