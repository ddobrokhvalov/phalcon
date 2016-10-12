<?php

namespace Multiple\Frontend\Controllers;

use Phalcon\Mvc\Controller;
use Multiple\Library\TrustedLibrary;

class IndexController extends Controller
{

	public function indexAction()
	{
        $auth = $this->session->get('auth');
        $this->view->is_logged_in = $auth == FALSE ? /*TRUE : FALSE;*/ FALSE : TRUE;
	}
}
