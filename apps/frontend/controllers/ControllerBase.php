<?php
namespace Multiple\Frontend\Controllers;
use Phalcon\Mvc\Controller;
use Multiple\Frontend\Models\User;
use Multiple\Frontend\Models\Applicant;
use Multiple\Frontend\Models\Complaint;

class ControllerBase extends Controller
{
    public $user;

    protected function initialize()
    {
       // $this->tag->prependTitle('INVO | ');
       // $this->view->setTemplateAfter('main');
        $auth = $this->session->get('auth');

        if (!$auth) {
            $user_id = false;
        } else {
            $user_id = $auth['id'];
        }
        if ($user_id) {
            $this->user = User::findFirst(
                array(
                    "id = :id:",
                    'bind' => array(
                        'id' => $user_id,
                    )
                )
            );

        }else{
            return $this->dispatcher->forward(
                array(
                    'controller' => 'index',
                    'action' => 'index'
                )
            );
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
    public function setMenu(){
        $applicant = new Applicant();
        $userApplicants = $applicant->findByUserId($this->user->id);
        $this->view->setTemplateAfter('menu');
        $this->view->applicants = $userApplicants;
        $complaint = new Complaint();
        $result = $complaint->findCountUserComplaints($this->user->id);

        $this->view->complaints_num = $result['complaints_num'];
        $this->view->total = $result['total'];

    }

}