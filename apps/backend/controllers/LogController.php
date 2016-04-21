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

        $log = new Log();

        $typeList = $log->getTypeList();

        $data = $this->request->getPost();
        $logs = $log->filterLog($data);
        $numberPage = 1;
        $paginator = new Paginator(array(
            "data"  => $logs,
            "limit" => 10,
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
        $this->view->paginator_builder = PaginatorBuilder::buildPaginationArray($numberPage, $pages->total_pages);
        $this->setMenu();
    }

}