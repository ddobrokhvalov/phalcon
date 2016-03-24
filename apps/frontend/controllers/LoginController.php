<?php

namespace Multiple\Frontend\Controllers;

use Phalcon\Mvc\Controller;
use Multiple\Frontend\Models\User;
use Multiple\Library\Log;
use Multiple\Library\TrustedLibrary;

class LoginController extends Controller
{
    private function _registerSession($user)
    {
        $this->session->set(
            'auth',
            array(
                'id' => $user->id,
                'email' => $user->email,
            )
        );
    }

    public function logoutAction()
    {
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
        header( 'Location: http://'.$_SERVER['HTTP_HOST'] );

    }

    public function startAction()
    {

        if ($this->request->isPost()) {


            $email = $this->request->getPost('email');
            $password = $this->request->getPost('password');


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

                $this->_registerSession($user);


                return $this->dispatcher->forward(
                    array(
                        'controller' => 'complaint',
                        'action' => 'index'
                    )
                );
            }


        }


        return $this->dispatcher->forward(
            array(
                'controller' => 'login',
                'action' => 'index'
            )
        );
    }

    public function authorizeAction()
    {
        require(__DIR__."../../../library/TrustedLibrary/trusted/settings.php");
        global $DB;
        require(__DIR__."../../../library/TrustedLibrary/trusted/login/authorize.php");
        exit();
    }

    public function indexAction()
    {

    }
}
