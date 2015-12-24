<?php

namespace Multiple\Backend\Controllers;

use Phalcon\Mvc\Controller;
use Multiple\Backend\Models\Index as Index;
use Multiple\Library\TestLib as TestLib;
class IndexController extends Controller
{

	public function indexAction()
	{
		return $this->response->forward('login');
	}
	public function testAction(){

         $data = Index::findFirst(5);
		var_dump($data->name);
		/*foreach($data as $v){
			var_dump($v);
		} */

		$obj = new TestLib();
		echo '<pre>';
		//var_dump($this);
		echo '</pre>';
		$obj->f();
		echo 'done';
		exit;
	}
}
