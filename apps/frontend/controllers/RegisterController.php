<?php

namespace Multiple\Backend\Controllers;
use Phalcon\Acl\Exception;
use Phalcon\Mvc\Controller;
use Multiple\Frontend\Validator\RegisterValidator;
use Multiple\Backend\Models\User;


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

                $admin = Admin::find("email = '{$data['email']}'");
                if (count($admin)) {
                    echo json_encode(array('status' => 'admin exists'))
                    exit;
                }

                $hashpassword = sha1($data['password']);
                $admin = new Admin();
                $admin->email = trim($data['email']);
                $admin->phone = trim($data['phone']);
                $admin->password = $hashpassword;
                $admin->hashreg = sha1($data['email'] . $data['password'] . date('now'));
                $admin->status = 2;
                $admin->date_reg = date('now');
                $admin->save();

                $message = $this->mailer->createMessageFromView('../views/emails/register', array(
                                'hashreg'   => $hashpassword,
                                'host'      => $host
                            ))
                    ->to('example_to@gmail.com', 'OPTIONAL NAME')
                    ->subject('Hello world!');
                $message->cc('example_cc@gmail.com');
                $message->bcc('example_bcc@gmail.com');
                $message->send();
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
            exit;
        }
    }

    public function confirmAction(){
        try{
            $data = $this->request->get();
            if(!isset($data['hashreg']) || trim($data['hashreg']) == '') throw new Exception('error hash registration');
            $admin = Admin::findFirst("hashreg='{$data['hashreg']}'");
            if(!$admin) throw new Exception('Error does not exists admin');

            $admin->hashreg = null;
            $admin->activeted = 1;
            $admin->save();
        } catch (Exception $e){
            echo $e->getMessage();
            exit;
        }
    }
}
