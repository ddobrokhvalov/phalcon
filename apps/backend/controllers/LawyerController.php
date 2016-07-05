<?php
namespace Multiple\Backend\Controllers;

use Phalcon\Mvc\Controller;
use Phalcon\Paginator\Adapter\QueryBuilder as Paginator;
use Multiple\Library\PaginatorBuilder;
use Multiple\Backend\Models\Permission;
use Multiple\Backend\Models\Question as Question;

class LawyerController extends ControllerBase
{

    public function indexAction(){
        $perm = new Permission();
        if (!$perm->actionIsAllowed($this->user->id, 'lawyer', 'index') && $this->user->id != 1) {
           $this->view->pick("access/denied");
           $this->setMenu();
        } else {
            $next_items = $this->request->getPost('next-portions-items');
            if (!isset($next_items)) {
                $next_items = 0;
            }
            $item_per_page = 20 + $next_items;
            $numberPage = isset($_GET['page']) ? $_GET['page'] : 1;
            $show_all_items = $this->request->get('all-portions-items');
            if (isset($show_all_items) && $show_all_items == 'all_items') {
                $item_per_page = 99999;
            }
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
            $this->view->only_new = isset($_POST['only_new']) && $_POST['only_new'] ? array('n') : array('y', 'n');
            $this->view->scroll_to_down = $next_items > 0 ? TRUE : FALSE;
            $this->view->paginator_builder = PaginatorBuilder::buildPaginationArray($numberPage, $pages->total_pages);
            $this->setMenu();
        }
    }
}