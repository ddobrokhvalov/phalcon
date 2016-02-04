<?php
namespace Multiple\Frontend\Controllers;

use Phalcon\Mvc\Controller;


class ApplicantController extends ControllerBase
{
    public function indexAction()
    {
        $this->view->setTemplateAfter('menu');
    }
    public function editAction()
    {

    }
    public function addAction()
    {
        $this->view->setTemplateAfter('menu');
    }
    public function createAction()
    {
        echo '<pre>';
        var_dump($_GET);
        echo '<hr>';
        var_dump($_POST);
        echo '<hr>';
        var_dump($_FILES);
        echo '/<pre>';


    }

}