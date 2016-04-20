<?php
namespace Multiple\Backend\Controllers;

use Phalcon\Mvc\Controller;
use Phalcon\Paginator\Adapter\QueryBuilder as Paginator;
use Multiple\Library\PaginatorBuilder;
use Multiple\Backend\Models\Question as Question;

class LawyerController extends ControllerBase
{

    public function indexAction(){
        $numberPage = isset($_GET['page']) ? $_GET['page'] : 1;
        $questions = $this->modelsManager->createBuilder()
            ->from('Multiple\Backend\Models\Complaint')
            ->join('Multiple\Backend\Models\Question')
            ->orderby('Multiple\Backend\Models\Complaint.id')
            ->groupby('Multiple\Backend\Models\Complaint.id');
        $paginator = new Paginator(array(
            "builder" => $questions,
            "limit" => 5,
            "page" => $numberPage
        ));

        $pages = $paginator->getPaginate();
        $this->view->page = $pages;
        $this->view->paginator_builder = PaginatorBuilder::buildPaginationArray($numberPage, $pages->total_pages);
    }
}