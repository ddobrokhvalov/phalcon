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
use Multiple\Backend\Models\Ufas;
use Multiple\Backend\Models\User;
use Multiple\Library\Translit;
use Multiple\Backend\Models\Arguments;
use Multiple\Backend\Models\ArgumentsCategory;
use  Phalcon\Mvc\Model\Query\Builder;

class ComplaintsController extends ControllerBase
{
	
	const STEP_ONE = 1;
    const STEP_TWO = 2;
    const STEP_THREE = 3;
    const STEP_FOUR = 4;
    const STEP_SEARCH = 6;
	
    public function indexAction()
    {
        $perm = new Permission();
        if (!$perm->actionIsAllowed($this->user->id, 'complaints', 'index')) {
            $this->view->pick("access/denied");
            $this->setMenu();
        } else {
            setlocale(LC_ALL, 'ru_RU.UTF-8');
            $search = $this->request->get('search');
            $search = preg_replace("/[^a-zA-ZА-Яа-я0-9\s]/u", "", $search);
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
            $complaints = Complaint::find(array(
                "conditions" => "complaint_name LIKE '%{$search}%' OR auction_id LIKE '%{$search}%'",
                "order" => "date DESC"
            ));

            $paginator = new Paginator(array(
                "data" => $complaints,
                "limit" => $item_per_page,
                "page" => $numberPage
            ));
            $pages = $paginator->getPaginate();
            $this->view->page = $pages;
            $this->view->item_per_page = $item_per_page;
            $this->view->scroll_to_down = $next_items > 0 ? TRUE : FALSE;
            $this->view->paginator_builder = PaginatorBuilder::buildPaginationArray($numberPage, $pages->total_pages);
            $this->setMenu();
        }
    }
	
	public function activeComplaintsAction()
    {
        $perm = new Permission();
        if (!$perm->actionIsAllowed($this->user->id, 'complaints', 'index')) {
            $this->view->pick("access/denied");
            $this->setMenu();
        } else {
			if($this->request->getPost("action") && $this->request->getPost("action") == "set_incoming_number"){
				$post_data = $this->request->getPost();
				$complaint = Complaint::findFirstById($post_data["complaint_id"]);
				if($complaint){
					$complaint->incoming_number = $post_data["incoming_number"];
					$complaint->save();
					$result = array("status"=>"ok", "text"=>"Входящий номер обновлен");
				}else{
					$result = array("status"=>"error", "text"=>"Жалоба не найдена");
				}
				echo json_encode($result);
				exit;
			}
            setlocale(LC_ALL, 'ru_RU.UTF-8');
            $search = $this->request->get('search');
            $search = preg_replace("/[^a-zA-ZА-Яа-я0-9\s]/u", "", $search);
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
            $complaints = Complaint::find(array(
                "conditions" => "(complaint_name LIKE '%{$search}%' OR auction_id LIKE '%{$search}%') and status = 'submitted'",
                "order" => "date DESC"
            ));

            $paginator = new Paginator(array(
                "data" => $complaints,
                "limit" => $item_per_page,
                "page" => $numberPage
            ));
            $pages = $paginator->getPaginate();
            $this->view->page = $pages;
            $this->view->item_per_page = $item_per_page;
            $this->view->scroll_to_down = $next_items > 0 ? TRUE : FALSE;
            $this->view->paginator_builder = PaginatorBuilder::buildPaginationArray($numberPage, $pages->total_pages);
            $this->setMenu();
        }
    }

