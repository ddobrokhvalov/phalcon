<?php
namespace Multiple\Backend\Controllers;

use Phalcon\Mvc\Controller;
use Phalcon\Paginator\Adapter\Model as Paginator;
use \Phalcon\Paginator\Adapter\NativeArray as PaginatorArray;
use Multiple\Backend\Models\Applicant;
use Multiple\Backend\Models\Complaint;
use Multiple\Backend\Models\Files;
use Multiple\Library\PaginatorBuilder;
use Multiple\Backend\Form\ApplicantForm;
use Multiple\Library\Log;

class ApplicantsController  extends ControllerBase
{
    public function indexAction(){
        $next_items = $this->request->getPost('next-portions-items');
        if (!isset($next_items)) {
            $next_items = 0;
        }
        $item_per_page = 20 + $next_items;
        $numberPage = isset($_GET['page']) ? $_GET['page'] : 1;
        $applicant = Applicant::find();
        $paginator = new Paginator(array(
            "data"  => $applicant,
            "limit" => $item_per_page,
            "page"  => $numberPage
        ));
        $pages = $paginator->getPaginate();
        $this->view->page = $pages;
        $this->view->item_per_page = $item_per_page;
        $this->view->paginator_builder = PaginatorBuilder::buildPaginationArray($numberPage, $pages->total_pages);
        $this->setMenu();
    }
    
    public function addAction(){
        $user_id = $this->request->get('user');
        if (isset($user_id) && $user_id) {
            $this->view->for_user_id = $user_id;
        } else {
            $this->view->for_user_id = 0;
        }
        $this->setMenu();
    }

    public function infoAction($id){
        $next_items = $this->request->getPost('next-portions-items');
        if (!isset($next_items)) {
            $next_items = 0;
        }
        $item_per_page = 20 + $next_items;
        $applicant = Applicant::findFirstById($id);
        if (!$applicant) {
            $this->flash->error("Applicant was not found");
            return $this->dispatcher->forward(array(
                'module' => 'backend',
                'controller' => 'applicants',
                'action' => 'index'
            ));
        }
        $numberPage = isset($_GET['page']) ? $_GET['page'] : 1;
        $this->view->applicant = $applicant;
        $paginator = new PaginatorArray(array(
            "data"  => Complaint::findApplicantComplaints($applicant->id),
            "limit" => $item_per_page,
            "page"  => $numberPage
        ));
        $pages = $paginator->getPaginate();
        $this->view->page = $pages;
        $this->view->item_per_page = $item_per_page;
        $this->view->paginator_builder = PaginatorBuilder::buildPaginationArray($numberPage, $pages->total_pages);
        $this->setMenu();
    }

