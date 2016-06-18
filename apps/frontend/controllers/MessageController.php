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
            'to_uid = :to_uid: AND is_deleted = 0',
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

    public function deleteAction() {
        $messages_ids = $this->request->getPost("ids");
            
        if(count($messages_ids)){
            $messages = Messages::find(
                array(
                    'id IN ({ids:array}) AND :to_uid:',
                    'bind' => array(
                        'ids' => $messages_ids,
                        'to_uid' => $this->user->id,
                    )
                )
            );
            if ($messages) {
                foreach ($messages as $message) {
                    $message->is_deleted = 1;
                    $message->update();
                }
            }
        }
        $this->flashSession->success('Сообщения удалены');
        $data = "ok";
        $this->view->disable();
        echo json_encode($data);
    }
}