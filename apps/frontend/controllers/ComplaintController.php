<?php

namespace Multiple\Frontend\Controllers;


use Multiple\Frontend\Models\Applicant;
use Multiple\Frontend\Models\Category;
use Multiple\Frontend\Models\Complaint;
use Multiple\Frontend\Models\Question;
use Phalcon\Mvc\Controller;
use \Phalcon\Paginator\Adapter\NativeArray as Paginator;
use Multiple\Library\PaginatorBuilder;
use Multiple\Library\TrustedLibrary;

class ComplaintController extends ControllerBase
{
    public function indexAction()
    {
        $this->setMenu();
        $complaint = new Complaint();
        $status = 0;
        $numberPage = $this->request->getQuery("page", "int");
        if($numberPage===null) $numberPage = 1;
        if (isset($_GET['status']))
            $status = $_GET['status'];
      
        $complaints = $complaint->findUserComplaints($this->user->id, $status, $this->applicant_id);
        $this->view->complaints = $complaints;
        $this->view->status = $status;
        $paginator = new Paginator(array(
            "data"  => $complaints,
            "limit" => 10,
            "page"  => $numberPage
        ));
        $pages = $paginator->getPaginate();
        $this->view->page = $pages;
        $this->view->paginator_builder = PaginatorBuilder::buildPaginationArray($numberPage, $pages->total_pages);
        $this->view->index_action = true;

    }

    public function editAction($id)
    {
        TrustedLibrary::trusted_library_init();
        $complaint = Complaint::findFirstById($id);
        if (!$complaint || !$complaint->checkComplaintOwner($id, $this->user->id))
            return $this->forward('complaint/index');
        if (isset($_SESSION['TRUSTEDNET']['OAUTH'])) $OAuth2 = unserialize($_SESSION['TRUSTEDNET']['OAUTH']);
        if (isset($OAuth2)){
            /*$token = $OAuth2->getAccessToken();
            if(!$OAuth2->checkToken())
                if($OAuth2->refresh())*/ $token = $OAuth2->getRefreshToken();
            $this->view->token  = $token;

        } else {
            $this->session->destroy();
            return $this->forward('/');
        }
        $complaint->purchases_name = str_replace("\r\n", " ", $complaint->purchases_name);
        $question = new Question();
        $complaintQuestion = $question->getComplainQuestionAndAnswer($id);
        $this->setMenu();
        $this->view->complaint = $complaint;
        $this->view->complaint_question = $complaintQuestion;
        $this->view->action_edit = false;
        if (isset($_GET['action']) && $_GET['action'] == 'edit' && $complaint->status =='draft')
            $this->view->action_edit = true;
    }


    public function addAction()
    {
        $this->setMenu();
        $category = new Category();
        $arguments = $category->getArguments();
        TrustedLibrary::trusted_library_init();
        if (isset($_SESSION['TRUSTEDNET']['OAUTH'])) $OAuth2 = unserialize($_SESSION['TRUSTEDNET']['OAUTH']);
        if (isset($OAuth2)){
            /*$token = $OAuth2->getAccessToken();
            if(!$OAuth2->checkToken())
                if($OAuth2->refresh())*/ $token = $OAuth2->getRefreshToken();
            $this->view->token  = $token;

        } else {
            $this->session->destroy();
            return $this->forward('/');
        }


        $this->view->arguments = $arguments;
    }

    public function createAction()
    {
        if (!$this->request->isPost()) {
            echo 'error';
            exit;
        }
        $data = $this->request->getPost();
        $complaint = new Complaint();

        $complaint->addComplaint($data);

        if ($complaint->save())
            $response = array('result' => 'success', 'id' => $complaint->id);
        else
            $response = array('result' => 'error', 'message' => 'Ошибка при попытке сохранения жалобы');
        echo json_encode($response);
        exit;
    }

    public function statusAction()
    {
        if (!$this->request->isPost()) {
            echo 'error';
            exit;
        }
        $data = $this->request->getPost();
        $complaint = new Complaint();
        $result = $complaint->changeStatus($data['status'], json_decode($data['complaints']), $this->user->id);
        echo $result;
        exit;
    }

    public function recallAction($id)
    {

        if ($id == '0') {
            $data = $this->request->getPost();
            $data = json_decode($data['complaints']);
        } else {
            $data = array($id);
        }

        //$complaint = new Complaint();
        //$complaint->changeStatus('recalled', $data, $this->user->id); //todo: refactor to this later
        foreach ($data as $v) { //todo: whole array can be passed in $complaint->changeStatus
            $complaint = Complaint::findFirstById($v);
            if (!$complaint)
                return $this->forward('complaint/index');
            if (!$complaint->checkComplaintOwner($v, $this->user->id))
                return $this->forward('complaint/index');
            if($complaint->status=='submitted') {
                $complaint = new Complaint();
                $complaint->changeStatus('recalled', [$v], $this->user->id);
            }
        }
        if ($id == '0') { //todo: maby we need json response
            echo 'true';
            exit;
        }else
            header('Location: http://' . $_SERVER['HTTP_HOST'] . '/complaint/edit/' . $id);


    }

    public function saveAction()
    {
        $data = $this->request->getPost();
        $complaint = Complaint::findFirstById($data['complaint_id']);
        if (!$complaint || $complaint->status!='draft' || !$complaint->checkComplaintOwner($data['complaint_id'], $this->user->id)) {
            echo 'error';
            exit;
        }
        echo $complaint->saveComplaint($data);
        exit;

    }

}