    public function editAction($id)
    {
        $perm = new Permission();
        if (!$perm->actionIsAllowed($this->user->id, 'complaints', 'edit')) {
            $this->view->pick("access/denied");
            $this->setMenu();
        } else {
            $this->view->is_admin = true;
            $this->view->show_applicant = true;
            $complaint = Complaint::findFirstById($id);
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
            $arr_users_arg = array();
            foreach ($arguments as $argument) {
                $text = $argument->text;
                $categories_id[] = $argument->argument_category_id;
                $arguments_id[] = $argument->argument_id;
                $text = preg_replace('/[\r\n\t]/', '', $text);
                $text = str_replace("'", '"', $text);
                $arr_users_arg[$argument->argument_id] = $text;
                if ($argument_order == $complaint->complaint_text_order) {
                    $user_arguments .= $complaint->complaint_text . '</br>';
                    $user_arguments .= $argument->text . '</br>';
                } else {
                    $user_arguments .= $argument->text . '</br>';
                }
                $arr_sub_cat[] = array(
                    'id' => $argument->argument_id,
                    'text' => preg_replace('/[\r\n\t]/', '', $text),
                );
                ++$argument_order;
            }
            if (!empty($arr_sub_cat)) {
                $this->view->arr_sub_cat = $arr_sub_cat;
            }
            $this->view->arr_users_arg = $arr_users_arg;
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

            $this->view->ufas_name = 'Уфас не определен';
            if ($complaint->ufas_id != null) {
                $ufas_name = Ufas::findFirst(array(
                    "id={$complaint->ufas_id}"
                ));
                if ($ufas_name) {
                    $this->view->ufas_name = $ufas_name->name;
                    $this->view->comp_inn = $ufas_name->number;
                }
            }

            $action = $this->request->get('action');
            if (isset($action) && $action == 'edit') {
                $this->view->edit_now = TRUE;
                $ufas = Ufas::find();
                $this->view->ufas = $ufas;
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
            $this->view->date_end = $this->checkDateEndSendApp($complaint->okonchanie_podachi);
            $this->view->complaint_question = $complaintQuestion;
            $this->view->action_edit = false;
            if (isset($_GET['action']) && $_GET['action'] == 'edit' && $complaint->status == 'draft')
                $this->view->action_edit = true;


        }
    }

    public function previewAction($id)
    {
        $perm = new Permission();
        if (!$perm->actionIsAllowed($this->user->id, 'lawyer', 'edit') && !$perm->actionIsAllowed($this->user->id, 'complaints', 'edit')) {
            $this->view->pick("access/denied");
            $this->setMenu();
        } else {
            $files_html = [];
            $complaint = Complaint::findFirstById($id);
			/*print_r("<pre>");
			print_r($complaint);
			print_r("</pre>");
			exit;*/
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
            $arguments = UsersArguments::find(
                array(
                    'complaint_id = :complaint_id:',
                    'bind' => [
                        'complaint_id' => $id,
                    ]
                )
            );

            $this->view->ufas_name = 'Уфас не определен';
            if ($complaint->ufas_id != null) {
                $ufas_name = Ufas::findFirst(array(
                    "id={$complaint->ufas_id}"
                ));
                if ($ufas_name) {
                    $this->view->ufas_name = $ufas_name->name;
                }
            }

            $user_arguments = '';
            foreach ($arguments as $argument) {
                $user_arguments .= $argument->text . '</br>';
            }
			if($complaint->user_id){
				$user = User::findFirst(array(
					"id={$complaint->user_id}"
				));
			}else{
				$applicant = Applicant::findFirst(array(
					"id={$complaint->applicant_id}"
				));
				if($applicant){
					$user = User::findFirst(array(
						"id={$applicant->user_id}"
					));
				}
			}
			
			$imported_data = $complaint->findImportedResult();
			$compl_arr = $complaint->toArray();
						
			if(count($imported_data)){
				$imported_data = array_reverse($imported_data);
				$imported_data = $imported_data[0];
				$imported_data["regDate"] = date("d.m.Y", strtotime($imported_data["regDate"]));
				$imported_data["planDecisionDate"] = date("d.m.Y H:i", strtotime($imported_data["planDecisionDate"]));
				$imported_data["attachments"] = json_decode($imported_data["attachments"]);
				$imported_data["decisionattachments"] = json_decode($imported_data["decisionattachments"]);
				$imported_data["icc_attachments"] = json_decode($imported_data["icc_attachments"]);
				if($_GET["debug"]){
					print_r("<pre>");
					print_r($imported_data);
					print_r("</pre>");
					exit;
				}
				$this->view->imported_data = $imported_data;
			}

            if (!$perm->actionIsAllowed($this->user->id, 'lawyer', 'index') && $this->user->id != 1) {
                $this->view->allow_answer = 0;
            } else {
                $this->view->allow_answer = 1;
            }
            $this->view->attached_files = $files_html;
            $this->view->user_arguments = $user_arguments;
//            $parser = new Parser();
//            $data = $parser->parseAuction((string)$complaint->auction_id);
//            $complaint->nachalo_podachi =           isset($data['procedura']['nachalo_podachi'])            ? $data['procedura']['nachalo_podachi']         : null;
//            $complaint->okonchanie_podachi =        isset($data['procedura']['okonchanie_podachi'])         ? $data['procedura']['okonchanie_podachi']      : null;
//            $complaint->okonchanie_rassmotreniya =  isset($data['procedura']['okonchanie_rassmotreniya'])   ? $data['procedura']['okonchanie_rassmotreniya']: null;
//            $complaint->data_provedeniya =          isset($data['procedura']['data_provedeniya'])           ? $data['procedura']['data_provedeniya']        : null;
//            $complaint->vremya_provedeniya =        isset($data['procedura']['vremya_provedeniya'])         ? $data['procedura']['vremya_provedeniya']      : null;
//            $complaint->vskrytie_konvertov =        isset($data['procedura']['vskrytie_konvertov'])         ? $data['procedura']['vskrytie_konvertov']      : null;
//            $complaint->data_rassmotreniya =        isset($data['procedura']['data_rassmotreniya'])         ? $data['procedura']['data_rassmotreniya']      : null;
            if (is_null($complaint->date_start)) $complaint->date_start = $complaint->nachalo_podachi;
            $this->view->complaint = $complaint;
            $this->view->user = $user;
            $this->view->applicant = $complaint->Applicant;
            $this->setMenu();
        }
    }

    public function deleteComplaintAction()
    {
        $perm = new Permission();
        if (!$perm->actionIsAllowed($this->user->id, 'complaints', 'edit')) {
            $data = "access_denied";
        } else {
            $complaint_id = $this->request->getPost("id");
            if ((isset($_GET['is_array']) && $complaint_id && count($complaint_id)) || (isset($_POST['is_array']) && $complaint_id && count($complaint_id))) {
                /*$complaints = Complaint::find(
                    array(
                        'id IN ({ids:array})',
                        'bind' => array(
                            'ids' => $complaint_id
                        )
                    )
                );//->delete();*/
				foreach($complaint_id as $compl_id){
					$complaint = Complaint::findFirstById($compl_id);
					$complaint->deleted = 1;
					$complaint->save();
				}
				
				
                $this->flashSession->success('Жалобы удалены');
                $data = "ok";
            } elseif ($complaint_id) {
                /*$complaint = Complaint::find(
                    array(
                        'id = :id:',
                        'bind' => array(
                            'id' => $complaint_id
                        )
                    )
                );//->delete();*/
				$complaint = Complaint::findFirstById($complaint_id);
				$complaint->deleted = 1;
				$complaint->save();
                $this->flashSession->success('Жалоба удалена');
                $data = 'ok';
            }
        }
        $this->view->disable();
        echo json_encode($data);
    }

    public function deleteAnswerAction()
    {
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

    public function updateAnswerAction()
    {
        $perm = new Permission();//&& $this->user->id != 1
        if (!$perm->actionIsAllowed($this->user->id, 'lawyer', 'edit')) {
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

    public function changeComplaintStatusAction()
    {
        $perm = new Permission();
        if (!$perm->actionIsAllowed($this->user->id, 'complaints', 'edit')) {
            $data = "access_denied";
        } else {
            $status = $this->request->getPost("status");
            $complaint_id = $this->request->getPost("id");

            if ($status && $complaint_id) {
                if (is_array($complaint_id)) {
                    foreach ($complaint_id as $id) {
                        $complaint = Complaint::findFirstById($id);
                        switch ($status) {
                            case 'recalled':
                                if ($complaint->status == 'submitted') {
                                    Log::addAdminLog("Статус жалобы", "Статус жалобы {$complaint->complaint_name} изменен", $this->user);
                                    @Complaint::changeStatus($status, array($id));
                                    //  $this->flashSession->success('Статус изменен');
                                }
                                break;
                            case 'draft':
                                if ($complaint->status == 'archive') {
                                    Log::addAdminLog("Статус жалобы", "Статус жалобы {$complaint->complaint_name} изменен", $this->user);
                                    @Complaint::changeStatus($status, array($id));
                                    // $this->flashSession->success('Статус изменен');
                                }
                                break;
                            case 'archive':
                                if ($complaint->status == 'draft' || $complaint->status == 'unfounded' || $complaint->status == 'recalled') {
                                    Log::addAdminLog("Статус жалобы", "Статус жалобы {$complaint->complaint_name} изменен", $this->user);
                                    @Complaint::changeStatus($status, array($id));
                                    // $this->flashSession->success('Статус изменен');
                                }
                                break;
                            default:
                                Log::addAdminLog("Статус жалобы", "Статус жалобы {$complaint->complaint_name} изменен", $this->user);
                                if($status == "copy"){
									$new_complaint_id = Complaint::changeStatus($status, array($id));
									$user_arg = new UsersArguments();
									$user_arg->copyUsersArguments($id, $new_complaint_id);
								}else{
									@Complaint::changeStatus($status, array($id));
								}
                                //$this->flashSession->success('Статус изменен');
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
					if($status == "copy"){
						$new_complaint_id = Complaint::changeStatus($status, array($complaint_id));
						$user_arg = new UsersArguments();
						$user_arg->copyUsersArguments($complaint_id, $new_complaint_id);
					}else{
						@Complaint::changeStatus($status, array($complaint_id));
					}
                    //$this->flashSession->success('Статус изменен');
                }
                if ($status == 'copy') {
                    $this->flashSession->success('Успешно скопировано');
                }
                if ($status == 'archive') {
                    $this->flashSession->success('Статус изменен на архив');
                }
                if ($status == 'submitted') {
                    $this->flashSession->success('Статус изменен на подано');
                }
                if ($status == 'recalled') {
                    $this->flashSession->success('Статус изменен на отозвано');
                }
                if ($status == 'draft') {
                    $this->flashSession->success('Успешно поместили в черновик');
                }
                if ($status == 'unfounded') {
                    $this->flashSession->success('Статус изменен на необосновано');
                }
                if ($status == 'justified') {
                    $this->flashSession->success('Статус изменен на обосновано');
                }
                if ($status == 'activate') {
                    $this->flashSession->success('Успешно активировали');
                }
                if ($status == 'under_consideration') {
                    $this->flashSession->success('Статус изменен на рассмотрение');
                }
                $data = "ok";
            }
        }
        echo json_encode($status);
        $this->view->disable();

    }

    public function addAnswerAction()
    {
        $perm = new Permission();
        if (!$perm->actionIsAllowed($this->user->id, 'lawyer', 'edit')) {
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

                $complaint = Complaint::findFirst($complaint_id);

                $body = "Юрист дал ответ на ваш вопрос. Закупка №" . $complaint->auction_id;
                if ($toid) {
                    $message = new Messages();
                    $message->from_uid = $from;
                    $message->to_uid = $toid;
                    $message->subject = $subject;
                    $message->body = $body;
                    $message->time = date('Y-m-d H:i:s');
                    $message->comp_id = $complaint_id;
                    $message->save();

                    $quest = Question::findFirstById($answer->question_id);
                    if ($quest) {
                        $quest->is_read = 'y';
                        $quest->save();
                    }

                    $user = User::findFirst($toid);
                    if ($user && $user->notifications == 1) {
                        $message = $this->mailer->createMessageFromView('../views/emails/lawyer', array(
                            'host' => $this->request->getHttpHost(),
                            'firstname' => $user->firstname,
                            'patronymic' => $user->patronymic,
                            'quies_text' => $quest->text,
                            'answer_text' => $answer_text
                        ))
                            ->to($user->email)
                            ->subject('Ответ юриста в системе ФАС-Онлайн');
                        $message->send();
                    }

                }
            }
            $response = new \Phalcon\Http\Response();
            $response->redirect('admin/complaints/preview/' . $this->request->get("complaint"));
            $response->send();
        }
    }

    public function deleteAction($id)
    {
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
        if (!$complaint || $complaint->status != 'draft') {
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
            if (!empty($data['complaint_text']) && $data['complaint_text'] == '<p>Пользовательский текст</p>') {
                $data['complaint_text'] = '<p>' . str_replace($data['argument_text'], 'Пользовательский текст', '') . '</p>';
            }
            if (!empty($data['complaint_text']) && $data['complaint_text'] == '<p>Вам необходимо выбрать хотябы одну обязательную жалобу!</p>') {
                $data['complaint_text'] = '<p>' . str_replace($data['argument_text'], 'Вам необходимо выбрать хотябы одну обязательную жалобу!', '') . '</p>';
            }
            $complaint->complaint_name = $data['complaint_name'];
            $complaint->complaint_text = (!empty($data['complaint_text'])) ? $data['complaint_text'] : '';
            $complaint->complaint_text_order = (!empty($data['complaint_text_order'])) ? $data['complaint_text_order'] : '';
            $ufas = Ufas::findFirst(array(
                "number = {$data['ufas_id']}"
            ));
            if ($ufas) {
                $complaint->ufas_id = $ufas->id;
            }
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
                            $applicant_file->file_path = Translit::rusToEng($name);
                            $applicant_file->file_size = round($file->getSize() / 1024, 2);
                            $applicant_file->file_type = $file->getType();
                            $applicant_file->save();
                            $saved_files[] = $applicant_file->id;
                            //Move the file into the application
                            $file->moveTo($baseLocation . Translit::rusToEng($name));
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

    public function askQuestionAction()
    {
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
        return $this->response->redirect('/admin/complaints/edit/' . $complaint_id);
    }

    public function sendMessageAction(){
        $from = $this->user->id;
        $toids = $this->request->getPost("toids");
        $subject = $this->request->getPost("subject");
        $body = $this->request->getPost("body");
        
        if(count($toids) && $from){
            foreach ($toids as $to){
                $message = new Messages();
                $message->from_uid = $from;
                $message->to_uid = $to;
                $message->subject = $subject;
                $message->body = $body;
                $message->time = date('Y-m-d H:i:s');
                $message->save();
            }
        }
        $this->view->disable();
        $this->flashSession->success('Сообщение отправлено');
        $data = "ok";
        echo json_encode($data);
    }
    
    private function checkDateEndSendApp($dateOff, &$result = false)
    {
        $dateOff = strtotime($dateOff);
        $nowTime = strtotime("now");

        if ($nowTime > $dateOff) {
            return 1;
        }
        return 0;
    }
	public function addAction()
    {
        $this->setMenu();
        $category = new Category();
        $arguments = $category->getArguments2();
        $ufas = Ufas::find();
		
		$users = User::find(array("order" => "email ASC"));
		$this->view->users = $users;
		
		if($_GET["select_user_id"]){
			$selested_user = User::findFirstById($_GET["select_user_id"]);
			$this->view->selested_user = $selested_user;
			$user_applicants = Applicant::findByUserId($selested_user->id);
			$this->view->user_applicants = $user_applicants;
			if($_GET["select_applicant_id"]){
				$selected_applicant = Applicant::findFirstById($_GET["select_applicant_id"]);
				$this->view->selected_applicant = $selected_applicant;
			}
		}
		
		/*print_r("<pre>");
		print_r($ufas);
		print_r("</pre>");
		exit;*/
		
		/*$complaint = new Complaint();
		$user_tarif = $complaint->getTarifById($this->user->tarif_id);
		$user_tarif = $user_tarif[0];
		$complaints = $complaint->findUserComplaints($this->user->id, 0, false, false, $this->user->tarif_date_activate);
		
		$tarif_out = false;
		
		if($user_tarif["tarif_price"] == 0 && count($complaints)){
			$tarif_out = true;
		}elseif($user_tarif["tarif_type"] == "complaint" && count($complaints) >= $this->user->tarif_count){
			$tarif_out = true;
		}elseif($user_tarif["tarif_type"] == "month" && date("Y-m-d H:i:s") >= date("Y-m-d H:i:s", strtotime($this->user->tarif_date_activate." +".$this->user->tarif_count." months")) ){
			$tarif_out = true;
		}
		
		$tarif_not_active = false;
		if(!$this->user->tarif_active){
			$tarif_not_active = true;
						
			$tarif_order = new TarifOrder();
			$tarif_orders = $tarif_order->getTarifOrders($this->user->id, $this->user->tarif_id, $this->user->tarif_count);
			
			if($tarif_orders){
				$tarif_orders = $tarif_orders[0];
				$this->view->tarif_orders = $tarif_orders;
				$_SESSION["order_id"] = $tarif_orders["id"];
			}
		}*/
		
		/*$this->view->tarif_not_active = $tarif_not_active;
		$this->view->tarif_out = $tarif_out;
		$this->view->user_tarif = $user_tarif;*/
		
        $this->view->edit_mode = 0;
        $this->view->ufas = $ufas;
        //$this->view->checkUser = $this->checkUser();
        $this->view->arguments = $arguments;
    }
	
	/* ADD COMPLICANT */
    public function ajaxStepsAddComplaintAction()
    {
		try{
			$data = array();
			$result = array(
				"cat_arguments" => array(),
				"arguments" => array(),
				"date" => 0
			);
			$CurrentStep = $this->request->get('step');
			$data['type'] = $this->request->getPost('type');
			$data['dateOff'] = $this->request->getPost('dateoff');
			
			//1 - пользователь выбрал обязательный довод  // 0 - не выбрал
			$data['checkRequired'] = $this->request->getPost('checkrequired');

			if (!$CurrentStep || !is_numeric($CurrentStep)) throw new Exception('bad step');
			if (!$data['type'] || !$this->checkTypePurchase($data['type'])) throw new Exception('bad type');
			if (!$data['dateOff'] || trim($data['dateOff']) == '') throw new Exception('bad date');

			// 0 - не просрочено // 1 - просрочено
			$data['checkDate'] = $this->checkDateEndSendApp2($data['dateOff'], $result);

			switch($CurrentStep){
				case self::STEP_ONE:
					$cat = new ArgumentsCategory();
					$cat_arguments = $cat->getCategoryNotEmpty($data['type'], $data['checkDate'], $data['checkRequired']);
					$temp_name = array();

					foreach($cat_arguments as $cat){
						if(!in_array($cat->lvl1, $temp_name)){
							$temp_name[] = $cat->lvl1;
							$result['cat_arguments'][] = array(
								'id' => $cat->lvl1_id,
								'name' => $cat->lvl1,
								'required' => $cat->lvl1_required,
								'parent_id' => 0
							);
						}
					}
					echo json_encode($result);
					break;
				case self::STEP_TWO:
					$parent_id = $this->request->getPost('id');
					if (!$parent_id || !is_numeric($parent_id)) throw new Exception('bad data');

					$cat = new ArgumentsCategory();
					$cat_arguments = $cat->getCategoryNotEmpty($data['type'], $data['checkDate'], $data['checkRequired']);
					$temp_name = array();

					foreach($cat_arguments as $cat){
						if($cat->lvl1_id == $parent_id){
							if(!in_array($cat->lvl2, $temp_name)){
								$temp_name[] = $cat->lvl2;
								$result["cat_arguments"][] = array(
									"id" => $cat->lvl2_id,
									"name" => $cat->lvl2,
									'required' => $cat->lvl2_required,
									"parent_id" => $cat->lvl1_id,
								);
							}
						}
					}
					echo json_encode($result);
					break;
				case self::STEP_THREE:
					$id = $this->request->getPost('id');

					if (!$id || !is_numeric($id)) throw new Exception('bad data');
					$parent_id = ArgumentsCategory::findFirst($id);
					if (!$parent_id) throw new Exception('no cat');

					//Получить не пустые категории (в которых есть доводы)
					$cat_arguments = new Builder();
					$cat_arguments->getDistinct();
					$cat_arguments->addFrom('Multiple\Backend\Models\ArgumentsCategory', 'ArgumentsCategory');
					$cat_arguments->rightJoin('Multiple\Backend\Models\Arguments', "ArgumentsCategory.id = category_id AND type LIKE '%{$data['type']}%'");
					$cat_arguments->where("parent_id = {$id}");
					if ($data['checkDate'] == 1 && $data['checkRequired'] == 0) {
						$cat_arguments->andWhere("ArgumentsCategory.required = 1");
						$cat_arguments->andWhere("Multiple\Backend\Models\Arguments.required = 1");
					}elseif ($data['checkDate'] == 0){
						$cat_arguments->andWhere("ArgumentsCategory.required = 0");
						$cat_arguments->andWhere("Multiple\Backend\Models\Arguments.required = 0");
					}
					$cat_arguments->groupBy('ArgumentsCategory.id');
					$cat_arguments = $cat_arguments->getQuery()->execute();

					//Если категорий нет, то получаем доводы и добавляем их в результирующий массив $result
					if(count($cat_arguments) == 0){
						$arguments = Arguments::query();
						$arguments->where("category_id = {$id}");
						$this->showRequiredOrNotRequired($arguments, $data);
						$arguments->andWhere("type LIKE '%{$data['type']}%'");
						$arguments = $arguments->execute();
						$this->setArgumentsInResult($arguments, $data['type'], $result);
                    }else{
						foreach ($cat_arguments as $cat) {
							$result['cat_arguments'][] = array(
								'id' => $cat->id,
								'name' => $cat->name,
								'required' => $cat->required,
								'parent_id' => $cat->parent_id,
							);
						}
					}
					echo json_encode($result);
					break;
				case self::STEP_FOUR:
					$id = $this->request->getPost('id');
					if(!$id || !is_numeric($id)) throw new Exception('bad data');

					$arguments = Arguments::query();
					$arguments->where("category_id = {$id}");
					$this->showRequiredOrNotRequired($arguments, $data);
					$arguments->andWhere("type LIKE '%{$data['type']}%'");
					$arguments = $arguments->execute();

					$this->setArgumentsInResult($arguments, $data['type'], $result);
					echo json_encode($result);
					break;
				case self::STEP_SEARCH:
					$search = $this->request->getPost('search');
					$search = (!empty($search)) ? trim($search) : '';

					if(empty($search)){
						echo json_encode($result);
						exit;
					}

					$arguments = Arguments::query();
					$arguments->where('name LIKE :name:', array('name' => '%' . $search . '%'));
					$this->showRequiredOrNotRequired($arguments, $data);
					$arguments->andWhere("type LIKE '%{$data['type']}%'");
					$arguments = $arguments->execute();

					$this->setArgumentsInResult($arguments, $data['type'], $result);
					echo json_encode($result);
					break;
			}
		}catch (Exception $e){
			echo json_encode(array(
				"error" => $e->getMessage()
			));
		}
		exit;
	}
	
	private function checkTypePurchase($type)
    {
        $checkType = false;
        switch ($type) {
            case 'electr_auction':
                $checkType = true;
                break;
            case 'concurs':
                $checkType = true;
                break;
            case 'kotirovok':
                $checkType = true;
                break;
            case 'offer':
                $checkType = true;
                break;
        }
        return $checkType;
    }
	
	private function checkDateEndSendApp2($dateOff, &$result = false)
    {
        $dateOff = strtotime($dateOff);
        $nowTime = strtotime("now");

        if ($nowTime > $dateOff) {
            if ($result) {
                $result['date'] = 1;
            }
            return 1;
        }
        return 0;
    }
	
	private function showRequiredOrNotRequired($arguments, $data)
    {
        if ($data['checkDate'] == 1 && $data['checkRequired'] == 0) $arguments->andWhere("required = 1");
        if ($data['checkDate'] == 0) $arguments->andWhere("required = 0");
    }
	
	private function setArgumentsInResult($arguments, $type, &$result)
    {
        foreach ($arguments as $argument) {
            $result['arguments'][] = array(
                'id' => $argument->id,
                'text' => $argument->text,
                'name' => $argument->name,
                'category_id' => $argument->category_id,
                'comment' => $argument->comment,
                'required' => $argument->required,
                'type' => ($argument->type != '') ? explode(',', $argument->type) : array()
            );
        }
    }
	
	public function createAction()
    {
        if (!$this->request->isPost()) {
            echo 'error';
            exit;
        }
        $users_arguments_ = [];
        $data = $this->request->getPost();
        $data['auctionData'] = explode('&', $data['auctionData']);
        $users_arguments = explode('_?_', $data['arguments_data']);

        $ufas_id = null;
        if (isset($data['ufas_id']) && is_numeric($data['ufas_id'])) {
            $ufas_id = Ufas::findFirst(array(
                "number={$data['ufas_id']}"
            ));
            if ($ufas_id) $ufas_id = $ufas_id->id;
        }
        $data['ufas_id'] = $ufas_id;


        unset($users_arguments[count($users_arguments) - 1]);
        foreach ($users_arguments as $key => $row) {
            $users_arguments[$key] = explode('?|||?', $row);
        }
        foreach ($users_arguments as $key => &$row) {
            //$cnt = count($row);
            foreach ($row as $data_) {
                $data_ = explode('===', $data_);
                $users_arguments_[$key][$data_[0]] = $data_[1];
                /*if (isset($users_arguments_[$key]['argument_id']) && $users_arguments_[$key]['argument_id'] == 'just_text') {
                    $data['complaint_text'] = $data_[1];
                    $data['complaint_text_order'] = $users_arguments_[$key]['order'];
                }*/
            }
            /*for ($ind = 0; $ind < $cnt; $ind++) {
                unset($row[$ind]);
            }*/
            //$users_arguments[$key] = explode('?|||?', $row);
        }
        foreach ($data['auctionData'] as $value) {
            $value = explode('=', $value);
            $data["{$value[0]}"] = $value[1];
        }
        $complaint = new Complaint();
        $complaint->addComplaint($data);
		if ($complaint->save() == false) {
			foreach ($complaint->getMessages() as $message) {
                $this->flashSession->error($message);
            }
            return $this->response->redirect('/admin/complaints/add?select_user_id='.$_GET["select_user_id"].'&select_applicant_id='.$_GET["select_applicant_id"]);
		}else{
			$allow = TRUE;
			
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
                            $applicant_file->file_path = Translit::rusToEng($name);
                            $applicant_file->file_size = round($file->getSize() / 1024, 2);
                            $applicant_file->file_type = $file->getType();
                            $applicant_file->save();
                            $saved_files[] = $applicant_file->id;
                            //Move the file into the application
                            $file->moveTo($baseLocation . Translit::rusToEng($name));
                        }
                    }
                }
                $complaint->fid = serialize($saved_files);
				$complaint->save();
			}
		
			echo json_encode(array(
					'complaint' => array(
						'id' => $complaint->id
					)
				));
		}
        /*if ($complaint->save() == false) {
            //$this->flashSession->error('Не выбран заявитель');
            foreach ($complaint->getMessages() as $message) {
                $this->flashSession->error($message);
            }
            return $this->response->redirect('/complaint/add');
            //$response = array('result' => 'error', 'message' => 'Ошибка при попытке сохранения жалобы');
        } else {
            $allow = TRUE;

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
                            $applicant_file->file_path = Translit::rusToEng($name);
                            $applicant_file->file_size = round($file->getSize() / 1024, 2);
                            $applicant_file->file_type = $file->getType();
                            $applicant_file->save();
                            $saved_files[] = $applicant_file->id;
                            //Move the file into the application
                            $file->moveTo($baseLocation . Translit::rusToEng($name));
                        }
                    }
                }
                $complaint->fid = serialize($saved_files);
                //$this->flashSession->error($applicant->fid);
                $complaint->user_id = $data["user_id"];
                $complaint->save();
                $docx_s = DocxFiles::find("complaint_name = '{$complaint->complaint_name}'");
                foreach ($docx_s as $docx) {
                    $docx->complaint_id = $complaint->id;
                    $docx->save();
                }
            }
            Log::addAdminLog("Создание жалобы", "Добавлена жалоба {$complaint->id}", $this->user, null, 'пользователь');
            $this->flashSession->success('Жалоба сохранена');
            echo json_encode(array(
                'complaint' => array(
                    'id' => $complaint->id
                )
            ));
            exit;
            //return $this->response->redirect('complaint/edit/' . $complaint->id . '?action=edit');
            //$response = array('result' => 'success', 'id' => $complaint->id);
        }*/
        /*header('Content-type: application/json');
        echo json_encode($response);
        exit;*/
		exit;
    }
	
	public function saveHtmlFileAction()
    {
        //error_reporting(E_ALL);
        //ini_set('display_errors', 1);

        $name = false;
        $format = 1;
        $recall = 0;
        $recall = $this->request->get('recall');

        if ($this->request->getPost('doc')) {
            $baseLocation = 'files/generated_complaints/user_' . $this->user->id . '/';
            if (strlen($this->request->getPost('doc'))) {
                if (!file_exists($baseLocation)) {
                    mkdir($baseLocation, 0777, true);
                }

                try {
                    if (empty($recall)) {
                        $unformatted = isset($_GET['unformatted']) ? 'unformatted_' : '';
                        if ($unformatted == 'unformatted_') {
                            $format = 0;
                        }
                        $name = 'complaint_' . $unformatted . time() . '.docx';
                        $data = json_decode($this->request->getPost('doc'));

                        require_once $_SERVER['DOCUMENT_ROOT'] . '/public/phpdocx/classes/CreateDocx.inc';
                        $docx = new \CreateDocxFromTemplate($_SERVER['DOCUMENT_ROOT'] . "/public/js/docx_generator/docx_templates/" . $this->request->getPost('file_to_load'));

                        foreach ($data as $key => $value) {
                            if ($key == 'dovod') {
                                /*preg_match_all('/<img.*?src\s*=(.*?)>/', $value, $out);
                                if (count($out[1])) {
                                    foreach ($out[1] as $key1 => $image) {
                                        $explode = explode(" ", $image);
                                        $image = trim($explode[0], '"');

                                        $file_name = time() + rand();

                                        if(substr_count($image, 'data:image'))
                                            $value = str_replace($out[0][$key1], '<img src="' . $_SERVER['DOCUMENT_ROOT'] . "/files/generated_complaints/user_" . $this->user->id . "/" . $this->save_base64_image($image, time() + rand(), $_SERVER['DOCUMENT_ROOT'] . "/files/generated_complaints/user_" . $this->user->id . "/") . '"><br/>', $value);
                                    }
                                }*/

                                if (trim($value) == '') $value = '  ';
                                $docx->replaceVariableByHTML($key, 'block', $value, array('isFile' => false, 'parseDivsAsPs' => true, 'downloadImages' => true));
                            } else
                                if (trim($value) == '') $value = '  ';
                            $docx->replaceVariableByHTML($key, 'inline', $value, array('isFile' => false, 'parseDivsAsPs' => true, 'downloadImages' => true));
                        }

                        //$templateProcessor->saveAs($baseLocation . $name);
                        $docx->createDocx($baseLocation . $name);

                        //$file->moveTo($baseLocation . $name);

                    } else {
                        $unformatted = isset($_GET['unformatted']) ? 'unformatted_' : '';
                        $name = 'recall_' . $unformatted . time() . '.docx';
                        $recall = 1;

                        $data = json_decode($this->request->getPost('doc'));
                        require_once $_SERVER['DOCUMENT_ROOT'] . '/public/phpdocx/classes/CreateDocx.inc';
                        $docx = new \CreateDocxFromTemplate($_SERVER['DOCUMENT_ROOT'] . "/public/js/docx_generator/docx_templates/" . $this->request->getPost('file_to_load'));

                        foreach ($data as $key => $value) {
                            if (trim($value) == '') $value = '  ';
                            $docx->replaceVariableByHTML($key, 'block', $value, array('isFile' => false, 'parseDivsAsPs' => true, 'downloadImages' => false));
                        }

                        $docx->createDocx($baseLocation . $name);
                        //$file->moveTo($baseLocation . $name);
                    }

                } catch (\Exception $e) {
                    print_R('ok=');
                    print_R($e);
                    die;
                }

            }
            $docx = new DocxFiles();
            if (!empty($recall)) {
                $docx->complaint_id = $this->request->get('complaint_id');
            }
            $docx->docx_file_name = $name;


            $tempCompPost = $this->request->getPost('complaint_id');
            $tempCompGet = $this->request->getQuery('complaint_id');
            if (is_numeric($tempCompPost)) {
                $compl_id = $tempCompPost;
            } elseif (is_numeric($tempCompGet)) {
                $compl_id = $tempCompGet;
            }

            $docx->complaint_name = $this->request->getPost('complaint_name');
            if (isset($compl_id) && $compl_id != 'undefined') {
                $delete_docx = DocxFiles::find("complaint_id = $compl_id");
                if (count($delete_docx) >= 2) {
                    foreach ($delete_docx as $del_docx) {
                        $del = @unlink($baseLocation . $del_docx->docx_file_name);
                        $del = @unlink($baseLocation . $del_docx->docx_file_name . '.sig');
                        $del_docx->delete();
                    }
                } else {
                    $delete_docx = DocxFiles::find("complaint_id = $compl_id AND recall = 1");
                    foreach ($delete_docx as $del_docx) {
                        $del = @unlink($baseLocation . $del_docx->docx_file_name);
                        $del = @unlink($baseLocation . $del_docx->docx_file_name . '.sig');
                        $del_docx->delete();
                    }
                }
            }
            $docx->created_at = date('Y-m-d H:i:s');
            $docx->recall = $recall;
            $docx->format = $format;
            $docx->complaint_id = $compl_id;
            $docx->user_id = $this->user->id;
            $docx->save();
        }
        $this->view->disable();
        if ($name) {
            $thumbprint = 0;
            if (isset($_POST['applicant_id'])) {
                $applicant_id = $_POST['applicant_id'];
                //"activ = 1 AND applicant_id = $applicant_id "

                $thumbprint = ApplicantECP::findFirst(array(
                    "conditions" => "activ = ?1 AND applicant_id = ?2",
                    "bind" => [
                        1 => 1,
                        2 => $applicant_id,
                    ],
                    'order' => 'id DESC'
                ));
                $thumbprint = $thumbprint->thumbprint;
            }
            $data = file_get_contents($baseLocation . $name);
            $File_data = base64_encode($data);
            echo json_encode([$File_data, $thumbprint, $name]);
        } else {
            echo 'error';
        }
        die();
        //0190300004615000296
        //  skyColor
    }
	
	
}