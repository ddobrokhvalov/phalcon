<?php

namespace Multiple\Backend\Controllers;

use Phalcon\Mvc\Controller;
use Multiple\Backend\Models\Admin;

class LoginController extends ControllerBase
{
	private function _registerSession($admin)
	{
		$this->session->set(
			'auth',
			array(
				'id'    => $admin->id,
				'email' => $admin->email,
			)
		);
	}
	public function startAction()
	{

		if ($this->request->isPost()) {


			$email    = $this->request->getPost('email');
			$password = $this->request->getPost('password');


			$admin = Admin::findFirst(
				array(
					"email = :email:  AND password = :password:",
					'bind' => array(
						'email'    => $email,
						'password' => sha1($password)
					)
				)
			);

			if ($admin != false) {

				$this->_registerSession($admin);


				return $this->dispatcher->forward(
					array(
						'controller' => 'dashboard',
						'action'     => 'index'
					)
				);
			}


		}


		return $this->dispatcher->forward(
			array(
				'controller' => 'login',
				'action'     => 'index'
			)
		);
	}

	public function indexAction()
	{

	}
}
