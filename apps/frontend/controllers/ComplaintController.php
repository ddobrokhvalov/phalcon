<?php

namespace Multiple\Frontend\Controllers;
use Multiple\Frontend\Models\Applicant;
use Multiple\Frontend\Models\Category;
use Phalcon\Mvc\Controller;


class ComplaintController extends ControllerBase
{
    public function indexAction()
    {
        $applicant = new Applicant();
        $userApplicants = $applicant->findByUserId($this->user->id);

        $this->view->setTemplateAfter('menu');
        $this->view->applicants = $userApplicants;


    }
    public function editAction()
    {

    }
    public function addAction(){
        $applicant = new Applicant();
        $userApplicants = $applicant->findByUserId($this->user->id);

        $category = new Category();
        $arguments = $category->getArguments();
     
        $this->view->setTemplateAfter('menu');
        $this->view->applicants = $userApplicants;
        $this->view->arguments = $arguments;
    }

}