<?php

namespace Multiple\Frontend\Controllers;


use Multiple\Frontend\Models\Applicant;
use Multiple\Frontend\Models\Category;
use Multiple\Frontend\Models\Complaint;
use Multiple\Frontend\Models\Question;
use Phalcon\Mvc\Controller;


class ComplaintController extends ControllerBase
{
    public function indexAction()
    {
        $this->setMenu();
        $complaint = new Complaint();
        $status = 0;
        $applicant_id = 0;
        if(isset($_GET['applicant_id']))
            $applicant_id = $_GET['applicant_id'];

        if (isset($_GET['status']))
            $status = $_GET['status'];
        $complaints = $complaint->findUserComplaints($this->user->id, $status,$applicant_id);
        $this->view->complaints = $complaints;
        $this->view->status = $status;

        if($applicant_id){
            $this->view->selected_applicant_id = $applicant_id;
        }
        $this->view->index_action = true;

    }

    public function editAction($id)
    {
        $complaint = Complaint::findFirstById($id);
        if(!$complaint)
            return $this->forward('complaint/index');
        if(!$complaint->checkComplaintOwner($id, $this->user->id))
            return $this->forward('complaint/index');


        $question = new Question();
        $complaintQuestion = $question->getComplainQuestionAndAnswer($id);


        $this->setMenu();

        
        $this->view->complaint = $complaint;
        $this->view->complaint_question = $complaintQuestion;

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
           echo $complaint->id;
        else
            echo 'error save';
        exit;

    }

    public function statusAction()
    {
        if (!$this->request->isPost()) {
            echo 'error'; exit;
        }
        $data = $this->request->getPost();
        $complaint = new Complaint();
        $result = $complaint->changeStatus($data['status'],json_decode($data['complaints']),$this->user->id);
        echo $result; exit;


    }

}