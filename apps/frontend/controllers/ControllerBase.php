<?php
namespace Multiple\Frontend\Controllers;
use Phalcon\Mvc\Controller;
use Multiple\Frontend\Models\User;
use Multiple\Frontend\Models\Applicant;
use Multiple\Frontend\Models\Complaint;
use Multiple\Frontend\Models\ComplaintMovingHistory;
use Multiple\Frontend\Models\Messages;

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
        if (!$this->user) {
             $this->flashSession->error('Вы не залогинены в системе');
             return $this->response->redirect('/');
        }
        $applicant = new Applicant();
        $userApplicants = $applicant->findByUserId($this->user->id);

        /*Messages block*/
        $messages = Messages::find(array(
            'to_uid = :to_user: AND is_read = :is_read: AND is_deleted = 0',
            "order" => "time DESC",
            'bind' => array(
                'to_user' => $this->user->id,
                'is_read' => 0,
            ),
        ));

        /*Complaint moving history*/
        $compl = new Complaint();
        $statuses = $compl->getComplaintMovingStatus($this->user->id);
        if (count($statuses)) {
            $move_statuses = ComplaintMovingHistory::find(
                array(
                    'complaint_id IN ({ids:array}) AND is_read = 0',
                    'bind' => array(
                        'ids' => array_keys($statuses)
                    )
                )
            )->toArray();
            if (count($move_statuses)) {
                foreach ($move_statuses as &$m_status) {
                    $m_status['auction_id'] = $statuses["{$m_status['complaint_id']}"];
                    $m_status['status'] = $compl->getCurrentStatusRussian($m_status['new_status']);
                    $m_status['date'] = date('d F Y', strtotime($m_status['date']));
                    $m_status['color'] = $compl->getComplaintColor($m_status['new_status']);
                }
            }
        }
        $this->view->messages = $messages->count() ? $messages : array();
        $this->view->move_statuses = isset($move_statuses) && is_array($move_statuses) ? $move_statuses : array();
        //$this->view->count_unread = isset($move_statuses) && is_array($move_statuses) ? count($move_statuses) : 0;
        $this->view->count_unread = $messages->count();
        //$messages->count() ? $this->view->count_unread += $messages->count() : '';


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