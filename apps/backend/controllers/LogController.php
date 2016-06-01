<?php

namespace Multiple\Backend\Controllers;

use Phalcon\Mvc\Controller;
use Multiple\Backend\Models\Log;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Multiple\Library\PaginatorBuilder;

class LogController extends ControllerBase
{

    public function indexAction()
    {
        $next_items = $this->request->getPost('next-portions-items');
        if (!isset($next_items)) {
            $next_items = 0;
        }
        $item_per_page = 20 + $next_items;
        $log = new Log();

        $typeList = $log->getTypeList();

        $data = $this->request->getPost();
        $logs = $log->filterLog($data);
        $numberPage = isset($_GET['page']) ? $_GET['page'] : 1;
        $paginator = new Paginator(array(
            "data"  => $logs,
            "limit" => $item_per_page,
            "page"  => $numberPage
        ));

        if(!$data)
            $data = array('au'=>'all','type'=>'all');
        if(!isset($data['additionally']))
            $data['additionally'] = '';
        if(!isset($data['textsearch']))
            $data['textsearch'] = '';
        if(!isset($data['datefrom']))
            $data['datefrom'] = '';
        if(!isset($data['dateto']))
            $data['dateto'] = '';


        $this->view->postdata = $data;
        $this->view->typelist = $typeList;
        $pages = $paginator->getPaginate();
        $this->view->page = $pages;
        $this->view->item_per_page = $item_per_page;
        $this->view->paginator_builder = PaginatorBuilder::buildPaginationArray($numberPage, $pages->total_pages);
        $this->setMenu();
    }

}