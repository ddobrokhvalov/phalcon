<?php

namespace Multiple\Frontend\Controllers;

use Phalcon\Mvc\Controller;
use Multiple\Frontend\Models\User;

class UsersController extends Controller
{

	public function indexAction()
	{
		echo '<br>', __METHOD__;
	}

    public function changePasswordAction() {
        $redirect_to = $this->request->getPost('current_path');
        if (!isset($redirect_to) || !$redirect_to || $redirect_to == '/login/start') {
            $redirect_to = '/complaint/index';
        }
        $old_password = $this->request->getPost('old_password');
        $new_password = $this->request->getPost('new_password');
        $new_password_confirm = $this->request->getPost('new_password_confirm');
        if (!isset($old_password) || (isset($old_password)) && !strlen($old_password)) {
            $this->flashSession->error('Старый пароль не введен');
            return $this->response->redirect($redirect_to);
        }
        if (isset($new_password) && $new_password) {
            if ($new_password != $new_password_confirm) {
                $this->flashSession->error('Подтвердите ввод нового пароля');
                return $this->response->redirect($redirect_to);
            }
        } else {
            $this->flashSession->error('Новый пароль не может быть пустым');
            return $this->response->redirect($redirect_to);
        }
        if (isset($this->session->get('auth')['id'])) {
            $user = User::findFirstById($this->session->get('auth')['id']);
            if ($user) {
                if ($user->password == sha1($old_password)) {
                    $user->password = sha1($new_password);
                    $user->update();
                    $this->flashSession->success('Пароль изменен');
                    return $this->response->redirect($redirect_to);
                } else {
                    $this->flashSession->error('Старый пароль введен не верно');
                    return $this->response->redirect($redirect_to);
                }
            }
        }
    }
}
