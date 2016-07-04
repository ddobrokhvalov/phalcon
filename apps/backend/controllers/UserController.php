<?php
namespace Multiple\Backend\Controllers;

use Phalcon\Mvc\Controller;
use Phalcon\Mvc\Model\Criteria;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Multiple\Backend\Models\User;
use Multiple\Backend\Models\Messages;
use Multiple\Backend\Models\Applicant;
use Multiple\Backend\Models\Complaint;
use Multiple\Backend\Models\Permission;
use Multiple\Library\PaginatorBuilder;
use Multiple\Backend\Validator\UserValidator;
use Phalcon\Validation\Validator\PresenceOf;
use Multiple\Library\Log;

class UserController extends ControllerBase
{

    public function indexAction()
    {
        $perm = new Permission();
        if (!$perm->actionIsAllowed($this->user->id, 'user', 'index')) {
           $this->view->pick("access/denied");
           $this->setMenu();
        } else {
            $next_items = $this->request->getPost('next-portions-items');
            if (!isset($next_items)) {
                $next_items = 0;
            }
            $this->persistent->searchParams = null;
            $item_per_page = 20 + $next_items;
            $numberPage = isset($_GET['page']) ? $_GET['page'] : 1;
            $show_all_items = $this->request->get('all-portions-items');
            if (isset($show_all_items) && $show_all_items == 'all_items') {
                $item_per_page = 99999;
            }
            $users = User::find();
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
            $this->setMenu();
        }
    }

    public function afterLoginAction() {
        $this->view->pick('access/afterlogin');
        $this->setMenu();
    }

    public function searchAction()
    {
        $perm = new Permission();
        if (!$perm->actionIsAllowed($this->user->id, 'user', 'search')) {
           $this->view->pick("access/denied");
           $this->setMenu();
        } else {
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
        $data['email'] = $post['email'];
        foreach(['lastname', 'firstname', 'patronymic', 'phone', 'admin_comment'] as $key)
               $data[$key] = $post[$key];

        if (strlen($post['password']) > 0)
            $data['password'] = sha1($post['password']);

        $notifications = $this->request->getPost('notifications');
        $data['notifications'] = isset($notifications) ? 1 : 0;
        $validation = new UserValidator();
        $messages = $validation->validate($data);
        if (count($messages)) {
            foreach ($messages as $message)
                $this->flashSession->error($message);
        } elseif ($user->save($data, array_keys($data)) == false) {
            foreach ($user->getMessages() as $message)
                $this->flashSession->error($message);
        } else {
            Log::addAdminLog("Редактирование пользователя", "Пользователь {$user->firstname} {$user->lastname} изменен", $this->user);
            $this->flashSession->success("Изменения сохранены");
        }
        
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
        if (!$this->request->isPost())
            return $this->forward("user/index");

        $user = new User();
        $post = $this->request->getPost();
        $data['email'] = $post['email'];
        $data['status'] = 1;
        $exist_user = User::findFirstByEmail($post['email']);
        if ($exist_user) {
            $this->flashSession->error("Пользователь с имейлом {$post['email']} уже существует");
            return $this->dispatcher->forward(array(
                'module' => 'backend',
                'controller' => 'user',
                'action' => 'index'
            ));
        }
        foreach(['lastname', 'firstname', 'patronymic', 'phone', 'admin_comment', 'password'] as $key)
           $data[$key] = $post[$key];
        $validation = new UserValidator();
        $validation->add('password', new PresenceOf((array('message' => 'The password is required'))));
        $messages = $validation->validate($data);
        if (count($messages))
            foreach ($messages as $message)
                $this->flashSession->error($message);

        if (!count($messages)) {
            $data['password'] = sha1($post['password']);
            if ($user->save($data, array_keys($data)) == false) {
                $messages = $user->getMessages();
                foreach ($messages as $message)
                    $this->flashSession->error($message);
            } else {
                Log::addAdminLog("Создание пользователя", "Пользователь {$user->firstname} {$user->lastname} сохранен", $this->user);
                $this->flashSession->success("Пользователь успешно сохранен");
            }
        }
        if(count($messages)) {
            return $this->dispatcher->forward(array(
                'module' => 'backend',
                'controller' => 'user',
                'action' => 'add'
            ));
        } else {
            return $this->dispatcher->forward(array(
                'module' => 'backend',
                'controller' => 'user',
                'action' => 'index'
            ));
        }
    }

    public function editAction($id)
    {
        $perm = new Permission();
        if (!$perm->actionIsAllowed($this->user->id, 'user', 'edit')) {
           $this->view->pick("access/denied");
           $this->setMenu();
        } else {
            $user = User::findFirstById($id);
            if (!$user) {
                $this->flashSession->error("Пользователь не найден");
                return $this->forward("user/index");
            }
            //$appl = new Applicant();
            //$this->view->applicants = $appl->findByUserId($id);
            $complaints = new Complaint();
            $applicants = new Applicant();
            $this->view->complaints = $complaints->findUserComplaints($id, false);
            $this->view->applicants = $applicants->findByUserIdWithAdditionalInfo($id);
            $this->view->edituser = $user;
            $this->setMenu();
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

    public function deleteUsersAction(){
        $perm = new Permission();
        if (!$perm->actionIsAllowed($this->user->id, 'user', 'edit')) {
           $data = "access_denied";
        } else {
            $user_ids = $this->request->getPost("ids");
            
            if(count($user_ids)){
                $users = User::find(
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
                    Log::addAdminLog("Удаление пользователя", "Пользователь {$us->firstname} {$us->lastname} удален", $this->user);
                }
            }
            
            $this->flashSession->success('Пользователи удалены');
            $data = "ok";
        }
        $this->view->disable();
        echo json_encode($data);
    }

    public function blockUnblockAction(){
        $perm = new Permission();
        if (!$perm->actionIsAllowed($this->user->id, 'user', 'edit')) {
           $data = "access_denied";
        } else {
            $users_ids = $this->request->getPost("ids");
            $block = $this->request->getPost("block");
            
            if(count($users_ids)){
                $users = User::find(
                    array(
                        'id IN ({ids:array})',
                        'bind' => array(
                            'ids' => $users_ids
                        )
                    )
                );
                foreach ($users as $user) {
                    if ($block) {
                        $user->status = 0;
                    } else {
                        $user->status = 1;
                    }
                    $user->update();
                }
            }
            $this->flashSession->success('Изменения сохранены');
            $data = "ok";
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
    
    public function blockUnblockUserApplicantAction() {
        $users_ids = $this->request->getPost("ids");
        $block = $this->request->getPost("block");
        
        if(count($users_ids)){
            $users = Applicant::find(
                array(
                    'id IN ({ids:array})',
                    'bind' => array(
                        'ids' => $users_ids
                    )
                )
            );
            foreach ($users as $user) {
                if ($block) {
                    $user->is_blocked = 0;
                } else {
                    $user->is_blocked = 1;
                }
                $user->update();
            }
        }
        $this->view->disable();
        $this->flashSession->success('Изменения сохранены');
        $data = "ok";
        echo json_encode($data);
    }

    public function deleteUserApplicantsAction() {
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
}