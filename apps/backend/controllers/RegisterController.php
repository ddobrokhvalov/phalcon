<?php

namespace Multiple\Backend\Controllers;
use Phalcon\Acl\Exception;
use Phalcon\Mvc\Controller;
use Multiple\Backend\Form\RegisterForm;
use Multiple\Backend\Validator\RegisterValidator;
use Multiple\Backend\Models\Admin;

class RegisterController extends Controller
{
    private $errorMessage;
    public function indexAction(){
        try{
            if($this->request->isPost()) {
                $data = $this->request->get();
                $validation = new RegisterValidator();
                $messages = $validation->validate($data);
                if(count($messages) > 0) {
                    foreach ($messages as $message) {
                        $this->flashSession->error($message->getMessage());
                    }
                }

                $admin = Admin::find("email = '{$data['email']}'");
                if (count($admin) > 0) throw new Exception('Error admin exists');

                if ($data['password'] == $data['confpassword']) {
                    $hashpassword = sha1($data['password']);
                    $admin = new Admin();
                    $admin->email = trim($data['email']);
                    $admin->phone = trim($data['phone']);
                    $admin->password = $hashpassword;
                    $admin->hashreg = sha1($data['email'] . $data['password'] . date('now'));
                    $admin->activated = 0;
                    $admin->date_reg = date('now');
                    $admin->save();
                    $this->mailer->send('', [
                    ], function($message) {
                        $message->to('vadim-antropov@ukr.net');
                        $message->subject('Test Email');
                    });
                }
            }


        } catch(Exception $e){
            $this->flashSession->error($e->getMessage());
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
