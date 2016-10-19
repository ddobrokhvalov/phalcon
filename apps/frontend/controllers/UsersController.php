<?php

namespace Multiple\Frontend\Controllers;

use Multiple\Frontend\Validator\EditUserValidator;
use Phalcon\Acl\Exception;
use Phalcon\Mvc\Controller;
use Multiple\Frontend\Models\User;
use Multiple\Frontend\Models\Messages;


class UsersController extends Controller
{

	public function indexAction()
	{
		echo '<br>', __METHOD__;
	}

    public function changePasswordAction(){
        try {
            $data = $this->request->getPost();
            $validation = new EditUserValidator();
            $user = User::findFirstById($this->session->get('auth')['id']);
            $messages = $validation->validate($data);
            if(!$user) throw new \Exception('not user');
            if(count($messages)) throw new \Exception('not user');

            $data['phone'] = $this->filter->sanitize($data['phone'], trim);
            $data['lastname'] = $this->filter->sanitize($data['lastname'], trim);
            $data['firstname'] = $this->filter->sanitize($data['firstname'], trim);
            $data['patronymic'] = $this->filter->sanitize($data['patronymic'], trim);
            $data['new_password'] = $this->filter->sanitize($data['new_password'], trim);
            $data['old_password'] = $this->filter->sanitize($data['old_password'], trim);
            $data['new_password_confirm'] = $this->filter->sanitize($data['new_password_confirm'], trim);
            $data['current_path'] = str_replace('public//', '', $data['current_path']);

            $user->phone = empty($data['phone']) ? $user->phone : $data['phone'];
            $user->lastname = empty($data['lastname']) ? $user->lastname : $data['lastname'];
            $user->firstname = empty($data['firstname']) ? $user->firstname : $data['firstname'];
            $user->patronymic = empty($data['patronymic']) ? $user->patronymic  : $data['patronymic'];
            $user->notifications = empty($data['notifications']) ? 0 : 1;

            if(!empty($data['current_path']) || $data['current_path'] == '/login/start' ){
                $data['current_path'] = '/complaint/index';
            }

            if(!empty($data['new_password']) && !empty($data['old_password'] && !empty($data['new_password_confirm']))) {
                if(strlen($data['new_password']) < 8)  throw new \Exception('Пароль менее 8 символов');
                if ($user->password == sha1($data['old_password'])) {
                    if ($data['new_password'] == $data['new_password_confirm']) {
                        $user->password = sha1($data['new_password']);
                    } else {
                        throw new \Exception('Непраильное подтверждние пароля');
                    }
                } else {
                    throw new \Exception('Неправильный старый пароль');
                }
            }
            $this->flashSession->success('Данные сохранены');
        } catch (\Exception $e){
            if(isset($messages) && count($messages)) {
                foreach ($messages as $message) {
                    $this->flashSession->error($message->getMessage());
                }
            } else {
                $this->flashSession->error($e->getMessage());
            }
        }
        $user->update();
        return $this->response->redirect($data['current_path']);
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
