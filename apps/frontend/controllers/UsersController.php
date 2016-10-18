<?php

namespace Multiple\Frontend\Controllers;

use Phalcon\Mvc\Controller;
use Multiple\Frontend\Models\User;
use Multiple\Frontend\Models\Messages;

class UsersController extends Controller
{

	public function indexAction()
	{
		echo '<br>', __METHOD__;
	}


    public function changePasswordAction() {
        $notification = $this->request->getPost('notifications');
        $firstname = $this->request->getPost('firstname');
        $lastname = $this->request->getPost('lastname');
        $patronymic = $this->request->getPost('patronymic');
        $phone = $this->request->getPost('phone');
        $user = User::findFirstById($this->session->get('auth')['id']);


        $user->notifications = ($notification == 1) ? 1 : 0;
        $user->firstname = (isset($firstname) && trim($firstname) != '') ? trim($firstname) : $user->firstname;
        $user->lastname = (isset($lastname) && trim($lastname) != '') ? trim($lastname) : $user->lastname;
        $user->patronymic = (isset($patronymic) && trim($patronymic) != '') ? trim($patronymic) : $user->patronymic;
        $user->phone = (isset($phone) && trim($phone) != '' && preg_match('/^((8|\+7)[\- ]?)?(\(?\d{3}\)?[\- ]?)?[\d\- ]{7,10}$/', $phone)) ? trim($phone) : $user->phone;
        $user->update();

        $old_password = $this->request->getPost('old_password');
        $new_password = $this->request->getPost('new_password');
        $new_password_confirm = $this->request->getPost('new_password_confirm');


        $redirect_to = $this->request->getPost('current_path');
        if (!isset($redirect_to) || !$redirect_to || $redirect_to == '/login/start') {
            $redirect_to = '/complaint/index';
        }
        $redirect_to = str_replace('public//', '', $redirect_to);

        if($new_password == '' && $old_password == '' && $new_password_confirm == ''){
            return $this->response->redirect($redirect_to);
        }

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

//                    $message = $this->mailer->createMessageFromView('../views/emails/edit_password', array(
//                        'hashreg'   => $user->hashreg,
//                        'host'      => $host
//                    ))
//                        ->to($user->email)
//                        ->subject('Регистрация в интеллектуальной системе ФАС');
//                    $message->send();
                    return $this->response->redirect($redirect_to);
                } else {
                    $this->flashSession->error('Старый пароль введен не верно');
                    return $this->response->redirect($redirect_to);
                }
            }
        }
    }

    public function setMessageReadAction() {
        $id = $this->request->getPost('message_id');
        if (isset($id) && $id) {
            $message = Messages::findFirstById($id);
            if ($message) {
                $message->is_read = 1;
                $message->update();
            }
            $data = "ok";
            $this->view->disable();
            echo json_encode($data);
        }
    }
}
