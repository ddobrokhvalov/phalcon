<?php

namespace Multiple\Frontend\Controllers;

use Phalcon\Mvc\Controller;

class IndexController extends Controller
{

	public function indexAction()
	{
        if(isset($_GET["register"])){
			$this->view->register = true;
		}
		$auth = $this->session->get('auth');
        $this->view->is_logged_in = $auth == FALSE ? /*TRUE : FALSE;*/ FALSE : TRUE;
	}
}
