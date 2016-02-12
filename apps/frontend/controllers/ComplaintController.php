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
        $this->setMenu();
        $complaint = new Complaint();
        $status = false;
        if (isset($_GET['status']))
            $status = $_GET['status'];
        $complaints = $complaint->findUserComplaints($this->user->id, $status);
        $this->view->complaints = $complaints;

    }

    public function editAction()
    {
        $this->setMenu();
    }


    public function addAction()
    {
        $this->setMenu();
        $category = new Category();
        $arguments = $category->getArguments();
        $this->view->arguments = $arguments;
    }
    public function createAction()
    {
        if (!$this->request->isPost()) {
            echo 'error'; exit;
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

    public function statusAction()
    {

        if (!$this->request->isPost()) {
            echo 'error'; exit;
        }
        $data = $this->request->getPost();
        $complaint = new Complaint();
        $complaint->changeStatus($data['status'],json_decode($data['complaints']),$this->user->id);
        echo 'done'; exit;
        /*if ($complaint->save())
            echo 'done';
        else
            echo 'error';
        exit;*/

    }

}