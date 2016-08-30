<?php
namespace Multiple\Backend\Controllers;

use Phalcon\Mvc\Controller;




class ErrorsController extends ControllerBase
{
    public function show404Action()
    {
        $this->setMenu();
        //$this->response->setHeader('HTTP/1.0 404','Not Found');
        // $this->response->setHeader(404, 'Not Found'); <- did not work
    }
}