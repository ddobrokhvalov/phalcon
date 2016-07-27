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
use Multiple\Backend\Models\Category;
use Multiple\Backend\Models\UsersArguments;
use Multiple\Backend\Models\ComplaintMovingHistory;
use Multiple\Backend\Models\Question;
use Multiple\Backend\Models\Applicant;
use Multiple\Library\Parser;

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

    public function editAction( $id ){
        $perm = new Permission();
        if (!$perm->actionIsAllowed($this->user->id, 'complaints', 'edit')) {
            $this->view->pick("access/denied");
            $this->setMenu();
        } else {
            $this->view->show_applicant = true;
            $complaint = Complaint::findFirstById( $id );
            if (!$complaint) return $this->forward('admin/complaint/index');
            $category = new Category();
            $arguments = $category->getArguments();
            $this->view->arguments = $arguments;
            $arguments = UsersArguments::find(
                array(
                    'complaint_id = :complaint_id:',
                    'bind' => [
                        'complaint_id' => $id,
                    ]
                )
            );
            $user_arguments = '';
            $argument_order = 0;
            $categories_id = [];
            $arguments_id = [];
            $arr_sub_cat = array();

            foreach ($arguments as $argument) {
                $categories_id[] = $argument->argument_category_id;
                $arguments_id[] = $argument->argument_id;
                if ($argument_order == $complaint->complaint_text_order) {
                    $user_arguments .= $complaint->complaint_text . '</br>';
                    $user_arguments .= $argument->text . '</br>';
                } else {
                    $user_arguments .= $argument->text . '</br>';
                }
                $arr_sub_cat[] = array(
                    'id'   => $argument->argument_id,
                    'text' => $argument->text,
                );
                ++$argument_order;
            }
            if(!empty($arr_sub_cat)){
                $this->view->arr_sub_cat = $arr_sub_cat;
            }
            $this->view->categories_id = implode(',', $categories_id);
            $this->view->arguments_id = implode(',', $arguments_id);
            $this->view->complaint_text_order = $complaint->complaint_text_order;
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
                        $files_html[] = $file_model->getFilesHtml($file, $id, 'complaints');
                    }
                }
            }

            $action = $this->request->get('action');
            if (isset($action) && $action == 'edit') {
                $this->view->edit_now = TRUE;
            } else {
                $this->view->edit_now = FALSE;
            }
            $this->view->user_arguments = $user_arguments;
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
//            $parser = new Parser();
//            $data = $parser->parseAuction((string)$complaint->auction_id);
//            $complaint->nachalo_podachi =           isset($data['procedura']['nachalo_podachi'])            ? $data['procedura']['nachalo_podachi']         : null;
//            $complaint->okonchanie_podachi =        isset($data['procedura']['okonchanie_podachi'])         ? $data['procedura']['okonchanie_podachi']      : null;
//            $complaint->okonchanie_rassmotreniya =  isset($data['procedura']['okonchanie_rassmotreniya'])   ? $data['procedura']['okonchanie_rassmotreniya']: null;
//            $complaint->data_provedeniya =          isset($data['procedura']['data_provedeniya'])           ? $data['procedura']['data_provedeniya']        : null;
//            $complaint->vremya_provedeniya =        isset($data['procedura']['vremya_provedeniya'])         ? $data['procedura']['vremya_provedeniya']      : null;
//            $complaint->vskrytie_konvertov =        isset($data['procedura']['vskrytie_konvertov'])         ? $data['procedura']['vskrytie_konvertov']      : null;
//            $complaint->data_rassmotreniya =        isset($data['procedura']['data_rassmotreniya'])         ? $data['procedura']['data_rassmotreniya']      : null;
            if(is_null($complaint->date_start)) $complaint->date_start = $complaint->nachalo_podachi;
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
                    $this->flashSession->success('Статус изменен');
                }
                if ($status == 'copy') {
                    $this->flashSession->success('Жалоба скопирована');
                }
                if ($status == 'archive') {
                    $this->flashSession->success('Жалоба добавлена в архив');
                }
                $data = "ok";
            }
        }
        $this->view->disable();
        echo json_encode($data);
    }
    
    public function addAnswerAction()
    {
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

                    $quest = Question::findFirstById($answer->question_id);
                    if($quest){
                        $quest->is_read = 'y';
                        $quest->save();
                    }
                }
            }
            $response = new \Phalcon\Http\Response();
            $response->redirect('admin/complaints/preview/' . $this->request->get("complaint"));
            $response->send();
        }
    }

    public function deleteAction($id){
        $perm = new Permission();
        if (!$perm->actionIsAllowed($this->user->id, 'complaints', 'edit')) {
            $this->view->pick("access/denied");
            $this->setMenu();
        }
        $complaint = Complaint::findFirst($id);
        if (!$complaint)
            return $this->forward('/admin/complaints/index');

        if ($complaint != false) {
            $complaint->delete();
        }
        $this->flashSession->success('Жалоба успешно удалена');
        return $this->response->redirect('/admin/complaints/index');
    }

    public function saveAction()
    {
        $perm = new Permission();
        if (!$perm->actionIsAllowed($this->user->id, 'complaints', 'edit')) {
            $this->view->pick("access/denied");
            $this->setMenu();
        }
        $data = $this->request->getPost();
        $complaint = Complaint::findFirstById($data['complaint_id']);
        if (!$complaint || $complaint->status!='draft' ) {
            echo 'error';
            exit;
        }
        echo $complaint->saveComplaint($data);
        exit;

    }
    public function updateAction()
    {
        if (!$this->request->isPost()) {
            echo 'error';
            exit;
        }
        $users_arguments_ = [];
        $data = $this->request->getPost();
        $users_arguments = explode('_?_', $data['arguments_data']);
        unset($users_arguments[count($users_arguments) - 1]);
        foreach ($users_arguments as $key => $row) {
            $users_arguments[$key] = explode('?|||?', $row);
        }
        foreach ($users_arguments as $key => &$row) {
            foreach ($row as $data_) {
                $data_ = explode('===', $data_);
                $users_arguments_[$key][$data_[0]] = $data_[1];
                if (isset($users_arguments_[$key]['argument_id']) && $users_arguments_[$key]['argument_id'] == 'just_text') {
                    $data['complaint_text'] = $data_[1];
                    $data['complaint_text_order'] = $users_arguments_[$key]['order'];
                }
            }
        }
        $complaint = Complaint::findFirstById($data['update-complaint-id']);
        if ($complaint) {
            $complaint->complaint_name = $data['complaint_name'];
            $complaint->complaint_text = $data['complaint_text'];
            $complaint->complaint_text_order = $data['complaint_text_order'];
        }

        if ($complaint->update() == false) {
            //$this->flashSession->error('Не выбран заявитель');
            foreach ($complaint->getMessages() as $message) {
                $this->flashSession->error($message);
            }
            return $this->response->redirect('/complaint/edit/' + $data['update-complaint-id']);
        } else {
            $allow = TRUE;

            // Remove all arguments
            $arguments_delete = UsersArguments::find(
                array(
                    'complaint_id = :complaint_id:',
                    'bind' => [
                        'complaint_id' => $data['update-complaint-id'],
                    ]
                )
            )->delete();

            // Save users arguments.
            foreach ($users_arguments_ as $argument) {
                if ($argument['argument_id'] != 'just_text') {
                    $u_arg = new UsersArguments();
                    $u_arg->complaint_id = $complaint->id;
                    $u_arg->argument_id = $argument['argument_id'];
                    $u_arg->argument_order = $argument['order'];
                    $u_arg->text = $argument['argument_text'];
                    $u_arg->argument_category_id = $argument['category_id'];
                    $u_arg->save();
                }
            }

            // Check all files with needed rules.
            if ($this->request->hasFiles() == true) {
                $files_model = new Files();
                if (!$files_model->checkAllFiles($this->request)) {
                    $allow = FALSE;
                }
            }
            if ($allow) {
                $saved_files = array();
                if ($this->request->hasFiles() == true) {
                    $baseLocation = 'files/complaints/';
                    foreach ($this->request->getUploadedFiles() as $file) {
                        if (strlen($file->getName())) {
                            $applicant_file = new Files();
                            $name = explode('.', $file->getName())[0] . '_' . time() . '.' . explode('.', $file->getName())[1];
                            //$name = iconv("UTF-8", "cp1251", $name);
                            $applicant_file->file_path = $name;
                            $applicant_file->file_size = round($file->getSize() / 1024, 2);
                            $applicant_file->file_type = $file->getType();
                            $applicant_file->save();
                            $saved_files[] = $applicant_file->id;
                            //Move the file into the application
                            $file->moveTo($baseLocation . $name);
                        }
                    }
                }
                $old_files = unserialize($complaint->fid);
                if (count($old_files)) {
                    $complaint->fid = serialize(array_merge($old_files, $saved_files));
                } else {
                    $complaint->fid = serialize($saved_files);
                }
                $complaint->save();
            }
            $this->flashSession->success('Жалоба обновлена');
            return $this->response->redirect('admin/complaints/preview/' . $complaint->id);
        }
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
        $this->flashSession->success('Копия жалобы создана');
        echo $result;
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
            return $this->response->redirect('/admin/complaints/edit/' . $complaint_id);
        }
        $this->flashSession->error('Поле с вопросом не заполнено');
        return $this->response->redirect('/admin/complaints/index');
    }
}