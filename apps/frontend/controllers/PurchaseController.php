<?php
namespace Multiple\Frontend\Controllers;

use Phalcon\Mvc\Controller;
use Multiple\Library\Parser;

class PurchaseController extends Controller
{

    public function getAction()
    {
        if (!$this->request->isPost()) {
            echo 'error';
            exit;
        }
        $postData = $this->request->getPost();
        $auction_id = $postData['auction_id'];
        $purchase = new Parser();
        $data = $purchase->parseAuction($auction_id);
        echo json_encode($data);
        exit;
    }
   public function indexAction(){

   }
}
