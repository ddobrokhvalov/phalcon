<?php
namespace Multiple\Backend\Controllers;

use Phalcon\Mvc\Controller;
use Phalcon\Mvc\Model\Criteria;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Multiple\Backend\Models\Admin;
use Multiple\Backend\Models\Permission;
use Multiple\Backend\Models\PermissionAdmin;
use Multiple\Backend\Form\AdminForm;
use Multiple\Library\PaginatorBuilder;
use Multiple\Backend\Validator\AdminValidator;
use Phalcon\Validation\Validator\PresenceOf;

class AdminsController extends ControllerBase
{

    public function indexAction()
    {
        $next_items = $this->request->getPost('next-portions-items');
        if (!isset($next_items)) {
            $next_items = 0;
        }
        $item_per_page = 20 + $next_items;
        $this->persistent->searchParams = null;
        $this->view->form = new AdminForm;

        $numberPage = isset($_GET['page']) ? $_GET['page'] : 1;
        $users = Admin::find();
        $paginator = new Paginator(array(
            "data" => $users,
            "limit" => $item_per_page,
            "page" => $numberPage
        ));
        $pages = $paginator->getPaginate();
        $this->view->page = $pages;
        $this->view->item_per_page = $item_per_page;
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
        $delete_admin = $this->request->getPost('delete_admin');
        if (isset($delete_admin) && $delete_admin) {
            Admin::findFirstById($delete_admin)->delete();
            $this->flashSession->success("Администратор успешно удален");
            return $this->forward("admins/index");
        }
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
            $this->flashSession->success("Изменения сохранены");
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
            $this->flashSession->success("Изменения сохранены");
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
            $this->flashSession->error("Администратор не найден");
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

    public function deleteAdminsAction(){
        $user_ids = $this->request->getPost("ids");
        
        if(count($user_ids)){
            $users = Admin::find(
                array(
                    'id IN ({ids:array})',
                    'bind' => array(
                        'ids' => $user_ids
                    )
                )
            )->delete();
            $this->flashSession->success("Администратор успешно удален");
        }
        $this->view->disable();

        $data = "ok";
        echo json_encode($data);
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

    public function permissionssaveAction($id){
        $post = $this->request->getPost();
        $result = array('success'=>true);
        $data = [
            'user'=>[
                'edit'=>isset($post['perm']['user']['edit'])?1:0,
                'read'=>isset($post['perm']['user']['read'])?1:0
            ],
            'complaints'=>[
                'edit'=>isset($post['perm']['complaints']['edit'])?1:0,
                'read'=>isset($post['perm']['complaints']['read'])?1:0
            ],
            'lawyer'=>[
                'edit'=>isset($post['perm']['lawyer']['edit'])?1:0,
            ],
            'arguments'=>[
                'edit'=>isset($post['perm']['arguments']['edit'])?1:0,
            ],
            'template'=>[
                'edit'=>isset($post['perm']['template']['edit'])?1:0,
            ]
        ];
        $permissions = Permission::find();
        foreach($permissions as $permission) {
            if (isset($data[$permission->name])) {
                $permissionAdmin = PermissionAdmin::findFirst(
                    array(
                        "permission_id = :permission_id: and admin_id = :admin_id:",
                        'bind' => array(
                            'permission_id' => $permission->id,
                            'admin_id' => $id,
                        )
                    )
                );
                if(!$permissionAdmin){
                    $permissionAdmin = new PermissionAdmin();
                    $permissionAdmin->admin_id = $id;
                    $permissionAdmin->permission_id = $permission->id;
                }
                isset($data[$permission->name]['edit']) ? $permissionAdmin->edit = $data[$permission->name]['edit'] : $permissionAdmin->edit = 0;
                isset($data[$permission->name]['read']) ? $permissionAdmin->read = $data[$permission->name]['read'] : $permissionAdmin->read = 0;
                if($permissionAdmin->save()==false) {
                    $result['success'] = false;
                    foreach ($permissionAdmin->getMessages() as $message)
                        $result['errors'][] = $message;
                }
            }
        }
        $this->view->disable();
        echo json_encode($result);
    }
}