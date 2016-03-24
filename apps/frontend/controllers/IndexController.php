<?php

namespace Multiple\Frontend\Controllers;

use Phalcon\Mvc\Controller;
use Multiple\Library\TrustedLibrary;

class IndexController extends Controller
{

	public function indexAction()
	{
		TrustedLibrary::trusted_library_init();
		$this->view->TRUSTED_LOGIN_PLUGIN_PATH = TRUSTED_LOGIN_PLUGIN_PATH;
		$this->view->TRUSTED_AUTH_REDIRECT_URI = TRUSTED_AUTH_REDIRECT_URI;
		$this->view->TRUSTED_LOGIN_CLIENT_ID = TRUSTED_LOGIN_CLIENT_ID;
		$this->view->TRUSTED_AUTH_WIDGET_REDIRECT_URI = TRUSTED_AUTH_WIDGET_REDIRECT_URI;
	}
}
