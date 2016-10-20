<?php

namespace Multiple\Frontend\Controllers;
use Phalcon\Acl\Exception;
use Phalcon\Mvc\Controller;
use Multiple\Frontend\Validator\RegisterValidator;
use Multiple\Frontend\Models\User;
use Multiple\Library\ReCaptcha;

class RegisterController extends Controller
{
    public function indexAction(){
        try{
            if($this->request->isPost()) {
                $data = $this->request->getPost();
                if (empty($data['g-recaptcha-response'])) throw new Exception('Ошибка каптчи');
                $captcha = ReCaptcha::chechCaptcha($data);
                if (empty($captcha) && !$captcha->success) throw new Exception('Ошибка каптчи');

                if(empty($data['password'])) throw new Exception('Введите пароль');

                $host =  $this->request->getHttpHost();
                $validation = new RegisterValidator();
                $messages = $validation->validate($data);
                if(count($messages))  throw new Exception('error');

                $user = User::find("email = '{$data['email']}'");
                if (count($user)) {
                    echo json_encode(array('error' => array('user' => 'Пользователь с таким email уже есть')));
                    exit;
                }

                $hashpassword = sha1($data['password']);
                $user = new User();
                $user->email = trim($data['email']);
                $user->password = $hashpassword;
                $user->hashreg = sha1($data['email'] . $data['password'] . date('now'));
                $user->status = 2;
                $user->date_registration = date('Y-m-d H:i:s');
                $user->save();

                $message = $this->mailer->createMessageFromView('../views/emails/order', array(
                                'hashreg'   => $user->hashreg,
                                'host'      => $host
                            ))
                    ->to($user->email)
                    ->subject('Регистрация в интеллектуальной системе ФАС');
                $message->send();
                echo json_encode(array('status' => 'ok'));
                exit;
            }
        } catch(Exception $e){
            $temp_err = array();
            if(isset($messages) && count($messages)) {
                foreach ($messages as $message) {
                    $temp_err[$message->getField()][] = $message->getMessage();
                }
            } else {
                $temp_err['errors'] = $e->getMessage();
            }
            echo json_encode(array('error' => $temp_err));
        }
        exit;
    }

    public function confirmAction(){
        try{
            $data = $this->request->get();
            if(!isset($data['hashreg']) || trim($data['hashreg']) == '') throw new Exception('error hash registration');
            $user = User::findFirst("hashreg='{$data['hashreg']}'");
            if(!$user) throw new Exception('Error does not exists user or user already activate');

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
        } catch (Exception $e){
            echo $e->getMessage();
            $this->response->redirect('/');
            exit;
        }
    }

    public function recoverypassAction(){
        try{
            if ($this->request->isPost()) {
                $email = $this->request->getPost('email');
                if(!isset($email) || trim($email) == '') throw new Exception('error email');
                $user = User::findFirst(array("email = {$email}"));
                if(!$user) throw new Exception('error user');
                $user->hashrecovery = sha1($user->email + date('Y-m-d H:i:s'));
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
                if(!isset($hashrecoverypass) || trim($hashrecoverypass) == '') throw new Exception('error hash');
                $user = User::findFirst(array("hashrecovery='{$hashrecoverypass}'"));
                if(!$user) throw new Exception('error user');
                $password = $this->random_password();
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
                //echo json_encode(array('status' => 'ok'));
                //exit;

            }
        } catch (Exception $e){
            echo json_encode(array('status' => $e->getMessage()));
            exit;
        }
        $this->response->redirect('/');
    }

    private function random_password($chars = 9) {
        $letters = 'abcefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        return substr(str_shuffle($letters), 0, $chars);
    }
}
