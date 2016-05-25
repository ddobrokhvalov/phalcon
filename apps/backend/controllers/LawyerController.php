<?php
namespace Multiple\Backend\Controllers;

use Phalcon\Mvc\Controller;
use Phalcon\Paginator\Adapter\QueryBuilder as Paginator;
use Multiple\Library\PaginatorBuilder;
use Multiple\Backend\Models\Question as Question;

class LawyerController extends ControllerBase
{

    public function indexAction(){
        $next_items = $this->request->getPost('next-portions-items');
        if (!isset($next_items)) {
            $next_items = 0;
        }
        $item_per_page = 5 + $next_items;
        $numberPage = isset($_GET['page']) ? $_GET['page'] : 1;
        $questions = $this->modelsManager->createBuilder()
            ->distinct('Multiple\Backend\Models\Complaint.id')
            ->from('Multiple\Backend\Models\Complaint')
            ->join('Multiple\Backend\Models\Question', 'Multiple\Backend\Models\Complaint.id=Multiple\Backend\Models\Question.complaint_id')
            ->orderby('Multiple\Backend\Models\Complaint.id');
            //->groupby('Multiple\Backend\Models\Complaint.id'); //error on live server
        $paginator = new Paginator(array(
            "builder" => $questions,
            "limit" => $item_per_page,
            "page" => $numberPage
        ));

        $pages = $paginator->getPaginate();
        $this->view->page = $pages;
        $this->view->item_per_page = $item_per_page;
        $this->view->paginator_builder = PaginatorBuilder::buildPaginationArray($numberPage, $pages->total_pages);
        $this->setMenu();
    }
}