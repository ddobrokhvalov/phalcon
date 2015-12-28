<?php

namespace Multiple\Frontend\Controllers;

use Phalcon\Mvc\Controller;
use Multiple\Library\TestLib as TestLib;

class IndexController extends Controller
{

	public function indexAction()
	{
		//$obj = new TestLib();
		//$obj->f();
       echo 'done';
		exit;
	}
}
