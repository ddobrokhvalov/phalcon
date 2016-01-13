<?php

namespace Multiple\Backend\Controllers;

use Phalcon\Mvc\Controller;
use Multiple\Backend\Models\Log;
use Phalcon\Paginator\Adapter\Model as Paginator;

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

        $this->view->postdata = $data;
        $this->view->typelist = $typeList;
        $this->view->page = $paginator->getPaginate();
    }

}