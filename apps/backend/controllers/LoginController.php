<?php

namespace Multiple\Backend\Controllers;

use Phalcon\Mvc\Controller;
use Multiple\Backend\Models\Admin;
use Multiple\Library\Log;

class LoginController extends ControllerBase
{
    private function _registerSession($admin)
    {
        $this->session->set(
            'auth',
            array(
                'id' => $admin->id,
                'email' => $admin->email,
            )
        );
    }

    public function logoutAction()
    {
        $auth = $this->session->get('auth');

        if (!$auth) {
            $admin_id = false;
        } else {
            $admin_id = $auth['id'];
        }
        if ($admin_id) {
            $admin = Admin::findFirst(
                array(
                    "id = :id:",
                    'bind' => array(
                        'id' => $admin_id,
                    )
                )
            );

            Log::addAdminLog(Log::$typeAdminAuth, Log::$textAdminLogout, $admin);
        }
        if ($auth)
            $this->session->destroy();
        return $this->dispatcher->forward(
            array(
                'controller' => 'login',
                'action' => 'index'
            )
        );

    }

    public function startAction()
    {
        if ($this->request->isPost()) {


            $email = $this->request->getPost('email');
            $password = $this->request->getPost('password');


            $admin = Admin::findFirst(
                array(
                    "email = :email:  AND password = :password:",
                    'bind' => array(
                        'email' => $email,
                        'password' => sha1($password)
                    )
                )
            );

            if ($admin != false) {
                $this->_registerSession($admin);

                Log::addAdminLog(Log::$typeAdminAuth, Log::$textAdminLogin, $admin);
                return $this->dispatcher->forward(
                    array(
                        'controller' => 'user',
                        'action' => 'afterLogin'
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

    public function indexAction()
    {
        if ($this->user) {
            return $this->dispatcher->forward(
                array(
                    'controller' => 'user',
                    'action' => 'afterLogin'
                )
            );
        }
    }
}
