<?php
namespace Multiple\Frontend\Controllers;

use Phalcon\Mvc\Controller;
use Multiple\Frontend\Models\Applicant;
use Multiple\Frontend\Models\ApplicantECP;
use Multiple\Frontend\Models\Files;
use Multiple\Frontend\Form\ApplicantForm;
use Multiple\Library\Log;

class ApplicantController extends ControllerBase
{
    public function indexAction()
    {
        $this->view->setTemplateAfter('menu');
    }

    public function editAction($id)
    {
        $this->setMenu();
        $applicant = Applicant::findFirstById($id);
        if (!$applicant || $applicant->user_id != $this->user->id)
            return $this->forward('complaint/index');
        $applicantFiles = $applicant->getApplicantFiles($applicant->id);
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
        $this->view->aplicant_ecp = ApplicantECP::find(array(
            'conditions' => 'applicant_id = ?1',
            'bind'       => array(
                1 => $applicant->id,
            ),
            'order' => 'id DESC'

        ));
        $this->view->applicant = $applicant;
        $this->view->attached_files = $files_html;
    }

    public function addAction()
    {
        $this->view->for_user_id = 0;
        
        $this->setMenu();
    }

    public function createAction()
    {
        if (!$this->request->isPost()) {
            return $this->forward('applicant/add');
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
                    /*  if (!isset($_POST['full-name']) || !$_POST['full-name']) {
                          $this->flashSession->error('Полное наименование не может быть пустым');
                          return $this->dispatcher->forward(array(
                              'module' => 'frontend',
                              'controller' => 'applicant',
                              'action' => 'add'
                          ));
                      } */
                    $applicant->user_id = $user_id;
                    $applicant->type = 'urlico';
                    $applicant->name_full = false;
                    $applicant->name_short = $_POST['kratkoe-name'];
                    $applicant->inn = $_POST['inn'];
                    $applicant->kpp = $_POST['kpp'];
                    $applicant->fid = '';
                    $applicant->is_blocked = 1;
                    $applicant->address = $_POST['address'];
                    $applicant->post = $_POST['post'];
                    $applicant->position = $_POST['position-fio'];
                    $applicant->fio_applicant = $_POST['fio'];
                    $applicant->fio_contact_person = $_POST['fio-kontakt-face'];
                    $applicant->telefone = $_POST['phone'];
                    $applicant->email = $_POST['email'];
                    break;
                case 'indlico':
                    $applicant->user_id = $user_id;
                    $applicant->type = 'ip';
                    $applicant->name_short = $_POST['kratkoe-name'];
                    $applicant->inn = $_POST['inn'];
                    //$applicant->kpp = '';
                    $applicant->fid = '';
                    $applicant->post = $_POST['post'];
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
                            'module' => 'frontend',
                            'controller' => 'applicant',
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
                    $applicant->address = '';
                    $applicant->position = '';
                    $applicant->is_blocked = 1;
                    $applicant->post = $_POST['post'];
                    $applicant->fio_applicant = $_POST['fio'];
                    $applicant->fio_contact_person = $_POST['fio-kontakt-face'];
                    $applicant->telefone = $_POST['phone'];
                    $applicant->email = $_POST['email'];
                    break;
                default:
                    $this->flashSession->error('Не указан тип заявителя');
                    return $this->dispatcher->forward(array(
                        'module' => 'frontend',
                        'controller' => 'applicant',
                        'action' => 'index'
                    ));
            }
            $applicant->save();

            $applicantECP = new  ApplicantECP();
            $applicantECP->applicant_id = $applicant->id;
            $applicantECP->thumbprint = $_POST['ecp'];
            $applicantECP->activ = 1;
            $applicantECP->name_ecp = $_POST['ecp_text'];
            $applicantECP->save();
            $applicantECP->deactiveOtherECP($applicantECP->id, $applicant->id);
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

            $applicant->save();
            $this->flashSession->success('Заявитель сохранен');
            Log::addAdminLog("Создание заявителя", "Заявитель  {$applicant->name_short} сохранен", $this->user);
        } else {
            $this->flashSession->error('Выбран недопустимый тип файлов или превышен размер в 5 Мб');
        }
        return $this->response->redirect('/complaint/index?applicant_id=' . $applicant->id);
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
        $cert = $this->request->getPost('cert');

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
//                'name_full' => '',
                'name_short' => '',
                'kpp' => '',
                'post' => '',
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

            // Save sertificates
            ApplicantECP::find(array(
                "applicant_id = {$id}"
            ))->delete();
            foreach ($cert as $key){
                $newEcp = new ApplicantECP();
                $newEcp->name_ecp = $key['name'];
                $newEcp->thumbprint = $key['thumbprint'];
                $newEcp->applicant_id = $id;
                $newEcp->activ = 1;
                $newEcp->save();
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
            Log::addAdminLog("Создание заявителя", "Заявитель  {$applicant->name_full} сохранен", $this->user);
            $this->flashSession->success('Заявитель сохранен');
            $form->clear();
        }
//        return $this->dispatcher->forward(array(
//            'module' => 'frontend',
//            'controller' => 'applicant',
//            'action' => 'edit',
//            'params' => ['id' => $id],
//        ));
        $this->response->redirect("applicant/edit/{$id}");
    }

    public function delfileAction($id)
    {
        $applicant = new Applicant();
        $applicantFile = $applicant->checkFileOwner($this->user->id, $id);
        if ($applicantFile) {
            $applicant->deleteFile($applicantFile);
            return $this->forward('applicant/edit/' . $applicantFile['app_id']);
        } else {
            return $this->forward('complaint/index');
        }
    }

    public function deleteAction($id)
    {
        $applicant = Applicant::findFirstById($id);
        if (!$applicant || $applicant->user_id != $this->user->id)
            return $this->forward('complaint/index');


        $appFiles = $applicant->getApplicantFiles($id);
        foreach ($appFiles as $file) {
            $applicant->deleteFile($file);
        }
        $appECP = ApplicantECP::findByApplicantId($id);
        foreach ($appECP as $ecp)
            $ecp->delete();
        $applicant->delete();
        return $this->forward('complaint/index');
    }

    public function deleteFileAction()
    {
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

    public function checkinnAction()
    {
        if (!$this->request->isPost()) {
            echo 'false';
            exit;
        }
        $data = $this->request->getPost();
        if (!isset($data['inn'])) {
            echo 'false';
            exit;
        }

        $app = new Applicant();

        if ($app->checkInn($data['inn']))
            echo 'true';
        else
            echo 'false';

        exit;
    }

    public function ajaxSetApplicantIdAction()
    {
        $response = array();
        $response['applicant_info'] = array();
        if (isset($_POST['applicant_id'])) {
            $this->session->set('applicant', array('applicant_id' => $_POST['applicant_id']));
            if ($_POST['applicant_id'] != 'All') {
                $response['applicant_info'] = Applicant::findFirstById($_POST['applicant_id'])->toArray();
            }
        }
        $this->view->disable();
        header('Content-type: application/json');
        echo json_encode($response);
        die();
    }

    public function getApplicantInfoAction()
    {
        $response = array();
        $response['applicant_info'] = array();
        if (isset($_POST['applicant_id'])) {
            if ($_POST['applicant_id'] != 'All') {
                $response['applicant_info'] = Applicant::findFirstById($_POST['applicant_id'])->toArray();
            }
        }
        $this->view->disable();
        header('Content-type: application/json');
        echo json_encode($response);
        die();
    }
}