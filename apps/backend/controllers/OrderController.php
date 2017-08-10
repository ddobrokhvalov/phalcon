<?php
namespace Multiple\Backend\Controllers;

use Phalcon\Mvc\Controller;
use Phalcon\Mvc\Model\Criteria;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Multiple\Backend\Models\User;
use Multiple\Backend\Models\Messages;
use Multiple\Backend\Models\Applicant;
use Multiple\Backend\Models\Complaint;
use Multiple\Backend\Models\Permission;
use Multiple\Backend\Models\Tarif;
use Multiple\Backend\Models\TarifOrder;
use Multiple\Library\PaginatorBuilder;
use Multiple\Backend\Validator\TarifValidator;
use Phalcon\Validation\Validator\PresenceOf;
use Multiple\Library\Log;

class OrderController extends ControllerBase
{
	public function indexAction()
    {
		$perm = new Permission();
        if (!$perm->actionIsAllowed($this->user->id, 'tarif_order', 'index')) {
           $this->view->pick("access/denied");
           $this->setMenu();
        } else {
		
			if($this->request->getPost('user_id') && $this->request->getPost('order_id')){
				$user_id = intval($this->request->getPost('user_id'));
				$order_id = intval($this->request->getPost('order_id'));
				
				$user = User::findFirstById($user_id);
				$order = TarifOrder::findFirstById($order_id);
				
				if($user && $order){
					if($this->request->getPost('order_payment')){
						$user->tarif_active = 1;
						$order->invoce_payment = 1;
						$message = $this->mailer->createMessage()
								->to($user->email)
								->bcc("ddobrokhvalov@gmail.com")
								->subject("Оплата счета получена.")
								->content("Оплата счета получена. Доступ к полному функционалу сервиса открыт.");
						$message->send();
					}else{
						$user->tarif_active = 0;
						$order->invoce_payment = 0;
					}
					/*print_r("<pre>");
					print_r($user->toArray());
					print_r("</pre>");*/
					
					$user->saveActive();
					$order->save();
					echo json_encode(array("status"=>"ok"));
				}else{
					echo json_encode(array("status"=>"error", "message"=>"Пользователь или счет не найден"));
				}
				exit;
			}
			
			$next_items = $this->request->getPost('next-portions-items');
            if (!isset($next_items)) {
                $next_items = 0;
            }
            $this->persistent->searchParams = null;
            $item_per_page = 20 + $next_items;
            $numberPage = isset($_GET['page']) ? $_GET['page'] : 1;
            $show_all_items = $this->request->get('all-portions-items');
            if (isset($show_all_items) && $show_all_items == 'all_items') {
                $item_per_page = 99999;
            }
			
			$tarif_orders = TarifOrder::find(array(
                "order" => "id desc"
            ));
            $paginator = new Paginator(array(
                "data" => $tarif_orders,
                "limit" => $item_per_page,
                "page" => $numberPage
            ));
			$this->view->item_per_page = $item_per_page;
            $pages = $paginator->getPaginate();
			$this->view->page = $pages;
		
			$this->setMenu();
		}
		$this->setMenu();
	}
	
	public function viewAction($id){
		$perm = new Permission();
		if (!$perm->actionIsAllowed($this->user->id, 'tarif_order', 'index')) {
           $this->view->pick("access/denied");
           $this->setMenu();
        } else {
			$order_id = $id;
			$tarif_order = TarifOrder::findFirstById($order_id);
			$error = false;
			if($tarif_order){
				$tarif_order = $tarif_order->toArray();
				if($tarif_order["user_id"] == $this->user->id){
					$error = false;
				}else{
					//$tarif_order = false;
					//$error = "not_user_order";
				}
			}else{
				$error = "not_order_exists";
			}
			
			include($_SERVER["DOCUMENT_ROOT"]."/include/payment_admin_download.php");
			exit;
		}
	}   
	
}