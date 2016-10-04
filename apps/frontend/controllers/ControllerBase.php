<?php
namespace Multiple\Frontend\Controllers;
use Phalcon\Mvc\Controller;
use Multiple\Frontend\Models\User;
use Multiple\Frontend\Models\Applicant;
use Multiple\Frontend\Models\Complaint;
use Multiple\Frontend\Models\ComplaintMovingHistory;
use Multiple\Frontend\Models\Messages;
use Phalcon\Mvc\Router\Route;

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
        $messages = [];
        /*Messages block*/
        $messagesObj = $this->modelsManager->createBuilder()
            ->columns('m.id, m.subject, m.body, m.history_id, m.time, m.stat_comp, m.comp_id, h.complaint_id, h.new_status, c.auction_id')
            ->addFrom('Multiple\Frontend\Models\Messages', 'm')
            ->leftJoin('Multiple\Frontend\Models\ComplaintMovingHistory', 'm.history_id = h.id', 'h')
            ->leftJoin('Multiple\Frontend\Models\Complaint', 'c.id = h.complaint_id', 'c')
            ->andWhere('m.to_uid = :to_user: AND m.is_read = 0 AND m.is_deleted = 0', ['to_user' => $this->user->id])
            ->orderBy('m.time desc')
            ->Limit(4)
            ->getQuery()
            ->execute();
        $compl = new Complaint();
        if($messagesObj){
            foreach($messagesObj as $obj){
                $temp = ['id' => $obj->id,'subject' => $obj->subject, 'body' => $obj->body, 'time' => strtotime($obj->time), 'status_change' => false];
                if(isset($obj->history_id, $obj->complaint_id, $obj->auction_id)){
                    $temp['status_change'] = true;
                    $temp['auction_id'] = $obj->auction_id;
                    $temp['complaint_id'] = $obj->complaint_id;
                    $temp['status'] = $compl->getCurrentStatusRussian($obj->stat_comp);
                    $temp['color'] = $compl->getComplaintColor($obj->new_status);
                } elseif(isset($obj->stat_comp)){
                    $temp['status_change'] = true;
                    $temp['status'] = $compl->getCurrentStatusRussian($obj->stat_comp);
                    $temp['color'] = $compl->getComplaintColor($obj->stat_comp);
                    $temp['complaint_id'] = $obj->comp_id;
                }
                $messages[] = $temp;
            }
        }
        $this->view->messages = $messages;
        $this->view->count_unread = $this->view->count_unread = Messages::count(['to_uid = :to_user: AND is_read = 0 AND is_deleted = 0','bind' => ['to_user' => $this->user->id]]);
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
        $save_applicant = $this->session->get('save_applicant');
        if($save_applicant != null){
            $applicant = $save_applicant;
            $this->session->set('applicant', $save_applicant);
            $this->session->remove("save_applicant");
        }
        if ($applicant) {
            $temp = explode(',', $applicant['applicant_id'] );
            foreach($temp as $key => $val){
                if($temp[$key] == 'All'){
                    unset($temp[$key]);
                }
            }
            $temp = implode(',',$temp);
            $this->view->applicant_session = $temp;
            $this->applicant_id = $temp;
        }else {
            $this->view->applicant_session = 'All';
            $this->applicant_id = 'All';
        }

    }

}