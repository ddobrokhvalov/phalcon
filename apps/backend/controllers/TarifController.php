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
use Multiple\Library\PaginatorBuilder;
use Multiple\Backend\Validator\TarifValidator;
use Phalcon\Validation\Validator\PresenceOf;
use Multiple\Library\Log;

class TarifController extends ControllerBase
{
	public function indexAction()
    {
		$perm = new Permission();
        if (!$perm->actionIsAllowed($this->user->id, 'tarifs', 'index')) {
           $this->view->pick("access/denied");
           $this->setMenu();
        } else {
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
			
			$tarifs = Tarif::find(array(
                "order" => "id asc"
            ));
            $paginator = new Paginator(array(
                "data" => $tarifs,
                "limit" => $item_per_page,
                "page" => $numberPage
            ));
            $pages = $paginator->getPaginate();
			$this->view->page = $pages;
		
			$this->setMenu();
		}
	}
	
	public function editAction($id)
    {
		$perm = new Permission();
        if (!$perm->actionIsAllowed($this->user->id, 'tarifs', 'edit')) {
           $this->view->pick("access/denied");
           $this->setMenu();
        } else {
			$tarif = Tarif::findFirstById($id);
            if (!$tarif) {
                $this->flashSession->error("Тариф не найден");
                return $this->forward("tarif/index");
            }
			$this->view->edittarif = $tarif;
			$this->setMenu();
		}
	}
	
	public function saveAction()
    {
		if (!$this->request->isPost())
            return $this->forward("tarif/index");
		$id = $this->request->getPost("id", "int");
        $tarif = Tarif::findFirstById($id);
        if (!$tarif)
            return $this->forward("tarif/index");

        $post = $this->request->getPost();
		$data['tarif_name'] = $post['tarif_name'];
		$data['tarif_anounce'] = $post['tarif_anounce'];
		$data['tarif_description'] = $post['tarif_description'];
		$data['tarif_type'] = $post['tarif_type'];
		$data['tarif_price'] = $post['tarif_price'];
		$data['tarif_discount'] = $post['tarif_discount'];
		
		$tarif->tarif_name = $data['tarif_name'];
		$tarif->tarif_anounce = $data['tarif_anounce'];
		$tarif->tarif_description = $data['tarif_description'];
		$tarif->tarif_type = $data['tarif_type'];
		$tarif->tarif_price = $data['tarif_price'];
		$tarif->tarif_discount = $data['tarif_discount'];
		
		$validation = new TarifValidator();
        $messages = $validation->validate($data);
		
		if (count($messages)) {
            foreach ($messages as $message)
                $this->flashSession->error($message);
        } elseif ($tarif->save($data, array_keys($data)) == false) {
            foreach ($tarif->getMessages() as $message)
                $this->flashSession->error($message);
        } else {
            $this->flashSession->success("Изменения сохранены");
        }
        
        return $this->dispatcher->forward(array(
            'module' => 'backend',
            'controller' => 'tarif',
            'action' => 'edit',
            'params' => ['id' => $id]
        ));
		
	}
	
	public function addAction()
    {
        $params = $this->dispatcher->getParams();
        if(!empty($params)) $this->view->params = $params;
        else {
            $this->view->params = array(
                'tarif_name'         => '',
                'tarif_anounce'      => '',
                'tarif_description'     => '',
                'tarif_type'         => '',
                'tarif_price'         => '',
                'tarif_discount' => ''
            );
        }
        $this->setMenu();
    }
	
	public function createAction()
    {
        if (!$this->request->isPost())
            return $this->forward("tarif/index");

        $tarif = new Tarif();
        $post = $this->request->getPost();
        $data['email'] = $post['email'];
		
		$data['tarif_name'] = $post['tarif_name'];
		$data['tarif_anounce'] = $post['tarif_anounce'];
		$data['tarif_description'] = $post['tarif_description'];
		$data['tarif_type'] = $post['tarif_type'];
		$data['tarif_price'] = $post['tarif_price'];
		$data['tarif_discount'] = $post['tarif_discount'];
		
		$tarif->tarif_name = $data['tarif_name'];
		$tarif->tarif_anounce = $data['tarif_anounce'];
		$tarif->tarif_description = $data['tarif_description'];
		$tarif->tarif_type = $data['tarif_type'];
		$tarif->tarif_price = $data['tarif_price'];
		$tarif->tarif_discount = $data['tarif_discount'];
		
		$validation = new TarifValidator();
        $messages = $validation->validate($data);
		
		if (count($messages)) {
            foreach ($messages as $message)
                $this->flashSession->error($message);
        } elseif ($tarif->save($data, array_keys($data)) == false) {
            foreach ($tarif->getMessages() as $message)
                $this->flashSession->error($message);
        } else {
            $this->flashSession->success("Изменения сохранены");
        }
		
		if(count($messages)) {
            return $this->dispatcher->forward(array(
                'module' => 'backend',
                'controller' => 'tarif',
                'action' => 'add'
            ));
        } else {
            return $this->dispatcher->forward(array(
                'module' => 'backend',
                'controller' => 'tarif',
                'action' => 'index'
            ));
        }
		
	}
	
	public function delAction($id)
    {
        $tarif = Tarif::findFirstById($id);
        if (!$tarif) {
            // $this->flash->error("admin was not found");
            return $this->forward("tarif/index");
        }
        if (!$tarif->delete()) {
            foreach ($tarif->getMessages() as $message) {
                // $this->flash->error($message);
            }
            return $this->forward("tarif/index");
        }
        $this->flash->success("tarif was deleted");
        return $this->forward("tarif/index");
    }    
	
}