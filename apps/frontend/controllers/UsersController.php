<?php

namespace Multiple\Frontend\Controllers;

use Multiple\Frontend\Validator\EditUserValidator;
use Phalcon\Acl\Exception;
use Phalcon\Mvc\Controller;
use Multiple\Frontend\Models\User;
use Multiple\Frontend\Models\Messages;
use Multiple\Library\Exceptions\MessageException;
use Multiple\Library\Exceptions\FieldException;


class UsersController extends Controller
{

	public function indexAction()
	{
		echo '<br>', __METHOD__;
	}

    public function changePasswordAction()
    {
        try {
            $data = $this->request->getPost();
            $user = User::findFirstById($this->session->get('auth')['id']);
            $data['current_path'] = str_replace('public//', '', $data['current_path']);
            if (!$user) throw new FieldException('not user', 'user');
            if (empty($data['new_password']) && empty($data['old_password'] && empty($data['new_password_confirm']))) {
                $validation = new EditUserValidator();
                $messages = $validation->validate($data);
                if (count($messages)) throw new MessageException($messages);
            }

            foreach ($data as $key){
                $data[$key] =  $this->filter->sanitize($data[$key], trim);
            }

            if (!empty($data['current_path']) || $data['current_path'] == '/login/start') {
                $data['current_path'] = '/complaint/index';
            }

            if (!empty($data['new_password']) && !empty($data['old_password'] && !empty($data['new_password_confirm']))) {
                if (strlen($data['new_password']) < 8) throw new FieldException('Пароль менее 8 символов', 'password');
                if ($user->password == sha1($data['old_password'])) {
                    if ($data['new_password'] == $data['new_password_confirm']) {
                        $user->password = sha1($data['new_password']);
                    } else {
                        throw new FieldException('Непраильное подтверждние пароля', 'confpassword');
                    }
                } else {
                    throw new FieldException('Неправильный старый пароль', 'oldpassword');
                }
            }
            $this->flashSession->success('Данные сохранены');

        } catch (MessageException $messages){
            foreach ($messages->getArrErrors() as $message) {
                $this->flashSession->error($message->getMessage());
            }
        } catch (FieldException $e){
            $this->flashSession->error($e->getMessage());
        } finally{
            $user->phone = empty($data['phone']) ? $user->phone : $data['phone'];
            $user->lastname = empty($data['lastname']) ? $user->lastname : $data['lastname'];
            $user->firstname = empty($data['firstname']) ? $user->firstname : $data['firstname'];
            $user->patronymic = empty($data['patronymic']) ? $user->patronymic : $data['patronymic'];
            $user->notifications = empty($data['notifications']) ? 0 : 1;
            $user->update();
            return $this->response->redirect($data['current_path']);
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
