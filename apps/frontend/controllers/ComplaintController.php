<?php

namespace Multiple\Frontend\Controllers;

use Phalcon\Mvc\Controller;


class ComplaintController extends ControllerBase
{
    public function indexAction()
    {
        $this->view->setTemplateAfter('menu');
    }
    public function editAction()
    {

    }

}