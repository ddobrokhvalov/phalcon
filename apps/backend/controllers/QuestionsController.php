<?php
namespace Multiple\Backend\Controllers;

use Phalcon\Mvc\Controller;

use Phalcon\Paginator\Adapter\Model as Paginator;


class QuestionsController extends ControllerBase
{

    public function indexAction()
    {
        $this->setMenu();
    }
}