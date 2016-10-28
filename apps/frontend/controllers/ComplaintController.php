<?php

namespace Multiple\Frontend\Controllers;

use Multiple\Frontend\Models\ApplicantECP;
use Multiple\Frontend\Models\Applicant;
use Multiple\Frontend\Models\Category;
use Multiple\Frontend\Models\Complaint;
use Multiple\Frontend\Models\ComplaintMovingHistory;
use Multiple\Frontend\Models\Question;
use Multiple\Frontend\Models\UsersArguments;
use Multiple\Frontend\Models\DocxFiles;
use Multiple\Frontend\Models\Files;
use Multiple\Backend\Models\Ufas;
use Multiple\Library\Parser;
use Phalcon\Acl\Exception;
use Phalcon\Mvc\Controller;
use \Phalcon\Paginator\Adapter\NativeArray as Paginator;
use Multiple\Library\PaginatorBuilder;
use Multiple\Frontend\Models\Arguments;
use Multiple\Frontend\Models\ArgumentsCategory;
use  Phalcon\Mvc\Model\Query\Builder;
use Multiple\Frontend\Models\Messages;
use Phalcon\Mvc\Url;
use Multiple\Library\Translit;



class ComplaintController extends ControllerBase
{
    const STEP_ONE = 1;
    const STEP_TWO = 2;
    const STEP_THREE = 3;
    const STEP_FOUR = 4;
    const STEP_SEARCH = 6;


    public function testAction(){

    }

    public function indexAction()
    {
        if (!$this->user) {
             $this->flashSession->error('Вы не залогинены в системе');
             return $this->response->redirect('/');
        }
        $search = $this->request->get('search');
        $search =  preg_replace ("/[^a-zA-ZА-Яа-я0-9\s]/u","", $search);



        $this->setMenu();
        $complaint = new Complaint();
        $status = 0;
        $numberPage = $this->request->getQuery("page", "int");
        if($numberPage===null) $numberPage = 1;
        if (isset($_GET['status']))
            $status = $_GET['status'];
      
        $complaints = $complaint->findUserComplaints($this->user->id, $status, $this->applicant_id, $search);
        #$this->view->complaints = $complaints;
        $this->view->status = $status;
        $paginator = new Paginator(array(
            "data"  => $complaints,
            "limit" => 10,
            "page"  => $numberPage
        ));
        $pages = $paginator->getPaginate();
        if($status){
            $url = '/complaint/index?status='.$status;
        } else {
            $url = '/complaint/index';
        }
        $this->view->searchurl = $url;
        $this->view->searhparam = $search;
        $this->view->page = $pages;
        $this->view->paginator_builder = PaginatorBuilder::buildPaginationArray($numberPage, $pages->total_pages);
        $this->view->index_action = true;

    }

    public function deleteFileAction() {
        $file_id = $this->request->getPost('file_id');
        $complaint_id = $this->request->getPost('complaint_id');
        if ($file_id && $complaint_id) {
            $complaint = Complaint::findFirstById($complaint_id);
            if ($complaint) {
                $file = Files::findFirstById($file_id);
                if ($file) {
                    $file->delete();
                    $complaint_files = unserialize($complaint->fid);
                    if (count($complaint_files)) {
                        unset($complaint_files[array_search($file_id, $complaint_files)]);
                        $complaint->fid = serialize(array_values($complaint_files));
                    } else {
                        $complaint->fid = serialize(array());
                    }
                    $complaint->save();
                    //$this->flashSession->success('Файл удален');
                }
            }
        }
        $this->view->disable();
        $data = "ok";
        echo json_encode($data);
    }

