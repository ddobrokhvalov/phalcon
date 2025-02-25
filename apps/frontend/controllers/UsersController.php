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
            
            if(trim($data['old_password']) =='')
                throw new FieldException('Неправильный старый пароль', 'old_password');
            
            if (!$user) throw new FieldException('not user', 'user');
            if (empty($data['new_password']) && empty($data['old_password'] && empty($data['new_password_confirm']))) {
                $validation = new EditUserValidator();
                $messages = $validation->validate($data);
                if (count($messages)) throw new MessageException($messages);
            }

            foreach ($data as $key => $value){
                $data[$key] =  $this->filter->sanitize($value, 'trim');
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
                        throw new FieldException('Неправильное подтверждние пароля', 'new_password_confirm');
                    }
                } else {
                    throw new FieldException('Неправильный старый пароль', 'old_password');
                }
            }
			
			if (!empty($data['old_password'] && empty($data['new_password']))) {
				throw new FieldException('Неправильный новый пароль', 'new_password');
			}
			
			if (!empty($data['old_password'] && empty($data['new_password_confirm']))) {
				throw new FieldException('Неправильное подтверждние пароля', 'new_password_confirm');
			}

            $message = $this->mailer->createMessageFromView('../views/emails/new_password', array(
                'host'      => $this->request->getHttpHost(),
                'password'  => $data['new_password']
            ))
                ->to($user->email)
                ->subject('Восстановление пароля в системе ФАС-Онлайн');
            $message->send();
            
            echo json_encode(array('status' => 'ok'));
        } catch (MessageException $messages){
            $temp_arr = array();
            foreach ($messages->getArrErrors() as $message) {
                $temp_arr[$message->getField()][] = $message->getMessage();
            }
            echo json_encode(array('error' => $temp_arr));
        } catch (FieldException $e){
            echo json_encode(array('error' => array( ($e->getField()=='password'?'new_password':$e->getField()) => $e->getMessage())));
        } finally{
            $user->phone = empty($data['phone']) ? $user->phone : $data['phone'];
            $user->conversion = empty($data['conversion']) ? $user->conversion : $data['conversion'];
            $user->mobile_phone = empty($data['mobile_phone']) ? $user->mobile_phone : $data['mobile_phone'];
            $user->notifications = empty($data['notifications']) ? 0 : 1;
            $user->update();
            exit;
        }
    }
	
	public function changeSettingsAction()
    {
        try {
            $data = $this->request->getPost();
            $user = User::findFirstById($this->session->get('auth')['id']);
            $data['current_path'] = str_replace('public//', '', $data['current_path']);
            
            if (!$user) throw new FieldException('not user', 'user');
            
            foreach ($data as $key => $value){
                $data[$key] =  $this->filter->sanitize($value, 'trim');
            }

            if (!empty($data['current_path']) || $data['current_path'] == '/login/start') {
                $data['current_path'] = '/complaint/index';
            }

            echo json_encode(array('status' => 'ok'));
        } catch (MessageException $messages){
            $temp_arr = array();
            foreach ($messages->getArrErrors() as $message) {
                $temp_arr[$message->getField()][] = $message->getMessage();
            }
            echo json_encode(array('error' => $temp_arr));
        } catch (FieldException $e){
            echo json_encode(array('error' => array( ($e->getField()=='password'?'new_password':$e->getField()) => $e->getMessage())));
        } finally{
            $user->phone = empty($data['phone']) ? $user->phone : $data['phone'];
            $user->conversion = empty($data['conversion']) ? $user->conversion : $data['conversion'];
            $user->mobile_phone = empty($data['mobile_phone']) ? $user->mobile_phone : $data['mobile_phone'];
            $user->notifications = empty($data['notifications']) ? 0 : 1;
            $user->update();
            exit;
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

    public function checkUserAction(){
        $user = User::findFirstById($this->session->get('auth')['id']);
        $res_arr = array();
        if(!$user->conversion){
            $res_arr['conversion'] = 'Как к вам обращаться, необходимо заполнить';
        }
        if(!$user->mobile_phone){
            $res_arr['mobile_phone'] = 'Мобильный телефон, необходимо заполнить ';
        }
        echo json_encode(['error' => $res_arr ]);
        exit;
    }

}
