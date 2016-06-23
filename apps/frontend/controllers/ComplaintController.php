<?php

namespace Multiple\Frontend\Controllers;


use Multiple\Frontend\Models\Applicant;
use Multiple\Frontend\Models\Category;
use Multiple\Frontend\Models\Complaint;
use Multiple\Frontend\Models\ComplaintMovingHistory;
use Multiple\Frontend\Models\Question;
use Multiple\Frontend\Models\Files;
use Phalcon\Mvc\Controller;
use \Phalcon\Paginator\Adapter\NativeArray as Paginator;
use Multiple\Library\PaginatorBuilder;
//use Multiple\Library\TrustedLibrary;

class ComplaintController extends ControllerBase
{
    public function indexAction()
    {
        if (!$this->user) {
             $this->flashSession->error('Вы не залогинены в системе');
             return $this->response->redirect('/');
        }
        $this->setMenu();
        $complaint = new Complaint();
        $status = 0;
        $numberPage = $this->request->getQuery("page", "int");
        if($numberPage===null) $numberPage = 1;
        if (isset($_GET['status']))
            $status = $_GET['status'];
      
        $complaints = $complaint->findUserComplaints($this->user->id, $status, $this->applicant_id);
        #$this->view->complaints = $complaints;
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
        //TrustedLibrary::trusted_library_init();
        $complaint = Complaint::findFirstById($id);
        if (!$complaint || !$complaint->checkComplaintOwner($id, $this->user->id))
            return $this->forward('complaint/index');
        //if (isset($_SESSION['TRUSTEDNET']['OAUTH'])) $OAuth2 = unserialize($_SESSION['TRUSTEDNET']['OAUTH']);
//        if (isset($OAuth2)){
//            /*$token = $OAuth2->getAccessToken();
//            if(!$OAuth2->checkToken())
//                if($OAuth2->refresh())*/ $token = $OAuth2->getRefreshToken();
//            $this->view->token  = $token;
//
//        } else {
//            $this->session->destroy();
//            return $this->forward('/');
//        }

        $files_html = [];
        if ($complaint->fid) {
            $file_ids = unserialize($complaint->fid);
            if (count($file_ids)) {
                $file_model = new Files();
                $files = Files::find(
                    array(
                        'id IN ({ids:array})',
                        'bind' => array(
                            'ids' => $file_ids
                        )
                    )
                );
                foreach ($files as $file) {
                    $files_html[] = $file_model->getFilesHtml($file, $id, 'complaint');
                }
            }
        }
        $action = $this->request->get('action');
        if (isset($action) && $action == 'edit') {
            $this->view->edit_now = TRUE;
        } else {
            $this->view->edit_now = FALSE;
        }
        $history = ComplaintMovingHistory::findFirst("complaint_id = $id");
        if ($history) {
            $history->is_read = 1;
            $history->update();
        }
        $this->view->attached_files = $files_html;
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
        //TrustedLibrary::trusted_library_init();
        $this->setMenu();
        $category = new Category();
        $arguments = $category->getArguments();
        //if (isset($_SESSION['TRUSTEDNET']['OAUTH'])) $OAuth2 = unserialize($_SESSION['TRUSTEDNET']['OAUTH']);
//        if (isset($OAuth2)){
//            /*$token = $OAuth2->getAccessToken();
//            if(!$OAuth2->checkToken())
//                if($OAuth2->refresh())*/
//            $token = $OAuth2->getRefreshToken();
//            $this->view->token  = $token;
//
//        } else {
//            $this->session->destroy();
//            return $this->forward('/');
//        }


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

        if ($complaint->save() == false) {
            $this->flashSession->error('Не выбран заявитель');
            /*foreach ($complaint->getMessages() as $message) {
                $this->flashSession->error($message);
            }*/
            $response = array('result' => 'error', 'message' => 'Ошибка при попытке сохранения жалобы');
        } else {
            $response = array('result' => 'success', 'id' => $complaint->id);
        }
        header('Content-type: application/json');
        echo json_encode($response);
        exit;
    }

    public function askQuestionAction(){
        $question = $this->request->getPost('new-question');
        $complaint_id = $this->request->getPost('complaint_id');
        if (isset($question) && strlen($question) && isset($complaint_id) && $complaint_id) {
            $new_question = new Question();
            $new_question->user_id = $this->user->id;
            $new_question->complaint_id = $complaint_id;
            $new_question->text = $question;
            $new_question->date = date('Y-m-d H:i:s');
            $new_question->is_read = 'n';
            $new_question->save();
            $this->flashSession->success('Ваш вопрос отправлен юристу');
            return $this->response->redirect('/complaint/edit/' . $complaint_id);
        }
        $this->flashSession->error('Поле с вопросом не заполнено');
        return $this->response->redirect('/complaint/index');
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