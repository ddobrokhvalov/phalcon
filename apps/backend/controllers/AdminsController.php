<?php
namespace Multiple\Backend\Controllers;

use Phalcon\Mvc\Controller;
use Phalcon\Mvc\Model\Criteria;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Multiple\Backend\Models\Admin;
use Multiple\Backend\Models\Permission;
use Multiple\Backend\Models\PermissionAdmin;
use Multiple\Backend\Models\Messages;
use Multiple\Backend\Form\AdminForm;
use Multiple\Library\PaginatorBuilder;
use Multiple\Backend\Validator\AdminValidator;
use Phalcon\Validation\Validator\PresenceOf;
use Multiple\Library\Log;

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
        $show_all_items = $this->request->get('all-portions-items');
        if (isset($show_all_items) && $show_all_items == 'all_items') {
            $item_per_page = 99999;
        }
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
        $this->view->scroll_to_down = $next_items > 0 ? TRUE : FALSE;
        $this->view->form = new AdminForm;
        $this->view->user_id = $this->user->id;
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
    public function saveAction() {
        $delete_admin = $this->request->getPost('delete_admin');
        if (isset($delete_admin) && $delete_admin && $this->user->id == 1) {
            if ($delete_admin != $this->user->id) {
                $adm = Admin::findFirstById($delete_admin);
                $adm_name = $adm->name;
                $adm->delete();
                Log::addAdminLog("Удаление администратора", "Администратор {$adm_name} удален", $this->user);
                $this->flashSession->success("Администратор успешно удален");
                return $this->forward("admins/index");
            } else {
                $this->flashSession->error("Невозможно удалить свой аккаунт");
                return $this->dispatcher->forward(array(
                    'module' => 'backend',
                    'controller' => 'admins',
                    'action' => 'edit',
                    'params' => ['id' => $this->user->id]
                ));
            }
        } else if ($this->user->id == $this->request->getPost("id", "int") || $this->user->id == 1) {
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
            $data['email'] = $post['email'];
            $data['avatar'] = $admin->avatar;
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
            } else {
                Log::addAdminLog("Изменение администратора", "Администратор {$admin->name} изменен", $this->user);
                $this->flashSession->success("Изменения сохранены");
            }
            return $this->dispatcher->forward(array(
                'module' => 'backend',
                'controller' => 'admins',
                'action' => 'edit',
                'params' => ['id' => $id]
            ));
        } else {
            $this->view->pick("access/denied");
            $this->setMenu();
        }
    }

    public function addAction()
    {
        $params = $this->dispatcher->getParams();
        if(!empty($params)) $this->view->params = $params;
        else {
            $this->view->params = array(
                'email'         => '',
                'password'      => '',
                'name'          => '',
                'surname'       => '',
                'phone'         => '',
                'patronymic'    => '',
                'avatar'        => '',
            );
        }
        if ($this->user->id != 1) {
            $this->view->pick("access/denied");
            $this->setMenu();
        } else {
            $this->setMenu();
        }
    }
    public function createAction(){
        if ($this->user->id == 1) {
            if (!$this->request->isPost())
                return $this->forward("admins/index");

            $post = $this->request->getPost();

            $checkEmail = Admin::findFirst("email = '{$post['email']}'");
            if($checkEmail != false) {
                $this->flashSession->error('Пользователь с таким email уже есть!');
                $admin = new Admin();
                if ($this->request->hasFiles() == true)
                    if($admin->saveAvatar($this->request->getUploadedFiles()))
                        $avatar = $admin->avatar;

                return $this->dispatcher->forward(array(
                    'module' => 'backend',
                    'controller' => 'admins',
                    'action' => 'add',
                    'params' => array(
                        'name'          => $post['name'],
                        'surname'       => $post['surname'],
                        'phone'         => $post['phone'],
                        'patronymic'    => $post['patronymic'],
                        'avatar'        => (!isset($avatar)) ? '' : $avatar
                    )
                ));
            }

            $admin = new Admin();
            $data['email'] = $post['email'];
            $data['admin_status'] = 0;
            $data['password'] = $post['password'];
            foreach(['name', 'surname', 'patronymic', 'phone'] as $key)
                    $data[$key] = $post[$key];
            if ($this->request->hasFiles() == true)
                if($admin->saveAvatar($this->request->getUploadedFiles()))
                    $data['avatar'] = $admin->avatar;

            $validation = new AdminValidator();
            $validation->add('password', new PresenceOf((array('message' => 'Пароль не может быть пустым'))));
            $messages = $validation->validate($data);
            if (count($messages)) {
                $this->flashSession->error('Не заполнены обязательные поля');
                return $this->dispatcher->forward(array(
                    'module' => 'backend',
                    'controller' => 'admins',
                    'action' => 'add',
                    'params' => array(
                        'name'          => $post['name'],
                        'surname'       => $post['surname'],
                        'phone'         => $post['phone'],
                        'patronymic'    => $post['patronymic'],
                        'avatar'        => (!isset($avatar)) ? '' : $avatar
                    )
                ));
                /*foreach ($messages as $message)
                    $this->flashSession->error($message);*/
            }
            if (!count($messages) ) {
                $data['password'] = sha1($data['password']);
                $admin->activated = 1;
                if($admin->save($data, array_keys($data)) == false) {
                    $this->flashSession->error('Возникла ошибка при сохранении администратора');
                        return $this->dispatcher->forward(array(
                        'module' => 'backend',
                        'controller' => 'admins',
                        'action' => 'add',
                    ));
                }
                    /*foreach ($admin->getMessages() as $message)
                        $this->flashSession->error($message);*/
            }
            $this->flashSession->success("Администратор добавлен");
            Log::addAdminLog("Создание администратора", "Администратор {$admin->name} создан", $this->user);
            return $this->response->redirect('/admin/admins/edit/' . $admin->id . '?ask_admin_rights=1');
            /*return $this->dispatcher->forward(array(
                'module' => 'backend',
                'controller' => 'admins',
                'action' => 'edit',
                'params' => ['id' => $admin->id]
            ));*/
        } else {
            $this->view->pick("access/denied");
            $this->setMenu();
        }
    }
    public function editAction($id){
        if ($id == $this->user->id || $this->user->id == 1) {
            $admin = Admin::findFirstById($id);
            if (!$admin) {
                $this->flashSession->error("Администратор не найден");
                return $this->forward("admins/index");
            }
            $this->view->admin = $admin;
            $permission = new Permission();
            $this->view->permisions = $permission->getAdminPermissionAsKeyArray($admin->id);
            $this->view->user_id = $this->user->id;
            $this->setMenu();
        } else {
           $this->view->pick("access/denied");
           $this->setMenu();
        }
    }
    
    public function delAction($id){
        $this->flashSession->error("Its wrong function for deleting admin");
        /*$admin = Admin::findFirstById($id);
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
        return $this->forward("admins/index");*/
    }

    public function deleteAdminsAction(){
        if ($this->user->id != 1) {
            $this->view->disable();
            $data = "access_denied";
            echo json_encode($data);
        } else {
            $user_ids = $this->request->getPost("ids");
            if(count($user_ids)){
                $users = Admin::find(
                    array(
                        'id IN ({ids:array})',
                        'bind' => array(
                            'ids' => $user_ids
                        )
                    )
                );
                $users_copy = $users;
                $users->delete();
                foreach ($users_copy as $us) {
                    Log::addAdminLog("Удаление администратора", "Администратор {$us->name} удален", $this->user);
                }
                $this->flashSession->success("Администратор успешно удален");
            }
            $this->view->disable();

            $data = "ok";
            echo json_encode($data);
        }
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
        $count_perm = 0;
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
                if ($permissionAdmin->edit == 1 || $permissionAdmin->read == 1) {
                    ++$count_perm;
                }
                if($permissionAdmin->save()==false) {
                    $result['success'] = false;
                    foreach ($permissionAdmin->getMessages() as $message)
                        $result['errors'][] = $message;
                }
            }
        }
        if ($count_perm) {
            $admin = Admin::findFirstById($id);
            $admin->admin_status = 1;
            $admin->save();
        }
        $this->view->disable();
        echo json_encode($result);
    }
    
    public function blockUnblockAction(){
        if ($this->user->id != 1) {
           $data = "access_denied";
        } else {
            $users_ids = $this->request->getPost("ids");
            $block = $this->request->getPost("block");
            
            if(count($users_ids)){
                $users = Admin::find(
                    array(
                        'id IN ({ids:array})',
                        'bind' => array(
                            'ids' => $users_ids
                        )
                    )
                );
                foreach ($users as $user) {
                    if ($block) {
                        $user->admin_status = 0;
                    } else {
                        $user->admin_status = 1;
                    }
                    $user->update();
                }
                $this->flashSession->success('Изменения сохранены');
                $data = "ok";
            }
        }
        $this->view->disable();
        echo json_encode($data);
    }

    public function sendMessageAction(){
        $from = $this->user->id;
        $toids = $this->request->getPost("toids");
        $subject = $this->request->getPost("subject");
        $body = $this->request->getPost("body");
        
        if(count($toids) && $from){
            foreach ($toids as $to){
                $message = new Messages();
                $message->from_uid = $from;
                $message->to_uid = $to;
                $message->subject = $subject;
                $message->body = $body;
                $message->time = date('Y-m-d H:i:s');
                $message->save();
            }
        }
        $this->view->disable();
        $this->flashSession->success('Сообщение отправлено');
        $data = "ok";
        echo json_encode($data);
    }
}