<?php
namespace Multiple\Backend\Controllers;

use Phalcon\Mvc\Controller;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Multiple\Backend\Models\Complaint;
use Multiple\Backend\Models\Answer;
use Multiple\Backend\Models\Permission;
use Multiple\Library\PaginatorBuilder;

class ComplaintsController extends ControllerBase
{

    public function indexAction(){
        $perm = new Permission();
        if (!$perm->actionIsAllowed($this->user->id, 'complaints', 'index')) {
           $this->view->pick("access/denied");
           $this->setMenu();
        } else {
            $next_items = $this->request->getPost('next-portions-items');
            if (!isset($next_items)) {
                $next_items = 0;
            }
            $item_per_page = 20 + $next_items;
            $numberPage = isset($_GET['page']) ? $_GET['page'] : 1;
            $complaints = Complaint::find();
            $paginator = new Paginator(array(
                "data"  => $complaints,
                "limit" => $item_per_page,
                "page"  => $numberPage
            ));
            $pages = $paginator->getPaginate();
            $this->view->page = $pages;
            $this->view->item_per_page = $item_per_page;
            $this->view->paginator_builder = PaginatorBuilder::buildPaginationArray($numberPage, $pages->total_pages);
            $this->setMenu();
        }
    }

    public function previewAction($id){
        $perm = new Permission();
        if (!$perm->actionIsAllowed($this->user->id, 'complaints', 'index')) {
           $this->view->pick("access/denied");
           $this->setMenu();
        } else {
            $this->view->complaint = Complaint::findFirstById($id);
            $this->setMenu();
        }
    }

    public function deleteComplaintAction(){
        $perm = new Permission();
        if (!$perm->actionIsAllowed($this->user->id, 'complaints', 'edit')) {
           $data = "access_denied";
        } else {
            $complaint_id = $this->request->getPost("id");
            if ((isset($_GET['is_array']) && $complaint_id && count($complaint_id)) || (isset($_POST['is_array']) && $complaint_id && count($complaint_id))) {
                $complaints = Complaint::find(
                    array(
                        'id IN ({ids:array})',
                        'bind' => array(
                            'ids' => $complaint_id
                        )
                    )
                )->delete();
                $this->flashSession->success('Жалобы удалены');
                $data = "ok";
            } elseif($complaint_id){
                $complaint = Complaint::find(
                    array(
                        'id = :id:',
                        'bind' => array(
                            'id' => $complaint_id
                        )
                    )
                )->delete();
                $this->flashSession->success('Жалоба удалена');
                $data = 'ok';
            }
        }
        $this->view->disable();
        echo json_encode($data);
    }

    public function changeComplaintStatusAction() {
        $perm = new Permission();
        if (!$perm->actionIsAllowed($this->user->id, 'complaints', 'edit')) {
           $data = "access_denied";
        } else {
            $status = $this->request->getPost("status");
            $complaint_id = $this->request->getPost("id");
            
            if($status && $complaint_id){
                if (is_array($complaint_id)) {
                    foreach ($complaint_id as $id) {
                        @Complaint::changeStatus($status, array($id));
                    }
                } else {
                    @Complaint::changeStatus($status, array($complaint_id));
                }
                if ($status == 'copy') {
                    $this->flashSession->success('Жалоба скопирована');
                } else {
                    $this->flashSession->success('Статус изменен');
                }
                $data = "ok";
            }
        }
        $this->view->disable();
        echo json_encode($data);
    }
    
    public function addAnswerAction() {
        $perm = new Permission();
        if (!$perm->actionIsAllowed($this->user->id, 'complaints', 'edit')) {
           $data = "access_denied";
        } else {
            $answer_text = $this->request->getPost("lawyer-answer");
            if (!isset($answer_text) || !strlen($answer_text)) {
                $this->flashSession->error('Не введен ответ на вопрос');
                return $this->forward('complaints/preview/' . $this->request->get("complaint"));
            }
            $data = "ok";
            $status = $this->request->getPost("status");
            $complaint_id = $this->request->getPost("id");
            $answer = new Answer();
            $answer->question_id = $this->request->get("question");
            $answer->admin_id = $this->user->id;
            $answer->text = $answer_text;
            $answer->date = date('Y-m-d H:i:s');
            $answer->save();
            $this->flashSession->success('Ответ сохранен');
            return $this->forward('complaints/preview/' . $this->request->get("complaint"));
        }
    }
}