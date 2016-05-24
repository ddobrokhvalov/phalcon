<?php
namespace Multiple\Backend\Controllers;

use Phalcon\Mvc\Controller;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Multiple\Backend\Models\Complaint;
use Multiple\Library\PaginatorBuilder;

class ComplaintsController extends ControllerBase
{

    public function indexAction(){
        $numberPage = isset($_GET['page']) ? $_GET['page'] : 1;
        $complaints = Complaint::find();
        $paginator = new Paginator(array(
            "data"  => $complaints,
            "limit" => 10,
            "page"  => $numberPage
        ));
        $pages = $paginator->getPaginate();
        $this->view->page = $pages;
        $this->view->paginator_builder = PaginatorBuilder::buildPaginationArray($numberPage, $pages->total_pages);
        $this->setMenu();
    }

    public function previewAction($id){
        $this->view->complaint = Complaint::findFirstById($id);
        $this->setMenu();
    }

    public function deleteComplaintAction(){
        $data = "ok";
         $complaint_id = $this->request->getPost("id");
        if (isset($_GET['is_array']) && $complaint_id && count($complaint_id)) {
            $complaints = Complaint::find(
                array(
                    'id IN ({ids:array})',
                    'bind' => array(
                        'ids' => $complaint_id
                    )
                )
            )->delete();
        } elseif($complaint_id){
            $complaint = Complaint::find(
                array(
                    'id = :id:',
                    'bind' => array(
                        'id' => $complaint_id
                    )
                )
            )->delete();
        } else {
            $data = FALSE;
        }
        $this->view->disable();
        echo json_encode($data);
    }

    public function changeComplaintStatusAction(){
        $data = "ok";
        $status = $this->request->getPost("status");
        $complaint_id = $this->request->getPost("id");
        
        if($status && $complaint_id){
            @Complaint::changeStatus($status, array($complaint_id));
        } else {
            $data = FALSE;
        }
        $this->view->disable();
        echo json_encode($data);
    }
}