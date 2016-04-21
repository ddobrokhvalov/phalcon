<?php

namespace Multiple\Backend\Controllers;

use Phalcon\Mvc\Controller;


class DashboardController extends ControllerBase
{

	public function indexAction()
	{
		$this->setMenu();

	}

}