    public function editAction($id)
    {
        $complaint = Complaint::findFirstById($id);
        if (!$complaint || !$complaint->checkComplaintOwner($id, $this->user->id))
            return $this->forward('complaint/index');
        $applicant = Applicant::findFirstById($complaint->applicant_id);
        $this->session->set('save_applicant', $this->session->get('applicant'));
        $this->session->set('applicant', array('applicant_id' => $complaint->applicant_id));
        // Load arguments
        $category = new Category();
        $arguments = $category->getArguments();
        $this->view->arguments = $arguments;
        // Load users arguments
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
            $arr_users_arg[$argument->argument_id] = preg_replace('/[\r\n\t]/', '', $argument->text);
            /*if ($argument_order == $complaint->complaint_text_order) {
                $user_arguments .= $complaint->complaint_text . '</br>';
                $user_arguments .= $argument->text . '</br>';
            } else {*/
                $user_arguments .= $argument->text . '</br>';
            //}
            $arr_sub_cat[] = array(
                'id' => $argument->argument_id,
                'text' =>  preg_replace('/[\r\n\t]/', '', $argument->text)
            );
            ++$argument_order;
        }
        if (!empty($arr_sub_cat)) {
            $this->view->arr_sub_cat = $arr_sub_cat;
        }
        $this->view->arr_users_arg = $arr_users_arg;
        $this->view->categories_id = implode(',', $categories_id);
        $this->view->arguments_id = implode(',', $arguments_id);
        //$this->view->complaint_text_order = $complaint->complaint_text_order;

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
            $ufas = Ufas::find();
            $this->view->ufas = $ufas;
        } else {
            $this->view->edit_now = FALSE;
        }

        $m_user_id = $this->user->id;
        $messages = Messages::find("comp_id = {$id} AND to_uid = {$m_user_id} ORDER BY time DESC");
        if ($messages) {
            foreach ($messages as $mess) {
                $mess->is_read = 1;
                $mess->update();
            }
        }
        $this->view->user_arguments = $user_arguments;
        $history = ComplaintMovingHistory::find("complaint_id = $id ORDER BY date DESC");
        if ($history) {
            foreach ($history as $hist) {
                $hist->is_read = 1;
                $hist->update();
            }
        }
        $this->view->attached_files = $files_html;
        $complaint->purchases_name = str_replace("\r\n", " ", $complaint->purchases_name);
        $question = new Question();
        $complaintQuestion = $question->getComplainQuestionAndAnswer($id);
        $this->setMenu();

        $this->view->applicant_session = $applicant->id;
        $this->applicant_id = $applicant->id;
