<?php

namespace Multiple\Frontend\Controllers;
use Phalcon\Acl\Exception;
use Phalcon\Mvc\Controller;
use Multiple\Frontend\Validator\RegisterValidator;
use Multiple\Frontend\Models\User;


class RegisterController extends Controller
{
    public function indexAction(){
        try{
            if($this->request->isPost()) {
                $data = $this->request->get();
                if($data['password'] != $data['confpassword']) throw new Exception('Пароли не совпадают');

                $host =  $this->request->getHttpHost();
                $validation = new RegisterValidator();
                $messages = $validation->validate($data);
                if(count($messages))  throw new Exception('error');

                $user = User::find("email = '{$data['email']}'");
                if (count($user)) {
                    echo json_encode(array('status' => 'user exists'));
                    exit;
                }

                $hashpassword = sha1($data['password']);
                $user = new User();
                $user->email = trim($data['email']);
                $user->firstname = trim($data['name']);
                $user->phone = trim($data['phone']);
                $user->password = $hashpassword;
                $user->hashreg = sha1($data['email'] . $data['password'] . date('now'));
                $user->status = 2;
                $user->date_registration = date('Y-m-d H:i:s');;
                $user->save();

                $message = $this->mailer->createMessageFromView('../views/emails/register', array(
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
                $temp_err['other'] = $e->getMessage();
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

            $this->response->redirect('/');
        } catch (Exception $e){
            echo $e->getMessage();
            $this->response->redirect('/');
            exit;
        }
    }
}
