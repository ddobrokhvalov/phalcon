<?php

namespace Multiple\Frontend\Controllers;

use Multiple\Frontend\Models\Applicant;
use Multiple\Frontend\Models\Category;
use Multiple\Frontend\Models\Complaint;
use Phalcon\Mvc\Controller;


class ComplaintController extends ControllerBase
{
    public function indexAction()
    {
        $applicant = new Applicant();
        $userApplicants = $applicant->findByUserId($this->user->id);

        $this->view->setTemplateAfter('menu');
        $this->view->applicants = $userApplicants;

        $complaint = new Complaint();
        $complaints = $complaint->findUserComplaints($this->user->id);

        $this->view->complaints = $complaints;

    }

    public function editAction()
    {

    }

    public function createAction()
    {
        if (!$this->request->isPost()) {
            return $this->forward('complaint/add');
        }
        $data = $this->request->getPost();
        $complaint = new Complaint();

        $complaint->addComplaint($data);
        if ($complaint->save())
            echo 'done';
        else
            echo 'error';
        exit;

    }

    public function addAction()
    {
        $applicant = new Applicant();
        $userApplicants = $applicant->findByUserId($this->user->id);

        $category = new Category();
        $arguments = $category->getArguments();

        $this->view->setTemplateAfter('menu');
        $this->view->applicants = $userApplicants;
        $this->view->arguments = $arguments;
    }

}