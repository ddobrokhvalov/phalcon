<?php
namespace Multiple\Backend\Controllers;

use Phalcon\Mvc\Controller;
use Phalcon\Mvc\Model\Criteria;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Multiple\Backend\Models\Admin as Admin;
use Multiple\Form\AdminForm as AdminForm;

class AdminsController extends ControllerBase
{

    public function indexAction()
    {
      /*  $admins = Admin::find(array(
            "order" => "email"
        ));
        var_dump($admins->toArray()); exit; */
       // $this->view->admins = $admins->toArray();
        $this->persistent->searchParams = null;
        $this->view->form               = new AdminForm;
    }
    public function searchAction(){

        $numberPage = 1;
        if ($this->request->isPost()) {
            $query = Criteria::fromInput($this->di, "Multiple\Backend\Models\Admin", $this->request->getPost());
            $this->persistent->searchParams = $query->getParams();
        } else {
            $numberPage = $this->request->getQuery("page", "int");
        }

        $parameters = array();
        if ($this->persistent->searchParams) {
            $parameters = $this->persistent->searchParams;
        }

        $admins = Admin::find($parameters);
        if (count($admins) == 0) {
            $this->flash->notice("The search did not find any products");
            return $this->forward("admin/admins/index");
        }

        $paginator = new Paginator(array(
            "data"  => $admins,
            "limit" => 10,
            "page"  => $numberPage
        ));

        $this->view->page = $paginator->getPaginate();

    }
    public function saveAction()
    {
        if (!$this->request->isPost()) {
            return $this->forward("admins/index");
        }

    }

    public function addAction()
    {
        $this->view->form = new AdminForm(null, array('edit' => true));
    }
    public function createAction(){
        if (!$this->request->isPost()) {
            return $this->forward("admins/index");
        }

    }


}