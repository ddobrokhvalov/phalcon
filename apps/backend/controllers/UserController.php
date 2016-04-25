<?php
namespace Multiple\Backend\Controllers;

use Phalcon\Mvc\Controller;
use Phalcon\Mvc\Model\Criteria;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Multiple\Backend\Models\User;
use Multiple\Backend\Models\Applicant;
use Multiple\Backend\Models\Complaint;
use Multiple\Library\PaginatorBuilder;
use Multiple\Backend\Validator\UserValidator;


class UserController extends ControllerBase
{

    public function indexAction()
    {

        $this->persistent->searchParams = null;

        $numberPage = isset($_GET['page']) ? $_GET['page'] : 1;
        $users = User::find();
        $paginator = new Paginator(array(
            "data" => $users,
            "limit" => 20,
            "page" => $numberPage
        ));
        $pages = $paginator->getPaginate();
        $this->view->page = $pages;
        //todo: цветовую дифференциацию, галочки
        $this->view->paginator_builder = PaginatorBuilder::buildPaginationArray($numberPage, $pages->total_pages);
        $this->setMenu();
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
        $this->setMenu();

    }

    public function saveAction()
    {
        if (!$this->request->isPost())
            return $this->forward("user/index");

        $id = $this->request->getPost("id", "int");
        $user = User::findFirstById($id);
        if (!$user)
            return $this->forward("user/index");

        $post = $this->request->getPost();
        $data = [
            'lastname'=>$post['lastname'],
            'firstname'=>$post['firstname'],
            'patronymic'=>$post['patronymic'],
            'phone'=> $post['phone'],
            'email'=> $post['email'],
            'admin_comment'=> $post['admin_comment']
        ];
        if (strlen($post['password']) > 0)
            $data['password'] = sha1($post['password']);

        $validation = new UserValidator();
        $messages = $validation->validate($data);
        if (count($messages)) {
            foreach ($messages as $message)
                $this->flashSession->error($message);
        } elseif ($user->save($data) == false) {
            foreach ($user->getMessages() as $message)
                $this->flashSession->error($message);
        } else
            $this->flashSession->success("Your information was stored correctly!");
        
        return $this->dispatcher->forward(array(
            'module' => 'backend',
            'controller' => 'user',
            'action' => 'edit',
            'params' => ['id' => $id]
        ));

    }

    public function addAction()
    {
        $this->setMenu();
    }

    public function createAction()
    {
        die('');
        if (!$this->request->isPost())
            return $this->forward("user/index");

        $user = new User();

        $data = $this->request->getPost();
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

        var_dump($user);
        die();
        return $this->forward("user/index");

    }

    public function editAction($id)
    {
        $user = User::findFirstById($id);
        if (!$user) {
            $this->flash->error("User was not found");
            return $this->forward("user/index");
        }
        $appl = new Applicant();
        $this->view->applicants = $appl->findByUserId($id);
        $complaints = new Complaint();
        $applicants = new Applicant();
        $this->view->complaints = $complaints->findUserComplaints($id, false);
        $this->view->applicants = $applicants->findByUserIdWithAdditionalInfo($id);
        $this->view->edituser = $user;
        $this->setMenu();
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

    public function editapplicantAction($id)
    {
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

    public function delapplicantAction($id)
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