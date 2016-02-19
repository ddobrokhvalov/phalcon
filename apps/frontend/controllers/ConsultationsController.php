<?php

namespace Multiple\Frontend\Controllers;


use Phalcon\Mvc\Controller;


class ConsultationsController extends ControllerBase
{
    public function indexAction()
    {

    }

    public function addquestionAction()
    {
        if (!$this->request->isPost()) {
            echo 'error';
            exit;
        }


    }

}

