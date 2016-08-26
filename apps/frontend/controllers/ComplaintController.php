<?php

namespace Multiple\Frontend\Controllers;


use Multiple\Frontend\Models\Applicant;
use Multiple\Frontend\Models\Category;
use Multiple\Frontend\Models\Complaint;
use Multiple\Frontend\Models\ComplaintMovingHistory;
use Multiple\Frontend\Models\Question;
use Multiple\Frontend\Models\UsersArguments;
use Multiple\Frontend\Models\Files;
use Multiple\Backend\Models\Ufas;
use Multiple\Library\Parser;
use Phalcon\Mvc\Controller;
use \Phalcon\Paginator\Adapter\NativeArray as Paginator;
use Multiple\Library\PaginatorBuilder;
use Multiple\Frontend\Models\Arguments;
use Multiple\Frontend\Models\ArgumentsCategory;
//use Multiple\Library\TrustedLibrary;
use  Phalcon\Mvc\Model\Query\Builder;


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
        //TrustedLibrary::trusted_library_init();
        $complaint = Complaint::findFirstById($id);
        if (!$complaint || !$complaint->checkComplaintOwner($id, $this->user->id))
            return $this->forward('complaint/index');

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
            $arr_users_arg[$argument->argument_id] = $argument->text;
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

        if(is_null($complaint->date_start)) $complaint->date_start = $complaint->nachalo_podachi;

        $this->view->complaint = $complaint;
        $this->view->complaint_question = $complaintQuestion;
        $this->view->action_edit = false;
        if (isset($_GET['action']) && $_GET['action'] == 'edit' && $complaint->status =='draft')
            $this->view->action_edit = true;
        unset($data);
    }

    public function saveBlobFileAction() {
        if ($this->request->hasFiles() == true) {
            $baseLocation = 'files/generated_complaints/user_' . $this->user->id . '/';
            foreach ($this->request->getUploadedFiles() as $file) {
                if (strlen($file->getName())) {
                    if (!file_exists($baseLocation)) {
                        mkdir($baseLocation, 0777, true);
                    }
                    $name = 'complaint_' . time() . '.docx';
                    $file->moveTo($baseLocation . $name);
                }
            }
        }
        $this->view->disable();
        die();
    }

    public function addAction()
    {
        //TrustedLibrary::trusted_library_init();        
        $this->setMenu();
        $category = new Category();
        $arguments = $category->getArguments();
        $ufas = Ufas::find();

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


//        $data = ArgumentsCategory::query()
//                ->where('parent_id=0')
//                ->execute();

//        $arg = new ArgumentsCategory();
//        $data = $arg->getCategoryNotEmpty();
//        $temp = array();
//        foreach($data as $val){
//            if($val->parent_id == 0) {
//                $temp[] = array(
//                    'id' => $val->lvl1_id,
//                    'name' => $val->lvl1,
//                    'parent_id' => 0
//                );
//            }
//        }
//        $temp = array_map("unserialize", array_unique( array_map("serialize", $temp) ));

        //$arg = new ArgumentsCategory();
        //$this->view->categories = $temp;
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
        unset($users_arguments[count($users_arguments) - 1]);
        foreach ($users_arguments as $key => $row) {
            $users_arguments[$key] = explode('?|||?', $row);
        }
        foreach ($users_arguments as $key => &$row) {
            //$cnt = count($row);
            foreach ($row as $data_) {
                $data_ = explode('===', $data_);
                $users_arguments_[$key][$data_[0]] = $data_[1];
                if (isset($users_arguments_[$key]['argument_id']) && $users_arguments_[$key]['argument_id'] == 'just_text') {
                    $data['complaint_text'] = $data_[1];
                    $data['complaint_text_order'] = $users_arguments_[$key]['order'];
                }
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
                            $applicant_file->file_path = $this->translit($name);
                            $applicant_file->file_size = round($file->getSize() / 1024, 2);
                            $applicant_file->file_type = $file->getType();
                            $applicant_file->save();
                            $saved_files[] = $applicant_file->id;
                            //Move the file into the application
                            $file->moveTo($baseLocation . $this->translit($name));
                        }
                    }
                }
                $complaint->fid = serialize($saved_files);
                //$this->flashSession->error($applicant->fid);
                $complaint->save();
            }
            $this->flashSession->success('Жалоба сохранена');
            return $this->response->redirect('complaint/edit/' . $complaint->id);
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
                            $applicant_file->file_path = $this->translit($name);
                            $applicant_file->file_size = round($file->getSize() / 1024, 2);
                            $applicant_file->file_type = $file->getType();
                            $applicant_file->save();
                            $saved_files[] = $applicant_file->id;
                            //Move the file into the application
                            $file->moveTo($baseLocation . $this->translit($name));
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

    public function ajaxStepsAddComplaintAction(){
        $step = $this->request->get('step');
        if(!is_numeric($step)){
            echo json_encode(array('error' => 'bad data'));
            exit;
        }
        $result = array(
            "cat_arguments" => array(),
            "arguments"     => array(),
            "date"          => 0
        );
        switch($step){
            case 1:
                $type       = $this->request->getPost('type');
                $dateOff    = $this->request->getPost('dateoff');
                $checkRequired = $this->request->getPost('checkrequired');

                if(!isset($dateOff) || trim($dateOff) == ''){
                    echo json_encode(array('error' => 'bad date'));
                    exit;
                }

                if(!isset($type) || !$this->checkType($type)){
                    echo json_encode(array('error' => 'bad type'));
                    exit;
                }

                $required = $this->checkDate($dateOff, $result);
                if(isset($checkRequired) && $checkRequired == 1){
                    $required = 0;
                }

                $cat = new ArgumentsCategory();
                $cat_arguments = $cat->getCategoryNotEmpty( $type, $required );
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
            case 2:
                $parent_id  = $this->request->getPost('id');
                $type       = $this->request->getPost('type');
                $dateOff    = $this->request->getPost('dateoff');
                $checkRequired = $this->request->getPost('checkrequired');

//                $parent_id  = ArgumentsCategory::findFirst($id);
//                if($parent_id == false){
//                    echo json_encode(array('error' => 'no cat'));
//                    exit;
//                }
//                $parent_id  = $parent_id->parent_id;

                if(!isset($dateOff) || trim($dateOff) == ''){
                    echo json_encode(array('error' => 'bad date'));
                    exit;
                }

                if(!is_numeric($parent_id)){
                    echo json_encode(array('error' => 'bad data'));
                    exit;
                }
                if(!isset($type) || !$this->checkType($type)){
                    echo json_encode(array('error' => 'bad type'));
                    exit;
                }

                $required = $this->checkDate($dateOff, $result);
                if(isset($checkRequired) && $checkRequired == 1){
                    $required = 0;
                }

                $cat = new ArgumentsCategory();
                $cat_arguments = $cat->getCategoryNotEmpty( $type, $required );

                $temp_name = array();
                foreach($cat_arguments as $cat){
                    if($cat->lvl1_id == $parent_id) {
                        if(!in_array($cat->lvl2, $temp_name)) {
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
            case 3:
                $id         = $this->request->getPost('id');
                $type       = $this->request->getPost('type');
                $dateOff    = $this->request->getPost('dateoff');
                $checkRequired = $this->request->getPost('checkrequired');

                if(!isset($dateOff) || trim($dateOff) == ''){
                    echo json_encode(array('error' => 'bad date'));
                    exit;
                }

                $required = $this->checkDate($dateOff, $result);
                if(isset($checkRequired) && $checkRequired == 1){
                    $required = 0;
                }

                $parent_id  = ArgumentsCategory::findFirst($id);
                if($parent_id == false){
                    echo json_encode(array('error' => 'no cat'));
                    exit;
                }
                $parent_id  = $parent_id->parent_id;

                if(!is_numeric($id)){
                    echo json_encode(array('error' => 'bad data'));
                    exit;
                }

                if(!isset($type) || !$this->checkType($type)){
                    echo json_encode(array('error' => 'bad type'));
                    exit;
                }

                $cat_arguments = new Builder();
                $cat_arguments->getDistinct();
                $cat_arguments->addFrom('Multiple\Frontend\Models\ArgumentsCategory', 'ArgumentsCategory');
                $cat_arguments->rightJoin('Multiple\Frontend\Models\Arguments', "ArgumentsCategory.id = category_id AND type LIKE '%{$type}%'");
                $cat_arguments->where("parent_id = {$id}");
                if($required == 1){
                    $cat_arguments->andWhere("ArgumentsCategory.required = {$required}");
                    $cat_arguments->andWhere("Multiple\Frontend\Models\Arguments.required = {$required}");
                }
                $cat_arguments->groupBy('ArgumentsCategory.id');
                $cat_arguments = $cat_arguments->getQuery()->execute();

                $arr_id = array();


                $arguments = Arguments::query();
                $arguments->where("category_id = {$id}");
                if($required == 1) {
                    $arguments->andWhere("required = {$required}");
                }
                $arguments->andWhere("type LIKE '%{$type}%'");
                $arguments = $arguments->execute();


                foreach($cat_arguments as $cat){
                    $result['cat_arguments'][] = array(
                        'id'        => $cat->id,
                        'name'      => $cat->name,
                        'required' => $cat->required,
                        'parent_id' => $cat->parent_id,
                    );
                    $arr_id[] = $cat->id;
                }



                if(!empty($arr_id) && count($arguments) > 0){
                    $arguments = Arguments::query()
                        ->where("category_id IN ({arr_id:array})")
                        ->orWhere("category_id = {$id}")
                        ->andWhere("required = {$required}")
                        ->andWhere("type LIKE '%{$type}%'")
                        ->bind(array("arr_id" => $arr_id))
                        ->execute();
                }


                $this->getArguments($arguments, $type, $result);
                echo json_encode($result);
            break;
            case 4:
                $id         = $this->request->getPost('id');
                $type       = $this->request->getPost('type');
                $dateOff    = $this->request->getPost('dateoff');
                $checkRequired = $this->request->getPost('checkrequired');

                if(!isset($dateOff) || trim($dateOff) == ''){
                    echo json_encode(array('error' => 'bad date'));
                    exit;
                }
                if(!is_numeric($id)){
                    echo json_encode(array('error' => 'bad data'));
                    exit;
                }
                if(!isset($type) || !$this->checkType($type)){
                    echo json_encode(array('error' => 'bad type'));
                    exit;
                }

                $required = $this->checkDate($dateOff, $result);
                if(isset($checkRequired) && $checkRequired == 1){
                    $required = 0;
                }


                $arguments = Arguments::query();
                $arguments->where("category_id = {$id}");
                if($required == 1) {
                    $arguments->andWhere("required = {$required}");
                }
                $arguments->andWhere("type LIKE '%{$type}%'");
                $arguments = $arguments->execute();

                $this->getArguments($arguments, $type, $result);
                echo json_encode($result);
            break;
            case 6:
                $search     = $this->request->getPost('search');
                $search     = (isset($search)) ? trim($search) : '';
                $type       = $this->request->getPost('type');
                $dateOff   = $this->request->getPost('dateoff');
                $checkRequired = $this->request->getPost('checkrequired');

                if(!isset($dateOff) || trim($dateOff) == ''){
                    echo json_encode(array('error' => 'bad date'));
                    exit;
                }
                if(empty($search)){
                    echo json_encode($result);
                    exit;
                }
                if(!isset($type) || !$this->checkType($type)){
                    echo json_encode(array('error' => 'bad type'));
                    exit;
                }

                $required = $this->checkDate($dateOff, $result);
                if(isset($checkRequired) && $checkRequired == 1){
                    $required = 0;
                }

                $arguments = Arguments::query();
                $arguments->where('name LIKE :name:', array('name' => '%' . $search . '%'));
                if($required == 1) {
                    $arguments->andWhere("required = {$required}");
                }
                $arguments->andWhere("type LIKE '%{$type}%'");
                $arguments = $arguments->execute();

                $this->getArguments($arguments, $type, $result);
                echo json_encode($result);
            break;
        }
        exit;
    }

    private function translit( $str ) {
        $rus = array('А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ё', 'Ж', 'З', 'И', 'Й', 'К', 'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ч', 'Ш', 'Щ', 'Ъ', 'Ы', 'Ь', 'Э', 'Ю', 'Я', 'а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я');
        $lat = array('A', 'B', 'V', 'G', 'D', 'E', 'E', 'Gh', 'Z', 'I', 'Y', 'K', 'L', 'M', 'N', 'O', 'P', 'R', 'S', 'T', 'U', 'F', 'H', 'C', 'Ch', 'Sh', 'Sch', 'Y', 'Y', 'Y', 'E', 'Yu', 'Ya', 'a', 'b', 'v', 'g', 'd', 'e', 'e', 'gh', 'z', 'i', 'y', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f', 'h', 'c', 'ch', 'sh', 'sch', 'y', 'y', 'y', 'e', 'yu', 'ya');
        return str_replace($rus, $lat, $str);
    }


    private function checkType( $type ){
        $checkType = false;
        if($type == 'electr_auction'){
            $checkType = true;
        } else if( $type == 'concurs'){
            $checkType = true;
        } else if( $type == 'kotirovok'){
            $checkType = true;
        } else if( $type == 'offer'){
            $checkType = true;
        } else {
            echo json_encode(array('status' => 'bad type'));
            exit;
        }
        return $checkType;
    }

    private function getArguments( $arguments, $type, &$result ){
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

    private function checkDate($dateOff, &$result){
        $dateOff = strtotime($dateOff);
        $nowTime = strtotime("now");

        if($nowTime > $dateOff){
            $result['date'] = 1;
            return 1;
        }
        return 0;
    }
}