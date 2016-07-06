<?php
namespace Multiple\Backend\Controllers;

use Phalcon\Mvc\Controller;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Multiple\Backend\Models\Complaint;
use Multiple\Backend\Models\Answer;
use Multiple\Backend\Models\Messages;
use Multiple\Backend\Models\Files;
use Multiple\Backend\Models\Permission;
use Multiple\Library\PaginatorBuilder;
use Multiple\Library\Log;

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
            $show_all_items = $this->request->get('all-portions-items');
            if (isset($show_all_items) && $show_all_items == 'all_items') {
                $item_per_page = 99999;
            }
            $complaints = Complaint::find();
            $paginator = new Paginator(array(
                "data"  => $complaints,
                "limit" => $item_per_page,
                "page"  => $numberPage
            ));
            $pages = $paginator->getPaginate();
            $this->view->page = $pages;
            $this->view->item_per_page = $item_per_page;
            $this->view->scroll_to_down = $next_items > 0 ? TRUE : FALSE;
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
            $files_html = [];
            $complaint = Complaint::findFirstById($id);
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
            if (!$perm->actionIsAllowed($this->user->id, 'lawyer', 'index') && $this->user->id != 1) {
               $this->view->allow_answer = 0;
            } else {
                $this->view->allow_answer = 1;
            }
            $this->view->attached_files = $files_html;
            $this->view->complaint = $complaint;
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

    public function deleteAnswerAction(){
        $perm = new Permission();
        if (!$perm->actionIsAllowed($this->user->id, 'lawyer', 'index') && $this->user->id != 1) {
           $data = "access_denied";
        } else {
            $answer_id = $this->request->getPost("id");
            if (isset($answer_id) && $answer_id) {
                $answer = Answer::find(
                    array(
                        'id = :id:',
                        'bind' => array(
                            'id' => $answer_id
                        )
                    )
                )->delete();
                $this->flashSession->success('Ответ удален');
                $data = "ok";
            }
        }
        $this->view->disable();
        echo json_encode($data);
    }

    public function updateAnswerAction(){
        $perm = new Permission();
        if (!$perm->actionIsAllowed($this->user->id, 'lawyer', 'index') && $this->user->id != 1) {
           $data = "access_denied";
        } else {
            $answer_id = $this->request->getPost("id");
            $answer_text = $this->request->getPost("text");
            if (isset($answer_id) && $answer_id && isset($answer_text) && strlen($answer_text)) {
                $answer = Answer::findFirstById($answer_id);
                $answer->text = $answer_text;
                $answer->save();
                $this->flashSession->success('Ответ сохранен');
                $data = "ok";
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
                        $complaint = Complaint::findFirstById($id);
                        switch ($status) {
                            case 'recalled':
                                if ($complaint->status == 'submitted') {
                                    Log::addAdminLog("Статус жалобы", "Статус жалобы {$complaint->complaint_name} изменен", $this->user);
                                    @Complaint::changeStatus($status, array($id));
                                    $this->flashSession->success('Статус изменен');
                                }
                                break;
                            case 'draft':
                                if ($complaint->status == 'archive') {
                                    Log::addAdminLog("Статус жалобы", "Статус жалобы {$complaint->complaint_name} изменен", $this->user);
                                    @Complaint::changeStatus($status, array($id));
                                    $this->flashSession->success('Статус изменен');
                                }
                                break;
                            case 'archive':
                                if ($complaint->status == 'draft' || $complaint->status == 'unfounded' || $complaint->status == 'recalled') {
                                    Log::addAdminLog("Статус жалобы", "Статус жалобы {$complaint->complaint_name} изменен", $this->user);
                                    @Complaint::changeStatus($status, array($id));
                                    $this->flashSession->success('Статус изменен');
                                }
                                break;
                            default:
                                Log::addAdminLog("Статус жалобы", "Статус жалобы {$complaint->complaint_name} изменен", $this->user);
                                @Complaint::changeStatus($status, array($id));
                                $this->flashSession->success('Статус изменен');
                                break;
                        }
                        /*if ($status == 'recalled' && $complaint->status == 'submitted') {
                            
                        } else*/ /*if ($status != 'recalled' && $complaint->status != 'submitted') {
                            Log::addAdminLog("Статус жалобы", "Статус жалобы {$complaint->complaint_name} изменен", $this->user);
                            @Complaint::changeStatus($status, array($id));
                        }*/
                    }
                } else {
                    $complaint = Complaint::findFirstById($complaint_id);
                    Log::addAdminLog("Статус жалобы", "Статус жалобы {$complaint->complaint_name} изменен", $this->user);
                    @Complaint::changeStatus($status, array($complaint_id));
                }
                if ($status == 'copy') {
                    $this->flashSession->success('Жалоба скопирована');
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
            $answer = new Answer();
            $answer->question_id = $this->request->get("question");
            $answer->admin_id = $this->user->id;
            $answer->text = $answer_text;
            $answer->date = date('Y-m-d H:i:s');
            $answer->save();
            $this->flashSession->success('Ответ сохранен');
            /*Send new system message to user*/
            $complaint = new Complaint();
            $from = $this->user->id;
            $complaint_id = $this->request->get("complaint");
            if (isset($complaint_id) && $complaint_id) {
                $toid = $complaint->getComplaintOwner($complaint_id);
                $subject = "Системное сообщение";
                $body = "Юрист дал ответ на ваш вопрос";
                if ($toid) {
                    $message = new Messages();
                    $message->from_uid = $from;
                    $message->to_uid = $toid;
                    $message->subject = $subject;
                    $message->body = $body;
                    $message->time = date('Y-m-d H:i:s');
                    $message->save();
                }
            }
            return $this->forward('complaints/preview/' . $this->request->get("complaint"));
        }
    }
}