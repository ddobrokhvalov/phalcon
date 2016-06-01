<?php
namespace Multiple\Backend\Controllers;

use Phalcon\Mvc\Controller;
use Phalcon\Paginator\Adapter\Model as Paginator;
use \Phalcon\Paginator\Adapter\NativeArray as PaginatorArray;
use Multiple\Backend\Models\Applicant;
use Multiple\Backend\Models\Complaint;
use Multiple\Library\PaginatorBuilder;
use Multiple\Backend\Form\ApplicantForm;

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
        $this->view->applicant = $applicant;
        $this->setMenu();
    }

    public function createAction(){
        if (!$this->request->isPost()) {
            return $this->dispatcher->forward(array(
                'module' => 'backend',
                'controller' => 'applicants',
                'action' => 'index'
            ));
        }
        
        $applicant = new Applicant();
        switch ($_POST['type']) {
            case 'fizlico':
                if (!isset($_POST['fio']) || !$_POST['fio']) {
                    $this->flashSession->error('Не указаны ФИО заявителя');
                    return $this->dispatcher->forward(array(
                        'module' => 'backend',
                        'controller' => 'applicants',
                        'action' => 'index'
                    ));
                }
                $applicant->user_id = $this->user->id;
                $applicant->type = 'fizlico';
                $applicant->name_full = '';
                $applicant->name_short = '';
                $applicant->inn = '';
                $applicant->kpp = '';
                $applicant->address = '';
                $applicant->position = '';
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
                $applicant->user_id = $this->user->id;
                $applicant->type = 'ip';
                $applicant->name_full = $_POST['full-name'];
                $applicant->name_short = '';
                $applicant->inn = '';
                $applicant->kpp = '';
                $applicant->address = '';
                $applicant->position = '';
                $applicant->fio_applicant = '';
                $applicant->fio_contact_person = '';
                $applicant->telefone = '';
                $applicant->email = '';
                break;
            case 'urlico';
                if (!isset($_POST['full-name']) || !$_POST['full-name']) {
                    $this->flashSession->error('Полное наименование не может быть пустым');
                    return $this->dispatcher->forward(array(
                        'module' => 'backend',
                        'controller' => 'applicants',
                        'action' => 'add'
                    ));
                }
                $applicant->user_id = $this->user->id;
                $applicant->type = 'urlico';
                $applicant->name_full = $_POST['full-name'];
                $applicant->name_short = $_POST['kratkoe-name'];
                $applicant->inn = $_POST['inn'];
                $applicant->kpp = $_POST['kpp'];
                $applicant->address = $_POST['address'];
                $applicant->position = $_POST['position-fio'];
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
        $this->flashSession->success('Заявитель сохранен');
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
        $form = new ApplicantForm(null, array('edit' => true, 'type' => $this->request->getPost('type')));
        $this->view->form = $form;
        $data = $this->request->getPost();
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
            return $this->forward('user/editapplicant/' . $id);
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
        $this->flashSession->success('Заявитель сохранен');
        $form->clear();
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
            )->delete();
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

        if (!$applicant->delete()) {
            foreach ($applicant->getMessages() as $message) {
                $this->flashSession->error($message);
            }
            $this->view->disable();
            echo json_encode(array('success' => 'false'));
            exit();
        }

        $this->flashSession->success("Заявитель был удален");
        $this->view->disable();
        echo json_encode(array('success' => 'ok'));
    }

}