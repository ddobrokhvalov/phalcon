<?php
namespace Multiple\Backend\Controllers;

use Phalcon\Mvc\Controller;
use Phalcon\Mvc\Model\Criteria;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Multiple\Backend\Models\Admin;
use Multiple\Backend\Form\AdminForm;
use Multiple\Library\PaginatorBuilder;
use Multiple\Backend\Validator\AdminValidator;
use Phalcon\Db\RawValue;

class AdminsController extends ControllerBase
{

    public function indexAction()
    {
        $this->persistent->searchParams = null;
        $this->view->form = new AdminForm;

        $numberPage = isset($_GET['page']) ? $_GET['page'] : 1;
        $users = Admin::find();
        $paginator = new Paginator(array(
            "data" => $users,
            "limit" => 20,
            "page" => $numberPage
        ));
        $pages = $paginator->getPaginate();
        $this->view->page = $pages;
        //todo: цветовую дифференциацию, галочки
        $this->view->paginator_builder = PaginatorBuilder::buildPaginationArray($numberPage, $pages->total_pages);
        $this->persistent->searchParams = null;
        $this->view->form               = new AdminForm;
        $this->setMenu();
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
        $this->setMenu();

    }
    public function saveAction()
    {

        if (!$this->request->isPost())
            return $this->forward("admins/index");

        $id = $this->request->getPost("id", "int");
        $admin = Admin::findFirstById($id);
        if (!$admin)
            return $this->forward("admins/index");
        $post = $this->request->getPost();
        $data = [
            'name'=>$post['name'],
            'surname'=>$post['surname'],
            'patronymic'=>$post['patronymic'],
            'phone'=> $post['phone'],
            'email'=> $post['email'],
            'avatar' => new RawValue('default')
        ];
        if (strlen($post['password']) > 0)
            $data['password'] = sha1($post['password']);

        if ($this->request->hasFiles() == true)
            if($admin->saveAvatar($this->request->getUploadedFiles()))
                $data['avatar'] = $admin->avatar;

        $validation = new AdminValidator();
        $messages = $validation->validate($data);

        if (count($messages)) { //todo: return arrays
            foreach ($messages as $message) {
                echo $message, '<br>';
                exit;
            }
        } elseif ($admin->save($data) == false) {
            foreach ($admin->getMessages() as $message) {
                var_dump($message); exit;
               // $this->flash->error($message);
            }
            return $this->forward('admins/edit/' . $id);
        }
       // $this->flash->success("Admins was updated successfully");
        return $this->forward('admins/edit/' . $id);

    }

    public function addAction()
    {
        $this->view->form = new AdminForm(null, array('add' => true));
        $this->setMenu();
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
            $this->view->admin = $admin;
            $this->view->form = new AdminForm($admin, array('edit' => true));
        }else{
            return $this->forward("admins/index");
        }
        $this->setMenu();
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