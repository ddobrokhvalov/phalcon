<?php
namespace Multiple\Backend\Controllers;
use Phalcon\Mvc\Controller;
use Multiple\Backend\Models\Admin;

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
        else {
            header('Location: http://'.$_SERVER['HTTP_HOST']);
            exit;
        }
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
}