<?php

namespace Multiple\Frontend\Controllers;

use Phalcon\Mvc\Controller;
use Multiple\Frontend\Models\Messages;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Multiple\Library\PaginatorBuilder;
use Multiple\Frontend\Models\Complaint;
use Multiple\Frontend\Models\ComplaintMovingHistory;

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
            "order" => "time DESC",
            'bind' => array(
                'to_uid' => $this->user->id,
            ),
        ));

        $show_all_items = $this->request->get('all-portions-items');
        if (isset($show_all_items) && $show_all_items == 'all_items') {
            $item_per_page = 99999;
        }

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
        $this->view->count_items = count($messages);
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
                    //$message->is_deleted = 1;
                    $message->delete();
                }
            }
        }
        $this->flashSession->success('Сообщения удалены');
        $data = "ok";
        $this->view->disable();
        echo json_encode($data);
    }
}