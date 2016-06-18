<?php

namespace Multiple\Frontend\Controllers;

use Phalcon\Mvc\Controller;
use Multiple\Frontend\Models\Messages;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Multiple\Library\PaginatorBuilder;

class MessageController extends ControllerBase
{

    public function indexAction() {
        $next_items = $this->request->getPost('next-portions-items');
        if (!isset($next_items)) {
            $next_items = 0;
        }
        $item_per_page = 20 + $next_items;
        $messages = Messages::find(array(
            'to_uid = :to_uid:',
            'bind' => array(
                'to_uid' => $this->user->id,
            ),
        ));

        $numberPage = isset($_GET['page']) ? $_GET['page'] : 1;
        $paginator = new Paginator(array(
            "data"  => $messages,
            "limit" => $item_per_page,
            "page"  => $numberPage
        ));

        $pages = $paginator->getPaginate();
        $this->view->page = $pages;
        $this->view->item_per_page = $item_per_page;
        $this->view->paginator_builder = PaginatorBuilder::buildPaginationArray($numberPage, $pages->total_pages);
        $this->setMenu();
    }

}