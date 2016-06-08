<?php
namespace Multiple\Backend\Controllers;

use Phalcon\Mvc\Controller;

class AccessController extends ControllerBase
{

    public function deniedAction(){
        $this->view->pick("access/denied");
        $this->setMenu();
    }
}