<?php
namespace Multiple\Frontend\Controllers;
use Phalcon\Mvc\Controller;


class HelpController extends ControllerBase
{
    public function faqAction()
    {
        $this->setMenu();
    }
    public function contactAction()
    {
        $this->setMenu();
    }
    public function aboutAction()
    {
        $this->setMenu();
    }
}