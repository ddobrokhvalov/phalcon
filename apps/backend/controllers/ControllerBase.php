<?php
namespace Multiple\Backend\Controllers;
use Phalcon\Mvc\Controller;
use Multiple\Backend\Models\Admin;
use Multiple\Backend\Models\User;
use Multiple\Backend\Models\Applicant;
use Multiple\Backend\Models\Complaint;
use Multiple\Backend\Models\Arguments;
use Multiple\Backend\Models\Log;
use Multiple\Backend\Models\Question;

class ControllerBase extends Controller
{

    protected function initialize()
    {
        $auth = $this->session->get('auth');
        if (!$auth)
            $user_id = false;
        else
            $user_id = $auth['id'];

        if ($user_id) {
            $this->user = Admin::findFirst(
                array(
                    "id = :id:",
                    'bind' => array(
                        'id' => $user_id,
                    )
                )
            );
            $this->view->user = $this->user;
        }
        $this->view->showMenu = false;
    }

    protected function forward($uri)
    {
        $uriParts = explode('/', $uri);
        $params = array_slice($uriParts, 2);
        return $this->dispatcher->forward(
            array(
                'controller' => $uriParts[0],
                'action' => $uriParts[1],
                'params' => $params
            )
        );
    }

    public function setMenu(){
        $this->view->showMenu = true;
        $this->view->menuItemsCount = [
            'User'=>User::count(),
            'Applicant'=>Applicant::count(),
            'Complaint'=>Complaint::count(),
            'Arguments'=>Arguments::count(),
            'Log'=>Log::count(),
            'Question'=>Question::count(),
            'Admin'=>Question::count(),
        ];
    }
}