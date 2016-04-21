<?php
namespace Multiple\Backend\Controllers;
use Phalcon\Mvc\Controller;
use Multiple\Backend\Models\Admin;
use Multiple\Frontend\Models\User;
use Multiple\Frontend\Models\Applicant;
use Multiple\Frontend\Models\Complaint;
use Multiple\Frontend\Models\Arguments;
use Multiple\Frontend\Models\Log;
use Multiple\Frontend\Models\Question;

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
    }
}