//        $parser = new Parser();
//        $data = $parser->parseAuction((string)$complaint->auction_id);
//
//        $complaint->nachalo_podachi =           isset($data['procedura']['nachalo_podachi'])            ? $data['procedura']['nachalo_podachi']         : null;
//        $complaint->okonchanie_podachi =        isset($data['procedura']['okonchanie_podachi'])         ? $data['procedura']['okonchanie_podachi']      : null;
//        $complaint->okonchanie_rassmotreniya =  isset($data['procedura']['okonchanie_rassmotreniya'])   ? $data['procedura']['okonchanie_rassmotreniya']: null;
//        $complaint->data_provedeniya =          isset($data['procedura']['data_provedeniya'])           ? $data['procedura']['data_provedeniya']        : null;
//        $complaint->vremya_provedeniya =        isset($data['procedura']['vremya_provedeniya'])         ? $data['procedura']['vremya_provedeniya']      : null;
//        $complaint->vskrytie_konvertov =        isset($data['procedura']['vskrytie_konvertov'])         ? $data['procedura']['vskrytie_konvertov']      : null;
//        $complaint->data_rassmotreniya =        isset($data['procedura']['data_rassmotreniya'])         ? $data['procedura']['data_rassmotreniya']      : null;

        $this->view->ufas_name = 'Уфас не определен';
        $this->view->comp_inn = 'null';
        if($complaint->ufas_id != null){
            $ufas_name = Ufas::findFirst(array(
                "id={$complaint->ufas_id}"
            ));
            if($ufas_name){
                $this->view->ufas_name = $ufas_name->name;
                $this->view->comp_inn = $ufas_name->number;
            }
        }



        if(is_null($complaint->date_start)) $complaint->date_start = $complaint->nachalo_podachi;


        ;

        $this->view->date_end = $this->checkDateEndSendApp($complaint->okonchanie_podachi);
        $this->view->edit_mode = 1;
        $this->view->complaint = $complaint;
        $this->view->complaint_question = $complaintQuestion;
        $this->view->action_edit = false;
        if (isset($_GET['action']) && $_GET['action'] == 'edit' && $complaint->status =='draft')
            $this->view->action_edit = true;
        unset($data);
    }

    public function saveBlobFileAction() {
        $name = false;
        if ($this->request->hasFiles() == true) {
            $baseLocation = 'files/generated_complaints/user_' . $this->user->id . '/';
            foreach ($this->request->getUploadedFiles() as $file) {
                if (strlen($file->getName())) {
                    if (!file_exists($baseLocation)) {
                        mkdir($baseLocation, 0777, true);
                    }
                    $unformatted = isset($_GET['unformatted']) ? 'unformatted_' : '';
                    $name = 'complaint_' . $unformatted . time() . '.docx';
                    $file->moveTo($baseLocation . $name);
                }
            }
            $docx = new DocxFiles();
            $docx->docx_file_name = $name;
            $compl_id = $this->request->getPost('complaint_id');
            $docx->complaint_name = $this->request->getPost('complaint_name');
            if (isset($compl_id) && $compl_id != 'undefined') {
                $delete_docx = DocxFiles::find("complaint_id = $compl_id");
                foreach ($delete_docx as $del_docx) {
                    $del = unlink($baseLocation . $del_docx->docx_file_name);
                    $del_docx->delete();
                }
            }
            $docx->user_id = $this->user->id;
            $docx->save();
        }
        $this->view->disable();
        if($name) {
            $thumbprint = 0;
            if(isset($_POST['applicant_id'])) {
                $applicant_id = $_POST['applicant_id'];
                $thumbprint = ApplicantECP::findFirst("activ = 1 AND applicant_id = $applicant_id ");
                $thumbprint = $thumbprint->thumbprint;
            }
            $data = file_get_contents($baseLocation . $name);
            $File_data = base64_encode($data);
            echo json_encode([$File_data,$thumbprint,$name]);
        }else{
            echo 'error';
        }
        die();
        //0190300004615000296
        //  skyColor
    }
    public function signatureAction(){

        $signature = $this->request->getPost('signature');
        $signFileOriginName  = $this->request->getPost('signFileOriginName');
        $baseLocation = 'files/generated_complaints/user_' . $this->user->id . '/';
        file_put_contents($baseLocation. $signFileOriginName.'.sig',base64_decode($signature));
        
        echo 'done';
        exit;
        
    }
    public function addAction()
    {

        $this->setMenu();
        $category = new Category();
        $arguments = $category->getArguments();
        $ufas = Ufas::find();

        $this->view->edit_mode = 0;
        $this->view->ufas = $ufas;
        $this->view->arguments = $arguments;
    }

    public function deleteAction($id){
        $complaint = Complaint::findFirst($id);
        if (!$complaint || !$complaint->checkComplaintOwner($id, $this->user->id))
            return $this->forward('complaint/index');

        if ($complaint != false) {
            $complaint->delete();
        }
        $this->flashSession->success('Жалоба успешно удалена');
        return $this->response->redirect('/complaint/index');
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
        if(isset($data['ufas_id']) && is_numeric($data['ufas_id'])){
            $ufas_id = Ufas::findFirst(array(
                "number={$data['ufas_id']}"
            ));
            if($ufas_id) $ufas_id = $ufas_id->id;
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
                $complaint->save();
                $docx_s = DocxFiles::find("complaint_name = '{$complaint->complaint_name}'");
                foreach ($docx_s as $docx) {
                    $docx->complaint_id = $complaint->id;
                    $docx->save();
                }
            }
            $this->flashSession->success('Жалоба сохранена');
            return $this->response->redirect('complaint/edit/' . $complaint->id . '?action=edit');
            //$response = array('result' => 'success', 'id' => $complaint->id);
        }
        /*header('Content-type: application/json');
        echo json_encode($response);
        exit;*/
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
                /*if (isset($users_arguments_[$key]['argument_id']) && $users_arguments_[$key]['argument_id'] == 'just_text') {
                    $data['complaint_text'] = $data_[1];
                    $data['complaint_text_order'] = $users_arguments_[$key]['order'];
                }*/
            }
        }
        $complaint = Complaint::findFirstById($data['update-complaint-id']);
        if ($complaint) {
            $complaint->complaint_name = $data['complaint_name'];
            //$complaint->complaint_text = $data['complaint_text'];
            //$complaint->complaint_text_order = $data['complaint_text_order'];
            $complaint->complaint_text = $data['complaint_text'];
            $complaint->complaint_text_order = $data['complaint_text_order'];
            $ufas = Ufas::findFirst(array(
                "number = {$data['ufas_id']}"
            ));
            if($ufas){
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

            $data = $_FILES;
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
                $docx_s = DocxFiles::find("complaint_name = '{$complaint->complaint_name}'");
                foreach ($docx_s as $docx) {
                    $docx->complaint_id = $complaint->id;
                    $docx->save();
                }
            }
            $this->flashSession->success('Жалоба обновлена');
            return $this->response->redirect('complaint/edit/' . $complaint->id);
        }
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
        return $this->response->redirect('/complaint/edit/' . $complaint_id);
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
        //$this->flashSession->success('Копия жалобы создана');
        echo $result;
        exit;
    }

    function isComplaintNameUnicAction() {
        $this->view->disable();
        $complaint_name = $this->request->get('complaint_name');
        $complaint_id = $this->request->get('complaint_id');
        $and_complaint_where = '';
        if (isset($complaint_id)) {
            $and_complaint_where = " AND id != {$complaint_id}";
        }
        $response['name_unic'] = TRUE;
        if ($complaint_name) {
            $db = $this->getDi()->getShared('db');
            $result = $db->query("SELECT id FROM complaint WHERE complaint_name = '{$complaint_name}'{$and_complaint_where}");
            $id = $result->fetch();
            if ($id) {
                $response['name_unic'] = FALSE;
            }
        }
        header('Content-type: application/json');
        echo json_encode($response);
        die();
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

    /* ADD COMPLICANT */
    public function ajaxStepsAddComplaintAction(){
        try {
            $data = array();
            $result = array(
                "cat_arguments" => array(),
                "arguments" => array(),
                "date" => 0
            );
            $CurrentStep =  $this->request->get('step');
            $data['type'] = $this->request->getPost('type');
            $data['dateOff'] = $this->request->getPost('dateoff');

            //1 - пользователь выбрал обязательный довод  // 0 - не выбрал
            $data['checkRequired'] = $this->request->getPost('checkrequired');

            if (!$CurrentStep || !is_numeric($CurrentStep)) throw new Exception('bad step');
            if (!$data['type'] || !$this->checkTypePurchase($data['type'])) throw new Exception('bad type');
            if (!$data['dateOff'] || trim($data['dateOff']) == '')  throw new Exception('bad date');

            // 0 - не просрочено // 1 - просрочено
            $data['checkDate'] =  $this->checkDateEndSendApp($data['dateOff'], $result);

            switch ($CurrentStep) {
                case self::STEP_ONE:
                    $cat = new ArgumentsCategory();
                    $cat_arguments = $cat->getCategoryNotEmpty($data['type'], $data['checkDate'], $data['checkRequired']);
                    $temp_name = array();

                    foreach ($cat_arguments as $cat) {
                        if (!in_array($cat->lvl1, $temp_name)) {
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

                    foreach ($cat_arguments as $cat) {
                        if ($cat->lvl1_id == $parent_id) {
                            if (!in_array($cat->lvl2, $temp_name)) {
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
                    $cat_arguments->addFrom('Multiple\Frontend\Models\ArgumentsCategory', 'ArgumentsCategory');
                    $cat_arguments->rightJoin('Multiple\Frontend\Models\Arguments', "ArgumentsCategory.id = category_id AND type LIKE '%{$data['type']}%'");
                    $cat_arguments->where("parent_id = {$id}");
                    if ($data['checkDate'] == 1 && $data['checkRequired'] == 0) {
                        $cat_arguments->andWhere("ArgumentsCategory.required = 1");
                        $cat_arguments->andWhere("Multiple\Frontend\Models\Arguments.required = 1");
                    } else if($data['checkDate'] == 0){
                        $cat_arguments->andWhere("ArgumentsCategory.required = 0");
                        $cat_arguments->andWhere("Multiple\Frontend\Models\Arguments.required = 0");
                    }
                    $cat_arguments->groupBy('ArgumentsCategory.id');
                    $cat_arguments = $cat_arguments->getQuery()->execute();

                    //Если категорий нет, то получаем доводы и добавляем их в результирующий массив $result
                    if(count($cat_arguments) == 0) {
                        $arguments = Arguments::query();
                        $arguments->where("category_id = {$id}");
                        $this->showRequiredOrNotRequired($arguments, $data);
                        $arguments->andWhere("type LIKE '%{$data['type']}%'");
                        $arguments = $arguments->execute();
                        $this->setArgumentsInResult($arguments, $data['type'], $result);
                    } else {
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
                    if (!$id || !is_numeric($id)) throw new Exception('bad data');

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

                    if (empty($search)) {
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
        } catch (Exception $e){
            echo json_encode(array(
                "error" => $e->getMessage()
            ));
        }
        exit;
    }


    private function checkTypePurchase($type ){
        $checkType = false;
        switch ($type){
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

    private function setArgumentsInResult($arguments, $type, &$result ){
        foreach($arguments as $argument){
            $result['arguments'][] = array(
                'id'            => $argument->id,
                'text'          => $argument->text,
                'name'          => $argument->name,
                'category_id'   => $argument->category_id,
                'comment'       => $argument->comment,
                'required'      => $argument->required,
                'type'          => ($argument->type != '') ? explode(',', $argument->type) : array()
            );
        }
    }

    private function checkDateEndSendApp($dateOff, &$result = false){
        $dateOff = strtotime($dateOff);
        $nowTime = strtotime("now");

        if($nowTime > $dateOff){
            if($result) {
                $result['date'] = 1;
            }
            return 1;
        }
        return 0;
    }

    private function showRequiredOrNotRequired($arguments, $data){
        if ($data['checkDate'] == 1 && $data['checkRequired'] == 0) $arguments->andWhere("required = 1");
        if ($data['checkDate'] == 0) $arguments->andWhere("required = 0");
    }
}