<?php
namespace Multiple\Backend\Controllers;

use Phalcon\Mvc\Controller;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Multiple\Backend\Models\Applicant;
use Multiple\Library\PaginatorBuilder;
use Multiple\Backend\Form\ApplicantForm;
class ApplicantsController  extends ControllerBase
{
    public function indexAction(){
        $numberPage = isset($_GET['page']) ? $_GET['page'] : 1;
        $applicant = Applicant::find();
        $paginator = new Paginator(array(
            "data"  => $applicant,
            "limit" => 10,
            "page"  => $numberPage
        ));
        $pages = $paginator->getPaginate();
        $this->view->page = $pages;
        $this->view->paginator_builder = PaginatorBuilder::buildPaginationArray($numberPage, $pages->total_pages);
        $this->setMenu();
    }

    public function infoAction($id){
        $applicant = Applicant::findFirstById($id);
        if (!$applicant) {
            $this->flash->error("Applicant was not found");
            return $this->dispatcher->forward(array(
                'module' => 'backend',
                'controller' => 'applicants',
                'action' => 'index'
            ));
        }
        $this->view->applicant = $applicant;
        $this->setMenu();
    }

    public function editAction($id)
    {
        $applicant = Applicant::findFirstById($id);
        if (!$applicant) {
            $this->flash->error("Applicant was not found");
            return $this->dispatcher->forward(array(
                'module' => 'backend',
                'controller' => 'applicants',
                'action' => 'index'
            ));
        }
        $this->view->applicant = $applicant;
        $this->setMenu();
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
        $form = new ApplicantForm(null, array('edit' => true));
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
        $form->clear();
        return $this->dispatcher->forward(array(
            'module' => 'backend',
            'controller' => 'applicants',
            'action' => 'index'
        ));

    }

    public function deletetAction($id)
    {
        $applicant = Applicant::findFirstById($id);
        if (!$applicant) {
            // $this->flash->error("admin was not found");
            return $this->forward("user/index");
        }

        if (!$applicant->delete()) {
            foreach ($applicant->getMessages() as $message) {
                // $this->flash->error($message);
            }
            return $this->forward("applicant/search");
        }

        $this->flash->success("applicant was deleted");
        return $this->forward("user/index");
    }

}