<?php
namespace Multiple\Backend\Controllers;

use Phalcon\Mvc\Controller;
use Phalcon\Paginator\Adapter\QueryBuilder as Paginator;
use Multiple\Library\PaginatorBuilder;
use Multiple\Backend\Models\Question as Question;

class LawyerController extends ControllerBase
{

    public function indexAction(){

        /*$question = Question::find(array(
            "complaint_id = :complaint_id:",
            'bind' => array(
                'complaint_id' => $this->id,
            ),
            "order" => "id DESC",
        ));
        var_dump($question);
        die();*/
        $numberPage = isset($_GET['page']) ? $_GET['page'] : 1;
        $questions = $this->modelsManager->createBuilder()
            ->from('Multiple\Backend\Models\Complaint')
            ->join('Multiple\Backend\Models\Question', 'Multiple\Backend\Models\Complaint.id=Multiple\Backend\Models\Question.complaint_id')
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