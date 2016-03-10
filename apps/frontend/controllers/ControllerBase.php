<?php
namespace Multiple\Frontend\Controllers;
use Phalcon\Mvc\Controller;
use Multiple\Frontend\Models\User;
use Multiple\Frontend\Models\Applicant;
use Multiple\Frontend\Models\Complaint;

class ControllerBase extends Controller
{
    public $user;
    public $applicant_id;

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
            header( 'Location: http://'.$_SERVER['HTTP_HOST'] );
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
    public function setMenu(){
        $applicant = new Applicant();
        $userApplicants = $applicant->findByUserId($this->user->id);

        $this->view->setTemplateAfter('menu');
        $this->view->applicants = $userApplicants;
        $complaint = new Complaint();
        $result = $complaint->findCountUserComplaints($this->user->id);

        $this->view->complaints_num = $result['complaints_num'];
        $this->view->total = $result['total'];
        $this->view->user = $this->user;

        if(isset($_GET['status']))
            $this->view->menu_status = $_GET['status'];
        else
            $this->view->menu_status = 'all';

        if(isset($_GET['applicant_id']))
            $this->session->set('applicant', array('applicant_id' => $_GET['applicant_id']));

        $applicant = $this->session->get('applicant');

        if ($applicant) {
            $this->view->applicant_session = $applicant['applicant_id'];
            $this->applicant_id = $applicant['applicant_id'];
        }else {
            $this->view->applicant_session = 'All';
            $this->applicant_id = 'All';
        }

    }

}