    public function editAction($id)
    {
        $applicant = Applicant::findFirstById($id);
        if (!$applicant) {
            $this->flashSession->error("Заявитель не найден");
            return $this->dispatcher->forward(array(
                'module' => 'backend',
                'controller' => 'applicants',
                'action' => 'index'
            ));
        }
        $files_html = [];
        if ($applicant->fid) {
            $file_ids = unserialize($applicant->fid);
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
                    $files_html[] = $file_model->getFilesHtml($file, $id, 'applicant');
                }
            }
        }
        $this->view->applicant = $applicant;
        $this->view->attached_files = $files_html;
        $this->setMenu();
    }

    public function deleteFileAction() {
        $file_id = $this->request->getPost('file_id');
        $applicant_id = $this->request->getPost('applicant_id');
        if ($file_id && $applicant_id) {
            $applicant = Applicant::findFirstById($applicant_id);
            if ($applicant) {
                $file = Files::findFirstById($file_id);
                if ($file) {
                    $file->delete();
                    $applicant_files = unserialize($applicant->fid);
                    if (count($applicant_files)) {
                        unset($applicant_files[array_search($file_id, $applicant_files)]);
                        $applicant->fid = serialize(array_values($applicant_files));
                    } else {
                        $applicant->fid = serialize(array());
                    }
                    $applicant->save();
                    $this->flashSession->success('Файл удален');
                }
            }
        }
        $this->view->disable();
        $data = "ok";
        echo json_encode($data);
    }

    public function createAction(){
        if (!$this->request->isPost()) {
            return $this->dispatcher->forward(array(
                'module' => 'backend',
                'controller' => 'applicants',
                'action' => 'index'
            ));
        }
        $user_id = $this->request->getPost('for_user_id');
        if ($user_id === "0") {
            $user_id = $this->user->id;
        }

        $allow = TRUE;
        // Check all files with needed rules.
        if ($this->request->hasFiles() == true) {
            $files_model = new Files();
            if (!$files_model->checkAllFiles($this->request)) {
                $allow = FALSE;
            }
        }
        if ($allow) {
            $baseLocation = 'files/applicant/';
            $applicant = new Applicant();
            switch ($_POST['type']) {
                case 'urlico';
                    if (!isset($_POST['full-name']) || !$_POST['full-name']) {
                        $this->flashSession->error('Полное наименование не может быть пустым');
                        return $this->dispatcher->forward(array(
                            'module' => 'backend',
                            'controller' => 'applicants',
                            'action' => 'add'
                        ));
                    }
                    $applicant->user_id = $user_id;
                    $applicant->type = 'urlico';
                    $applicant->name_full = $_POST['full-name'];
                    $applicant->name_short = $_POST['kratkoe-name'];
                    $applicant->inn = $_POST['inn'];
                    $applicant->kpp = $_POST['kpp'];
                    $applicant->fid = '';
                    $applicant->is_blocked = 1;
                    $applicant->address = $_POST['address'];
                    $applicant->position = $_POST['position-fio'];
                    $applicant->fio_applicant = $_POST['fio'];
                    $applicant->fio_contact_person = $_POST['fio-kontakt-face'];
                    $applicant->telefone = $_POST['phone'];
                    $applicant->email = $_POST['email'];
                    break;
                case 'indlico':
                    if (!isset($_POST['full-name']) || !$_POST['full-name']) {
                        $this->flashSession->error('Полное наименование не может быть пустым');
                        return $this->dispatcher->forward(array(
                            'module' => 'backend',
                            'controller' => 'applicants',
                            'action' => 'add'
                        ));
                    }
                    $applicant->user_id = $user_id;
                    $applicant->type = 'ip';
                    $applicant->name_full = $_POST['full-name'];
                    $applicant->name_short = '';
                    $applicant->inn = $_POST['inn'];
                    $applicant->kpp = '';
                    $applicant->fid = '';
                    $applicant->address = $_POST['address'];
                    $applicant->position = $_POST['position-fio'];
                    $applicant->is_blocked = 1;
                    $applicant->fio_applicant = $_POST['fio'];
                    $applicant->fio_contact_person = $_POST['fio-kontakt-face'];
                    $applicant->telefone = $_POST['phone'];
                    $applicant->email = $_POST['email'];
                    break;
                case 'fizlico':
                    if (!isset($_POST['fio']) || !$_POST['fio']) {
                        $this->flashSession->error('Не указаны ФИО заявителя');
                        return $this->dispatcher->forward(array(
                            'module' => 'backend',
                            'controller' => 'applicants',
                            'action' => 'index'
                        ));
                    }
                    $applicant->user_id = $user_id;
                    $applicant->type = 'fizlico';
                    $applicant->name_full = '';
                    $applicant->name_short = '';
                    $applicant->inn = '';
                    $applicant->kpp = '';
                    $applicant->fid = '';
                    $applicant->address = $_POST['address'];
                    $applicant->position = '';
                    $applicant->is_blocked = 1;
                    $applicant->fio_applicant = $_POST['fio'];
                    $applicant->fio_contact_person = $_POST['fio-kontakt-face'];
                    $applicant->telefone = $_POST['phone'];
                    $applicant->email = $_POST['email'];
                    break;
                default:
                    $this->flashSession->error('Не указан тип заявителя');
                    return $this->dispatcher->forward(array(
                        'module' => 'backend',
                        'controller' => 'applicants',
                        'action' => 'index'
                    ));
            }
            $applicant->save();
            // Save attached files.
            $saved_files = array();
            if ($this->request->hasFiles() == true) {
                foreach ($this->request->getUploadedFiles() as $file) {
                    if (strlen($file->getName())) {
                        $applicant_file = new Files();
                        $applicant_file->file_path = $file->getName();
                        $applicant_file->file_size = round($file->getSize() / 1024, 2);
                        $applicant_file->file_type = $file->getType();
                        $applicant_file->save();
                        $saved_files[] = $applicant_file->id;
                        //Move the file into the application
                        $file->moveTo($baseLocation . $file->getName());
                    }
                }
            }
            $applicant->fid = serialize($saved_files);
            //$this->flashSession->error($applicant->fid);
            $applicant->save();
            /*if ($applicant->save() == false) {
                foreach ($applicant->getMessages() as $message) {
                    $this->flashSession->error($message);
                }
                return $this->dispatcher->forward(array(
                    'module' => 'backend',
                    'controller' => 'applicants',
                    'action' => 'edit',
                    'params' => ['id' => $id]
                ));
            }*/
            $this->flashSession->success('Заявитель сохранен');
            Log::addAdminLog("Создание заявителя", "Заявитель  {$applicant->name_full} сохранен", $this->user);
        } else {
            $this->flashSession->error('Выбран недопустимый тип файлов или превышен размер в 5 Мб');
        }
        return $this->dispatcher->forward(array(
            'module' => 'backend',
            'controller' => 'applicants',
            'action' => 'add',
        ));
    }
    
    public function saveAction()
    {
        if (!$this->request->isPost()) {
            return $this->dispatcher->forward(array(
                'module' => 'backend',
                'controller' => 'applicants',
                'action' => 'index'
            ));
        }
        $id = $this->request->getPost("id", "int");
        $applicant = Applicant::findFirstById($id);
        if (!$applicant) {
            //$this->flash->error("Product does not exist");
            return $this->dispatcher->forward(array(
                'module' => 'backend',
                'controller' => 'applicants',
                'action' => 'index'
            ));
        }
        $allow = TRUE;
        // Check all files with needed rules.
        if ($this->request->hasFiles() == true) {
            $files_model = new Files();
            if (!$files_model->checkAllFiles($this->request)) {
                $allow = FALSE;
            }
        }
        if ($allow) {
            $baseLocation = 'files/applicant/';
            $form = new ApplicantForm(null, array('edit' => true, 'type' => $this->request->getPost('type')));
            $this->view->form = $form;
            $all_fields = array(
                'name_full' => '',
                'name_short' => '',
                'inn' => '',
                'kpp' => '',
                'address' => '',
                'position' => '',
                'fio_applicant' => '',
                'fio_contact_person' => '',
                'telefone' => '',
                'email' => '',
            );
            $data = array_merge($all_fields, $this->request->getPost());
            if (!$form->isValid($data, $applicant)) {
                foreach ($form->getMessages() as $message) {
                    $this->flashSession->error($message);
                }
                return $this->dispatcher->forward(array(
                    'module' => 'backend',
                    'controller' => 'applicants',
                    'action' => 'edit',
                    'params' => ['id' => $id]
                ));
                return $this->forward('admin/applicants/edit/' . $id);
            }
            foreach ($data as $field => $value) {
                $applicant->$field = $value;
            }
            if ($applicant->save() == false) {
                foreach ($applicant->getMessages() as $message) {
                    $this->flashSession->error($message);
                }
                return $this->dispatcher->forward(array(
                    'module' => 'backend',
                    'controller' => 'applicants',
                    'action' => 'edit',
                    'params' => ['id' => $id]
                ));
            }
            // Save attached files.
            $saved_files = array();
            if ($this->request->hasFiles() == true) {
                foreach ($this->request->getUploadedFiles() as $file) {
                    if (strlen($file->getName())) {
                        $applicant_file = new Files();
                        $applicant_file->file_path = $file->getName();
                        $applicant_file->file_size = round($file->getSize() / 1024, 2);
                        $applicant_file->file_type = $file->getType();
                        $applicant_file->save();
                        $saved_files[] = $applicant_file->id;
                        //Move the file into the application
                        $file->moveTo($baseLocation . $file->getName());
                    }
                }
            }
            $already_attached = unserialize($applicant->fid);
            if (is_array($already_attached)) {
                $applicant->fid = serialize(array_merge($already_attached, $saved_files));
            } else {
                $applicant->fid = serialize(array());
            }
            $applicant->save();
            Log::addAdminLog("Изменения заявителя", "Заявитель  {$applicant->name_full} изменен", $this->user);
            $this->flashSession->success('Заявитель сохранен');
            $form->clear();
        }
        return $this->dispatcher->forward(array(
            'module' => 'backend',
            'controller' => 'applicants',
            'action' => 'edit',
            'params' => ['id' => $id],
        ));

    }

    public function deleteApplicantsAction(){
        $user_ids = $this->request->getPost("ids");
        
        if(count($user_ids)){
            $users = Applicant::find(
                array(
                    'id IN ({ids:array})',
                    'bind' => array(
                        'ids' => $user_ids
                    )
                )
            );
            $users_copy = $users;
            $users->delete();
            foreach ($users_copy as $us) {
                Log::addAdminLog("Удаление заявителя", "Заявитель  {$us->name_full} удален", $this->user);
            }
            $this->flashSession->success("Заявитель успешно удален");
        }
        $this->view->disable();

        $data = "ok";
        echo json_encode($data);
    }

    public function deletetAction($id)
    {
        $applicant = Applicant::findFirstById($id);
        if (!$applicant) {
            $this->flashSession->error("Заявитель не найден");
            $this->view->disable();
            echo json_encode(array('success' => 'redirect'));
            exit();
        }
        $applicant_name = $applicant->name_full;
        if (!$applicant->delete()) {
            foreach ($applicant->getMessages() as $message) {
                $this->flashSession->error($message);
            }
            $this->view->disable();
            echo json_encode(array('success' => 'false'));
            exit();
        }

        $this->flashSession->success("Заявитель был удален");
        Log::addAdminLog("Удаление заявителя", "Заявитель  {$applicant_name} удален", $this->user);
        $this->view->disable();
        echo json_encode(array('success' => 'ok'));
    }

    public function blockUnblockAction(){
        $users_ids = $this->request->getPost("ids");
        $block = $this->request->getPost("block");
        
        if(count($users_ids)){
            $users = Applicant::find(
                array(
                    'id IN ({ids:array})',
                    'bind' => array(
                        'ids' => $users_ids
                    )
                )
            );
            foreach ($users as $user) {
                if ($block) {
                    $user->is_blocked = 0;
                } else {
                    $user->is_blocked = 1;
                }
                $user->update();
            }
        }
        $this->view->disable();
        $this->flashSession->success('Изменения сохранены');
        $data = "ok";
        echo json_encode($data);
    }
}