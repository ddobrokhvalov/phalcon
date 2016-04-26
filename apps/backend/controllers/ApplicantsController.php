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
        $this->setMenu();
    }

    public function editAction($id)
    {
        $applicant = Applicant::findFirstById($id);
        if (!$applicant) {
            $this->flash->error("Applicant was not found");
            return $this->forward("user/index");
        }
        $this->view->form = new ApplicantForm($applicant, array('edit' => true));
        $this->view->applicant = $applicant;
        $this->setMenu();
    }

    public function editapplicantAction($id)
    {
        $applicant = Applicant::findFirstById($id);
        if (!$applicant) {
            $this->flash->error("Applicant was not found");
            return $this->forward("user/index");
        }
        $this->view->form = new ApplicantForm($applicant, array('edit' => true));
        $this->setMenu();
    }

    public function saveAction()
    {
        if (!$this->request->isPost()) {
            return $this->forward("user/index");
        }
        $id = $this->request->getPost("id", "int");
        $applicant = Applicant::findFirstById($id);
        if (!$applicant) {
            //$this->flash->error("Product does not exist");
            return $this->forward("user/index");
        }
        $form = new ApplicantForm(null, array('edit' => true));
        $this->view->form = $form;
        $data = $this->request->getPost();
        if (!$form->isValid($data, $applicant)) {
            foreach ($form->getMessages() as $message) {
                // $this->flash->error($message);
                var_dump($message);
                exit;
            }
            return $this->forward('user/editapplicant/' . $id);
        }
        if ($applicant->save() == false) {
            foreach ($applicant->getMessages() as $message) {
                var_dump($message);
                exit;
                // $this->flash->error($message);
            }
            return $this->forward('user/editapplicant/' . $id);
        }
        $form->clear();
        // $this->flash->success("Admins was updated successfully");
        return $this->forward("user/index");

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