<?php
namespace Multiple\Backend\Controllers;

use Phalcon\Mvc\Controller;
use Phalcon\Mvc\Model\Criteria;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Multiple\Backend\Models\Admin;
use Multiple\Backend\Form\AdminForm;

class AdminsController extends ControllerBase
{

    public function indexAction()
    {

        $this->persistent->searchParams = null;
        $this->view->form               = new AdminForm;
    }
    public function searchAction(){
        $numberPage = 1;
        if ($this->request->isPost()) {
            $query = Criteria::fromInput($this->di, "Multiple\Backend\Models\Admin", $this->request->getPost());
            $this->persistent->searchParams = $query->getParams();
        } else {
            $numberPage = $this->request->getQuery("page", "int");//todo: init if null
        }

        $parameters = array();
        if ($this->persistent->searchParams) {
            $parameters = $this->persistent->searchParams;
        }

        $admins = Admin::find($parameters);
        if (count($admins) == 0) {
            $this->flash->notice("The search did not find any admins");
            return $this->forward("admins/index");
        }

        $paginator = new Paginator(array(
            "data"  => $admins,
            "limit" => 10,
            "page"  => $numberPage
        ));

        $this->view->page = $paginator->getPaginate();

    }
    public function saveAction()
    {

        if (!$this->request->isPost()) {
            return $this->forward("admins/index");
        }


        $id = $this->request->getPost("id", "int");

        $admin = Admin::findFirstById($id);
        if (!$admin) {
            //$this->flash->error("Product does not exist");

            return $this->forward("admins/index");
        }

        $form =  new AdminForm(null, array('edit' => true));
        $this->view->form = $form;

        $data = $this->request->getPost();

       if($_FILES['avatar']['tmp_name'])
        $admin->saveAvatar($_FILES['avatar']);

        if (!$form->isValid($data, $admin)) {
            foreach ($form->getMessages() as $message) {
               // $this->flash->error($message);
                var_dump($message); exit;
            }
            return $this->forward('admins/edit/' . $id);
        }
        $admin->email = $data['email'];
        if(strlen($data['emptypassword'])>0)
         $admin->password = sha1($data['emptypassword']);

        if ($admin->save() == false) {
            foreach ($admin->getMessages() as $message) {
                var_dump($message); exit;
               // $this->flash->error($message);
            }
            return $this->forward('admins/edit/' . $id);
        }

        $form->clear();

       // $this->flash->success("Admins was updated successfully");
        return $this->forward("admins/index");

    }

    public function addAction()
    {
        $this->view->form = new AdminForm(null, array('add' => true));
    }
    public function createAction(){
        if (!$this->request->isPost()) {
            return $this->forward("admins/index");
        }
        $form  = new AdminForm;
        $admin = new Admin();

        $data = $this->request->getPost();
        if (!$form->isValid($data, $admin)) {
            foreach ($form->getMessages() as $message) {

                var_dump($message); exit;
               // $this->flash->error($message);
            }
            return $this->forward('admins/add');
        }
        $admin->email = $data['email'];
        $admin->password = sha1($data['password']);
        if ($admin->save() == false) {
            foreach ($admin->getMessages() as $message) {

                var_dump($message); exit;
               // $this->flash->error($message);
            }
            return $this->forward('admins/add');
        }

        $form->clear();

        //$this->flash->success("Product was created successfully");
        return $this->forward("admins/index");

    }
    public function editAction($id){
        if (!$this->request->isPost()) {

            $admin = Admin::findFirstById($id);
            if (!$admin) {
                $this->flash->error("Admin was not found");
                return $this->forward("admins/index");
            }

            $this->view->form = new AdminForm($admin, array('edit' => true));
        }else{
            return $this->forward("admins/index");
        }
    }
    public function delAction($id){
        $admin = Admin::findFirstById($id);
        if (!$admin) {
           // $this->flash->error("admin was not found");
            return $this->forward("admins/index");
        }

        if (!$admin->delete()) {
            foreach ($admin->getMessages() as $message) {
               // $this->flash->error($message);
            }
            return $this->forward("admins/search");
        }

        $this->flash->success("admin was deleted");
        return $this->forward("admins/index");
    }


}