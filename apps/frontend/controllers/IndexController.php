<?php

namespace Multiple\Frontend\Controllers;

use Phalcon\Mvc\Controller;

class IndexController extends Controller
{

	public function indexAction()
	{
		require(__DIR__."../../../library/TrustedLibrary/trusted/login/common.php");
		define("TRUSTED_LOGIN_PLUGIN_PATH", "https://net.trusted.ru/static/");
		$this->view->TRUSTED_LOGIN_PLUGIN_PATH = TRUSTED_LOGIN_PLUGIN_PATH;
		$this->view->TRUSTED_AUTH_REDIRECT_URI = TRUSTED_AUTH_REDIRECT_URI;
		$this->view->TRUSTED_LOGIN_CLIENT_ID = TRUSTED_LOGIN_CLIENT_ID;
		$this->view->TRUSTED_AUTH_WIDGET_REDIRECT_URI = TRUSTED_AUTH_WIDGET_REDIRECT_URI;
	}
}
