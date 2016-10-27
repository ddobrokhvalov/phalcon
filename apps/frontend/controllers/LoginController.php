<?php

namespace Multiple\Frontend\Controllers;

use Phalcon\Mvc\Controller;
use Multiple\Frontend\Models\User;
use Multiple\Library\Log;
use Multiple\Frontend\Validator\LoginValidator;

class LoginController extends Controller
{
    private function _registerSession($user) {
        $this->session->set(
            'auth',
            array(
                'id' => $user->id,
                'email' => $user->email,
            )
        );
    }

    public function logoutAction(){
        $auth = $this->session->get('auth');

        if (!$auth) {
            $user_id = false;
        } else {
            $user_id = $auth['id'];
        }
        if ($user_id) {
            $user = User::findFirst(
                array(
                    "id = :id:",
                    'bind' => array(
                        'id' => $user_id,
                    )
                )
            );
        }
        if ($auth)
            $this->session->destroy();
        return $this->response->redirect('/');
        //header( 'Location: http://'.$_SERVER['HTTP_HOST'] );
    }

    public function startAction() {
        if ($this->request->isPost()) {
            $email = $this->request->getPost('email');
            $password = $this->request->getPost('password');

            $validation = new LoginValidator();
            $messages = $validation->validate($this->request->getPost());
            if(count($messages) > 0){
                foreach ($messages as $key){
                    echo json_encode(array('error' => array($key->getField() => $key->getMessage())));
                    exit;
                }
            }

            $checkEmail = User::findFirst(array(
                    "email = :email: ",
                    'bind' => array(
                        'email' => $email,
                    )
                )
            );
            if(!$checkEmail){
                echo json_encode(array('error' => array('email' => 'Пользователь с таким email не зарегистрирован')));
                exit;
            }

            $user = User::findFirst(
                array(
                    "email = :email:  AND password = :password:",
                    'bind' => array(
                        'email' => $email,
                        'password' => sha1($password)
                    )
                )
            );
            if ($user != false) {
                if($user->status == 0){
                    echo json_encode(array('error' => array('email' => 'Ваш аккаунт заблокирован. Для разблокировки обратитесь к администратору по номеру телефона или E-mail')));
                    exit;
                }
                if($user->status == 2){
                    echo json_encode(array('error' => array('email' => 'Вы не активировали аккаунт.')));
                    exit;
                }
                $this->_registerSession($user);
                echo json_encode(array('status' => 'ok'));
                exit;
            }  else {
                echo json_encode(array('error' => array('email' => 'Имя пользователя или пароль введен не верно')));
                exit;
            }
        }
//        return $this->dispatcher->forward(
//            array(
//                'controller' => 'login',
//                'action' => 'index'
//            )
//        );


        return $this->response->redirect('complaint/index');
    }

    public function authorizeAction()
    {

    }

    public function indexAction()
    {
       return $this->response->redirect('/');
    }
}
