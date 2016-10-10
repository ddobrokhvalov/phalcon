<?php

namespace Multiple\Backend\Controllers;
use Phalcon\Acl\Exception;
use Phalcon\Mvc\Controller;
use Multiple\Backend\Form\RegisterForm;
use Multiple\Backend\Validator\RegisterValidator;
use Multiple\Backend\Models\Admin;
use Phalcon\Mvc\Url;


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
                if($data['password'] != $data['confpassword']) throw new Exception('Passwords are not equal');

                $admin = Admin::find("email = '{$data['email']}'");
                if (count($admin)) {
                    $this->view->admin_exists = true;
                    $this->view->host = $host;
                }

                $hashpassword = sha1($data['password']);
                $admin = new Admin();
                $admin->email = trim($data['email']);
                $admin->phone = trim($data['phone']);
                $admin->password = $hashpassword;
                $admin->hashreg = sha1($data['email'] . $data['password'] . date('now'));
                $admin->activated = 0;
                $admin->date_reg = date('now');
                $admin->save();

                $message = $this->mailer->createMessageFromView('../views/emails/register', array(
                                'hashreg'   => $admin->hashreg,
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
                foreach ($messages as $message) {
                    $this->flashSession->error($message->getMessage());
                }
            } else {
                $this->flashSession->error($e->getMessage());
            }
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
