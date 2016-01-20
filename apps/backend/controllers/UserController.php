<?php
namespace Multiple\Backend\Controllers;

use Phalcon\Mvc\Controller;
use Phalcon\Mvc\Model\Criteria;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Multiple\Backend\Models\User;
use Multiple\Backend\Form\UserForm;
use Multiple\Backend\Models\Applicant;
use Multiple\Backend\Form\ApplicantForm;

class UserController extends ControllerBase
{

    public function indexAction()
    {

        $this->persistent->searchParams = null;
        $this->view->form = new UserForm;


        $users = User::find();
        $paginator = new Paginator(array(
            "data" => $users,
            "limit" => 3,
            "page" => isset($_GET['page'])?$_GET['page']:1
        ));

        $this->view->page = $paginator->getPaginate();
    }

    public function searchAction()
    {
        $numberPage = 1;
        if ($this->request->isPost()) {
            $query = Criteria::fromInput($this->di, "Multiple\Backend\Models\User", $this->request->getPost());
            $this->persistent->searchParams = $query->getParams();
        } else {
            $numberPage = $this->request->getQuery("page", "int");
        }

        $parameters = array();
        if ($this->persistent->searchParams) {
            $parameters = $this->persistent->searchParams;
        }

        $users = User::find($parameters);
        if (count($users) == 0) {
            $this->flash->notice("The search did not find any user");
            return $this->forward("user/index");
        }

        $paginator = new Paginator(array(
            "data" => $users,
            "limit" => 10,
            "page" => $numberPage
        ));

        $this->view->page = $paginator->getPaginate();

    }

    public function saveAction()
    {
        if (!$this->request->isPost()) {
            return $this->forward("user/index");
        }


        $id = $this->request->getPost("id", "int");

        $user = User::findFirstById($id);
        if (!$user) {
            //$this->flash->error("Product does not exist");

            return $this->forward("user/index");
        }

        $form = new UserForm(null, array('edit' => true));
        $this->view->form = $form;

        $data = $this->request->getPost();

        if (!$form->isValid($data, $user)) {
            foreach ($form->getMessages() as $message) {
                // $this->flash->error($message);
                var_dump($message);
                exit;
            }
            return $this->forward('user/edit/' . $id);
        }
        $user->email = $data['email'];
        if (strlen($data['emptypassword']) > 0)
            $user->password = sha1($data['emptypassword']);

        if ($user->save() == false) {
            foreach ($user->getMessages() as $message) {
                var_dump($message);
                exit;
                // $this->flash->error($message);
            }
            return $this->forward('user/edit/' . $id);
        }

        $form->clear();

        // $this->flash->success("Admins was updated successfully");
        return $this->forward("user/index");

    }

    public function addAction()
    {
        $this->view->form = new UserForm(null, array('add' => true));
    }

    public function createAction()
    {
        if (!$this->request->isPost()) {
            return $this->forward("user/index");
        }
        $form = new UserForm;
        $user = new User();

        $data = $this->request->getPost();
        if (!$form->isValid($data, $user)) {
            foreach ($form->getMessages() as $message) {

                var_dump($message);
                exit;
                // $this->flash->error($message);
            }
            return $this->forward('user/add');
        }
        $user->email = $data['email'];
        $user->password = sha1($data['password']);
        $user->date_registration = date("Y-m-d H:i:s");
        if ($user->save() == false) {
            foreach ($user->getMessages() as $message) {

                var_dump($message);
                exit;
                // $this->flash->error($message);
            }
            return $this->forward('user/add');
        }

        $form->clear();

        //$this->flash->success("Product was created successfully");
        return $this->forward("user/index");

    }

    public function editAction($id)
    {
        if (!$this->request->isPost()) {

            $user = User::findFirstById($id);
            if (!$user) {
                $this->flash->error("User was not found");
                return $this->forward("user/index");
            }

            $appl = new Applicant();

            $this->view->applicants = $appl->findByUserId($id);
            $this->view->form = new UserForm($user, array('edit' => true));


        } else {
            return $this->forward("user/index");
        }
    }

    public function delAction($id)
    {
        $user = User::findFirstById($id);
        if (!$user) {
            // $this->flash->error("admin was not found");
            return $this->forward("user/index");
        }

        if (!$user->delete()) {
            foreach ($user->getMessages() as $message) {
                // $this->flash->error($message);
            }
            return $this->forward("user/search");
        }

        $this->flash->success("user was deleted");
        return $this->forward("user/index");
    }
    public function editapplicantAction($id){

            $applicant = Applicant::findFirstById($id);
            if (!$applicant) {
                $this->flash->error("Applicant was not found");
                return $this->forward("user/index");
            }

               $this->view->form = new ApplicantForm($applicant, array('edit' => true));


    }
    public function saveapplicantAction()
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


}