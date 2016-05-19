<?php
namespace Multiple\Backend\Controllers;

use Phalcon\Mvc\Controller;
use Phalcon\Mvc\Model\Criteria;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Multiple\Backend\Models\User;
use Multiple\Backend\Models\Messages;
use Multiple\Backend\Models\Applicant;
use Multiple\Backend\Models\Complaint;
use Multiple\Library\PaginatorBuilder;
use Multiple\Backend\Validator\UserValidator;
use Phalcon\Validation\Validator\PresenceOf;

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
        $data['email'] = $post['email'];
        foreach(['lastname', 'firstname', 'patronymic', 'phone', 'admin_comment'] as $key)
               $data[$key] = $post[$key];

        if (strlen($post['password']) > 0)
            $data['password'] = sha1($post['password']);

        $validation = new UserValidator();
        $messages = $validation->validate($data);
        if (count($messages)) {
            foreach ($messages as $message)
                $this->flashSession->error($message);
        } elseif ($user->save($data, array_keys($data)) == false) {
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
        if (!$this->request->isPost())
            return $this->forward("user/index");

        $user = new User();
        $post = $this->request->getPost();
        $data['email'] = $post['email'];
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
            } else
                $this->flashSession->success("Your information was stored correctly!");
        }
        if(count($messages))
            return $this->dispatcher->forward(array(
                'module' => 'backend',
                'controller' => 'user',
                'action' => 'add'
            ));
        else
            return $this->dispatcher->forward(array(
                'module' => 'backend',
                'controller' => 'user',
                'action' => 'index'
            ));
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

    public function deleteUsersAction(){
        $user_ids = $this->request->getPost("ids");
        
        if(count($user_ids)){
            $users = User::find(
                array(
                    'id IN ({ids:array})',
                    'bind' => array(
                        'ids' => $user_ids
                    )
                )
            )->delete();
        }
        $this->view->disable();

        $data = "ok";
        echo json_encode($data);
    }

    public function blockUnblockAction(){
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
        $this->view->disable();

        $data = "ok";
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

        $data = "ok";
        echo json_encode($data);
    }
}