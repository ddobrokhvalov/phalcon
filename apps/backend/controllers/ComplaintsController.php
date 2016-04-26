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
        /*echo "<pre>";
        $complaint = Complaint::findFirstById($id);
        var_dump($complaint->auction_id);
        echo "</pre>";
        die('');*/
        $this->view->complaint = Complaint::findFirstById($id);
        $this->setMenu();
    }
}