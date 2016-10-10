<?php

namespace Multiple\Frontend\Controllers;
use Phalcon\Acl\Exception;
use Phalcon\Mvc\Controller;
use Multiple\Frontend\Validator\RegisterValidator;
use Multiple\Frontend\Models\User;


class RegisterController extends Controller
{
    private $errorMessage;

    public function indexAction(){
        try{
            if($this->request->isPost()) {
                $data = $this->request->get();
                $host =  $this->request->getHttpHost();
                $validation = new RegisterValidator();
                $messages = $validation->validate($data);
                if(count($messages)){
                    $this->errorMessage = $messages;
                    throw new Exception();
                }
                if($data['password'] != $data['confpassword']) throw new Exception('Пароли не совпадают');

                $user = User::find("email = '{$data['email']}'");
                if (count($user)) {
                    echo json_encode(array('status' => 'user exists'));
                    exit;
                }

                $hashpassword = sha1($data['password']);
                $user = new User();
                $user->email = trim($data['email']);
                $user->phone = trim($data['phone']);
                $user->password = $hashpassword;
                $user->hashreg = sha1($data['email'] . $data['password'] . date('now'));
                $user->status = 2;
                $user->date_registration = date('now');
                $user->save();

                $message = $this->mailer->createMessageFromView('../views/emails/register', array(
                                'hashreg'   => $user->hashreg,
                                'host'      => $host
                            ))
                    ->to($user->email)
                    ->subject('Регистрация в интеллектуальной системе ФАС');
                //$message->cc('example_cc@gmail.com');
                //$message->bcc('example_bcc@gmail.com');
                $message->send();
                echo json_encode(array('status' => 'ok'));
            }
        } catch(Exception $e){
            if(count($messages)) {
                $temp_err = array();
                foreach ($messages as $message) {
                    $temp_err[] = $message->getMessage();
                }
                echo json_encode(array('error' => $temp_err ));
            } else {
                echo json_encode(array('error' => $e->getMessage()));
            }
        }
        exit;
    }

    public function confirmAction(){
        try{
            $data = $this->request->get();
            if(!isset($data['hashreg']) || trim($data['hashreg']) == '') throw new Exception('error hash registration');
            $admin = User::findFirst("hashreg='{$data['hashreg']}'");
            if(!$admin) throw new Exception('Error does not exists admin');

            $admin->hashreg = null;
            $admin->status = 1;
            $admin->save();
        } catch (Exception $e){
            echo $e->getMessage();
            exit;
        }
    }
}
