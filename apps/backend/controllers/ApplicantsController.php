<?php
namespace Multiple\Backend\Controllers;

use Phalcon\Mvc\Controller;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Multiple\Backend\Models\Applicant;
use Multiple\Library\PaginatorBuilder;
class ApplicantsController  extends ControllerBase
{
    public function indexAction(){
        $numberPage = isset($_GET['page']) ? $_GET['page'] : 1;
        $applicant = Applicant::find();
        $paginator = new Paginator(array(
            "data"  => $applicant,
            "limit" => 10,
            "page"  => $numberPage
        ));
        $pages = $paginator->getPaginate();
        $this->view->page = $pages;
        $this->view->paginator_builder = PaginatorBuilder::buildPaginationArray($numberPage, $pages->total_pages);
    }
}