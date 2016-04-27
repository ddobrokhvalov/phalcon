<?php
namespace Multiple\Backend\Controllers;

use Phalcon\Mvc\Controller;
use Phalcon\Mvc\Model\Criteria;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Multiple\Backend\Models\Admin;
use Multiple\Backend\Models\Permission;
use Multiple\Backend\Form\AdminForm;
use Multiple\Library\PaginatorBuilder;
use Multiple\Backend\Validator\AdminValidator;
use Phalcon\Validation\Validator\PresenceOf;

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
        if (strlen($post['password']) > 0)
            $data['password'] = sha1($post['password']);
        if ($this->request->hasFiles() == true)
            $admin->saveAvatar($this->request->getUploadedFiles());
        $data = [
            'email'=> $post['email'],
            'avatar' => $admin->avatar
        ];
        foreach(['name', 'surname', 'patronymic', 'phone'] as $key)
            $data[$key] = $post[$key];
        $validation = new AdminValidator();
        $messages = $validation->validate($data);
        if (count($messages)) {
            foreach ($messages as $message)
                $this->flashSession->error($message);
        } elseif ($admin->save($data, array_keys($data)) == false) {
            foreach ($admin->getMessages() as $message)
                $this->flashSession->error($message);
        } else
            $this->flashSession->success("Your information was stored correctly!");
        return $this->dispatcher->forward(array(
            'module' => 'backend',
            'controller' => 'admins',
            'action' => 'edit',
            'params' => ['id' => $id]
        ));

    }

    public function addAction()
    {
        $this->setMenu();
    }
    public function createAction(){
        if (!$this->request->isPost())
            return $this->forward("admins/index");

        $admin = new Admin();
        $post = $this->request->getPost();
        $data['email'] = $post['email'];
        foreach(['name', 'surname', 'patronymic', 'phone'] as $key)
                $data[$key] = $post[$key];
        if ($this->request->hasFiles() == true)
            if($admin->saveAvatar($this->request->getUploadedFiles()))
                $data['avatar'] = $admin->avatar;

        $validation = new AdminValidator();
        $validation->add('password', new PresenceOf((array('message' => 'The password is required'))));
        $messages = $validation->validate($data);
        if (count($messages)) {
            foreach ($messages as $message)
                $this->flashSession->error($message);
        }
        if (!count($messages) ) {
            $data['password'] = sha1($post['password']);
            if($admin->save($data, array_keys($data)) == false)
                foreach ($admin->getMessages() as $message)
                    $this->flashSession->error($message);
        } else
            $this->flashSession->success("Your information was stored correctly!");
        $admin->password = sha1($data['password']);        
        if ($admin->save($data, array_keys($data)) == false)
            foreach ($admin->getMessages() as $message)
                $this->flashSession->error($message);
        if(count($messages))
            return $this->dispatcher->forward(array(
                'module' => 'backend',
                'controller' => 'admins',
                'action' => 'add',
            ));
        else
            return $this->dispatcher->forward(array(
                'module' => 'backend',
                'controller' => 'admins',
                'action' => 'index',
            ));
    }
    public function editAction($id){
        $admin = Admin::findFirstById($id);
        if (!$admin) {
            $this->flash->error("Admin was not found");
            return $this->forward("admins/index");
        }
        $this->view->admin = $admin;
        $permission = new Permission();
        $this->view->permisions = $permission->getAdminPermissionAsKeyArray($admin->id);
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

    public function profilesaveAction(){
        $result = array('success'=>true, 'errors'=>array());
        $messages = [];
        $admin = Admin::findFirstById($this->user->id);
        $post = $this->request->getPost();
        if (strlen($post['password']) > 0 || strlen($post['opassword']) > 0 || strlen($post['rpassword']) > 0) {
            if(!strlen($post['opassword']))
                $messages[] = 'Введите старый пароль';
            elseif(sha1($post['opassword'])==$admin->password)
                $messages[] = 'Cтарый пароль не верен';
            elseif(!strlen($post['password']))
                $messages[] = 'Введите новый пароль';
            elseif($post['password']!==$post['rpassword'])
                $messages[] = 'Пароли не совпадают';
            else
                $data['password'] = sha1($post['password']);
        }
        if (!count($messages)) {
            if ($this->request->hasFiles() == true)
                $admin->saveAvatar($this->request->getUploadedFiles());
            $data = [
                'email' => $post['email'],
                'avatar' => $admin->avatar,
                'name' =>$post['name'],
                'surname' =>$post['surname'],
                'patronymic' =>$post['patronymic'],
            ];
            $validation = new AdminValidator();
            $messages = $validation->validate($data);
            if (count($messages))
                $result = array('success' => false, 'errors' => $messages);
            elseif ($admin->save($data, array_keys($data)) == false)
                $result = array('success' => false, 'errors' => $admin->getMessages());
        } else $result = array('success' => false, 'errors' => $messages);
        echo json_encode($result);
        exit;
    }

}