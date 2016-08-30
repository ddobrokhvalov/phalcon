<?php

namespace Multiple\Backend\Controllers;

use Phalcon\Mvc\Controller;
use Multiple\Backend\Models\Ufas;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Multiple\Library\PaginatorBuilder;
use Multiple\Library\Log;
use Multiple\Backend\Models\Permission;

class UfasController extends ControllerBase {

    public function indexAction() {
        $perm = new Permission();
        if (!$perm->actionIsAllowed($this->user->id, 'template', 'edit')  && $this->user->id != 1) {
            $this->view->pick("access/denied");
            $this->setMenu();
        } else {
            $next_items = $this->request->getPost('next-portions-items');
            if (!isset($next_items)) {
                $next_items = 0;
            }
            $item_per_page = 20 + $next_items;
            $numberPage = isset($_GET['page']) ? $_GET['page'] : 1;
            $show_all_items = $this->request->get('all-portions-items');
            if (isset($show_all_items) && $show_all_items == 'all_items') {
                $item_per_page = 99999;
            }
            $ufas = Ufas::find();
            $paginator = new Paginator(array(
                "data" => $ufas,
                "limit" => $item_per_page,
                "page" => $numberPage
            ));
            $pages = $paginator->getPaginate();
            $this->view->page = $pages;
            $this->view->item_per_page = $item_per_page;
            $this->view->scroll_to_down = $next_items > 0 ? TRUE : FALSE;
            $this->view->paginator_builder = PaginatorBuilder::buildPaginationArray($numberPage, $pages->total_pages);
            $this->setMenu();
        }
    }

    function checkInnAction() {
        $number = $this->request->getPost("inn");
        $ufas = Ufas::findFirstByNumber($number);
        if (!$ufas) {
            $success = 'ok';
        } else {
            $success = 'no';
        }
        $this->view->disable();
        echo json_encode([
            'success' => $success,
        ]);
        exit();
    }

    public function detailAction($id) {
        $perm = new Permission();
        if (!$perm->actionIsAllowed($this->user->id, 'template', 'edit')  && $this->user->id != 1) {
            $this->view->pick("access/denied");
            $this->setMenu();
        } else {
            $ufas = Ufas::findFirstById($id);
            if (!$ufas) {
                $this->flashSession->error("Данные по УФАС не найдены");
                return $this->response->redirect('/admin/ufas/index');
            }
            $this->view->ufas = $ufas;
            $this->setMenu();
        }
    }

    public function addAction() {
        $this->setMenu();
    }

    public function saveAction() {
        $perm = new Permission();
        if (!$perm->actionIsAllowed($this->user->id, 'template', 'edit')  && $this->user->id != 1) {
            $this->view->pick("access/denied");
            $this->setMenu();
        } else {
            if (!$this->request->isPost()) {
                return $this->response->redirect('/admin/ufas/index');
            }
            $id = $this->request->getPost("ufas-id", "int");
            if ($id) {
                $ufas = Ufas::findFirstById($id);
                if (!$ufas) {
                    $this->flashSession->error("Данные по УФАС не найдены");
                    return $this->response->redirect('/admin/ufas/index');
                }
                $data = $this->request->getPost();
                unset($data["ufas-id"]);
                foreach ($data as $field => $value) {
                    $ufas->$field = $value;
                }
                if ($ufas->save() == false) {
                    foreach ($ufas->getMessages() as $message) {
                        $this->flashSession->error($message);
                    }
                } else {
                    Log::addAdminLog("Изменения УФАС", "Данные УФАС сохранены", $this->user);
                    $this->flashSession->success('Данные УФАС сохранены');
                }
                return $this->response->redirect('/admin/ufas/detail/' . $id);
            }
            return $this->response->redirect('/admin/ufas/index');
        }
    }

    public function createAction() {
        $perm = new Permission();
        if (!$perm->actionIsAllowed($this->user->id, 'template', 'edit')  && $this->user->id != 1) {
            $this->view->pick("access/denied");
            $this->setMenu();
        } else {
            if (!$this->request->isPost()) {
                return $this->dispatcher->forward(array(
                    'module' => 'backend',
                    'controller' => 'ufas',
                    'action' => 'index'
                ));
            }
            $ufas = new Ufas();
            $ufas->name = $_POST['name'];
            $ufas->number = $_POST['number'];
            $ufas->address = $_POST['address'];
            $ufas->phone = $_POST['phone'];
            $ufas->email = $_POST['email'];
            $ufas->save();


            if ($ufas->save() == false) {
                foreach ($ufas->getMessages() as $message) {
                    $this->flashSession->error($message);
                }
                return $this->response->redirect('/admin/ufas/add');
            } else {
                Log::addAdminLog("Создание УФАС", "Данные УФАС созданы", $this->user);
                $this->flashSession->success('Данные УФАС созданы');
            }
            return $this->response->redirect('/admin/ufas/detail/' . $ufas->id);
        }
    }

    public function deleteAction() {
        $perm = new Permission();
        if (!$perm->actionIsAllowed($this->user->id, 'template', 'edit')  && $this->user->id != 1) {
            $this->view->pick("access/denied");
            $this->setMenu();
        } else {
            $id = $this->request->getPost("id", "int");
            $ufas = Ufas::findFirstById($id);
            if (!$ufas) {
                $this->flashSession->error("Данные по УФАС не найдены");
                $this->view->disable();
                echo json_encode(array('success' => 'reload'));
                exit();
            }
            if (!$ufas->delete()) {
                foreach ($ufas->getMessages() as $message) {
                    $this->flashSession->error($message);
                }
                $this->view->disable();
                echo json_encode(array('success' => 'reload'));
                exit();
            }
            $this->flashSession->success("Данные по УФАС были удалены");
            Log::addAdminLog("Удаление данные УФАС", "Данные УФАС удалены", $this->user);
            $this->view->disable();
            echo json_encode(array('success' => 'ok'));
        }
    }

}