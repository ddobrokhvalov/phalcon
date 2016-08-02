<?php
namespace Multiple\Backend\Controllers;

use Phalcon\Mvc\Controller;
use Phalcon\Mvc\Model;
use Multiple\Backend\Models\ArgumentsCategory;
use Multiple\Library\PaginatorBuilder;
use Multiple\Backend\Models\Permission;
use Multiple\Backend\Models\Arguments;

class ArgumentsController  extends ControllerBase
{

    public function indexAction(){
        /*$perm = new Permission();
        if (!$perm->actionIsAllowed($this->user->id, 'arguments', 'index')) {
           $this->view->pick("access/denied");
           $this->setMenu();
        } else {*/
            $Arguments = $this->modelsManager->createBuilder()
                ->columns(
                    'Multiple\Backend\Models\Arguments.id,
                    Multiple\Backend\Models\Arguments.argument_status,
                    Multiple\Backend\Models\Arguments.date,
                    Multiple\Backend\Models\Arguments.name,
                    Multiple\Backend\Models\ArgumentsCategory.name as catname'
                )
                ->from('Multiple\Backend\Models\Arguments')
                ->join('Multiple\Backend\Models\ArgumentsCategory', 'Multiple\Backend\Models\ArgumentsCategory.id = Multiple\Backend\Models\Arguments.category_id')
                ->getQuery()
                ->execute();
            $this->view->Arguments = $Arguments;
            $this->view->ArgumentsCategory = ArgumentsCategory::find(
                array(
                    "parent_id = 0",
                )
            );
            $this->setMenu();
        //}
    }

    public function addAction(){
        $perm = new Permission();
        if (!$perm->actionIsAllowed($this->user->id, 'arguments', 'edit')) {
           $this->view->pick("access/denied");
           $this->setMenu();
        } else {
            $data = $this->request->getPost("arguments");
            $errors = FALSE;
            if ($data) {
                $argument = new Arguments();
                foreach ($data as $field => $value) {
                    if ($value) {
                        $argument->$field = $value;
                    } else {
                        $this->flashSession->error($this->getFieldName($field) . ' не может быть пустым');
                        $errors = TRUE;
                        //return $this->forward("/admin/index");
                    }
                }
                if (!$errors) {
                    $argument->argument_status = 1;
                    $argument->date = date('Y-m-d H:i:s');
                    $argument->save();
                    $this->flashSession->success('Довод успешно сохранен');
                }
            }
            
            $this->setMenu();
            $this->view->ArgumentsCategory = ArgumentsCategory::find();
        }
    }

    public function getFieldName($database_name){
        $fields = array(
            'name' => 'Заголовок довода',
            'text' => 'Текст довода',
            'category_id' => 'Категория довода',
        );
        return $fields[$database_name];
    }

    public function deleteAction(){
        $perm = new Permission();
        if (!$perm->actionIsAllowed($this->user->id, 'arguments', 'edit')) {
           $data = "access_denied";
        } else {
            $argument_ids = $this->request->getPost("ids");
            
            if(count($argument_ids)){
                $arguments = Arguments::find(
                    array(
                        'id IN ({ids:array})',
                        'bind' => array(
                            'ids' => $argument_ids
                        )
                    )
                )->delete();
            }
            $this->flashSession->success('Довод удален');
            $data = "ok";
        }
        $this->view->disable();
        echo json_encode($data);
    }

    public function deleteCategoryAction(){
        $perm = new Permission();
        if (!$perm->actionIsAllowed($this->user->id, 'arguments', 'edit')) {
           $data = "access_denied";
        } else {
            $argument_ids = $this->request->getPost("ids");
            if(count($argument_ids)){
                $arguments = Arguments::find(
                    array(
                        'category_id IN ({ids:array})',
                        'bind' => array(
                            'ids' => $argument_ids
                        )
                    )
                );
                if ($arguments->count()) {
                    $data = "false";
                } else {
                    $categories = ArgumentsCategory::find(
                    array(
                        'id IN ({ids:array})',
                        'bind' => array(
                            'ids' => $argument_ids
                        )
                    )
                )->delete();
                $this->flashSession->success('Категория удалена');
                $data = "ok";
                }
            }
        }
        $this->view->disable();
        echo json_encode($data);
    }
    
    public function addCategoryAction(){
        $perm = new Permission();
        if (!$perm->actionIsAllowed($this->user->id, 'arguments', 'edit')) {
           $data = "access_denied";
        } else {
            $parent_id = $this->request->getPost('parent_id');
            $parent_id = (isset($parent_id)) ? $parent_id : 0;
            $category_name = $this->request->getPost("name");
            if ($category_name) {
                $category = new ArgumentsCategory();
                $category->name = $category_name;
                $category->parent_id = $parent_id;
                $category->create();
                $this->flashSession->success('Категория сохранена');
            }
            $data = 'ok';
        }
        $this->view->disable();
        echo json_encode($data);
    }

    public function hideShowAction(){
        $perm = new Permission();
        if (!$perm->actionIsAllowed($this->user->id, 'arguments', 'edit')) {
           $data = "access_denied";
        } else {
            $argument_ids = $this->request->getPost("ids");
            $hide = $this->request->getPost("hide");
            
            if(count($argument_ids)){
                $arguments = Arguments::find(
                    array(
                        'id IN ({ids:array})',
                        'bind' => array(
                            'ids' => $argument_ids
                        )
                    )
                );
                foreach ($arguments as $argument) {
                    if ($hide) {
                        $argument->argument_status = 0;
                    } else {
                        $argument->argument_status = 1;
                    }
                    $argument->update();
                    /*if ($argument->save() == false) {
                        foreach ($argument->getMessages() as $message) {
                            $Message = $message->getMessage();
                            $Field = $message->getField();
                            $Type = $message->getType();
                        }
                    }*/
                }
                $this->flashSession->success('Изменения сохранены');
                $data = "ok";
            }
        }
        $this->view->disable();
        echo json_encode($data);
    }



    /*AJAX*/

//    public function getAjaxCategoryAction(){
//        $obj = new ArgumentsCategory();
//        $data = $obj->getAllCategory();
//        $data = $obj->buildTreeArray( $data );
//        echo json_encode( $data );
//    }


    public function ajaxRemoveAction(){
        $perm = new Permission();
        if (!$perm->actionIsAllowed($this->user->id, 'arguments', 'edit')) {
            $this->view->pick("access/denied");
            $this->setMenu();
        } else {
            $id = $this->request->get('id');
            $argument = $this->request->get('argument');
            if (!is_numeric($id)) {
                echo "bad data";
                exit;
            }
            if (isset($argument) && $argument == true) {
                Arguments::find(
                    array(
                        "id = {$id}",
                    )
                )->delete();
                echo json_encode(array('status' => 'ok'));
                exit;
            } else {
                $this->deleteTrees($id);
                echo json_encode(array('status' => 'ok'));
                exit;
            }
        }
        echo json_encode(array('status' => 'err'));
        exit;
    }

    private function deleteTrees($id){
        Arguments::find(
            array(
                "category_id = {$id}",
            )
        )->delete();
        $categories = ArgumentsCategory::find(
            array(
                "parent_id = {$id}"
            )
        );
        if(count($categories)) {
            foreach ($categories as $key) {
                $this->deleteTrees($key->id);
            }
        }
        ArgumentsCategory::find(
            array(
                "id = {$id}"
            )
        )->delete();
    }

    public function ajaxGetCatArgumentsAction(){
        $id = $this->request->get('id');
        if(!is_numeric($id)){
            echo json_encode(array('error' => 'bad data'));
            exit;
        }

        $result = array(
            "cat_arguments" => array(),
            "arguments"     => array()
        );

        $cat_arguments = ArgumentsCategory::find("parent_id = {$id}");
        $arguments = Arguments::query()
            ->where("category_id = {$id}")
            ->execute();

        foreach($cat_arguments as $cat){
            $result["cat_arguments"][] = array(
                "id"        => $cat->id,
                "name"      => $cat->name,
                "parent_id" => $cat->parent_id,
            );
        }
        foreach($arguments as $argument){
            $result['arguments'][] = array(
                'id'        => $argument->id,
                'name'      => $argument->name,
                'text'      => $argument->text,
                'category_id' => $argument->category_id,
            );
        }

        echo json_encode($result);
    }

    public function ajaxAddCategoryAction(){
        $perm = new Permission();
        if (!$perm->actionIsAllowed($this->user->id, 'arguments', 'edit')) {
            echo json_encode(array('status' => 'permission denied'));
        } else {
            $parent_id = $this->request->get('parent_id');
            $parent_id = (isset($parent_id)) ? $parent_id : 0;
            $category_name = $this->request->get("name");
            if ($category_name) {
                $category = new ArgumentsCategory();
                $category->name = $category_name;
                $category->parent_id = $parent_id;
                $category->create();

                echo json_encode(array(
                    'id'   => $category->id,
                    'name' => $category->name,
                    'parent_id' => $category->parent_id,
                ));
            }
        }
    }


    public function ajaxAddArgumentsAction(){
        $perm = new Permission();
        if (!$perm->actionIsAllowed($this->user->id, 'arguments', 'edit')) {
            $this->view->pick("access/denied");
            $this->setMenu();
        } else {
            $data = $this->request->get("arguments");
            if(!isset($data)){
                echo json_encode(array( 'status' => 'non data' ));
                exit;
            } else if(!isset($data['category_id']) || !is_numeric($data['category_id'])){
                echo json_encode(array( 'status' => 'bad id' ));
                exit;
            }
            $errors = FALSE;
            $err_arr = array();
            if ($data) {
                $argument = new Arguments();
                foreach ($data as $field => $value) {
                    if ($value) {
                        $argument->$field = $value;
                    } else {
                        $err_arr[] = $this->getFieldName($field) . ' не может быть пустым';
                        $errors = TRUE;
                    }
                }
                if (!$errors) {
                    $argument->argument_status = 1;
                    $argument->date = date('Y-m-d H:i:s');
                    $argument->save();
                    echo json_encode(array(
                        'id'            => $argument->id,
                        'category_id'   => $argument->category_id,
                        'name'          => $argument->name,
                        'text'          => $argument->text
                    ));
                    exit;
                } else {
                    echo json_encode(array( 'status' => $err_arr ));
                    exit;
                }
            }
        }
    }


    public function ajaxEditAction(){
        $perm = new Permission();
        if (!$perm->actionIsAllowed($this->user->id, 'arguments', 'edit')) {
            $this->view->pick("access/denied");
            $this->setMenu();
        } else {
            $edit = $this->request->get("edit");
            if(!isset($edit['id']) || !is_numeric($edit['id'])){
                echo json_encode(array('err' => 'bad id'));
                exit;
            }
            if(!isset($edit['name']) || trim($edit['name']) == ''){
                echo json_encode(array('err' => 'bad name'));
                exit;
            }
            if(isset($edit['arg']) && $edit['arg'] == true){
                if(!isset($edit['text']) && trim($edit['text']) == ''){
                    echo json_encode(array('err' => 'bad text'));
                    exit;
                }
                $id = $edit['id'];
                $argument = Arguments::findFirst($id);
                $argument->name = trim($edit['name']);
                $argument->text = trim($edit['text']);
                $argument->save();
                echo json_encode(array(
                    'id'            => $argument->id,
                    'category_id'   => $argument->category_id,
                    'name'          => $argument->name,
                    'text'          => $argument->text
                ));
            } else {
                $id = $edit['id'];
                $category = ArgumentsCategory::findFirst($id);
                $category->name = trim($edit['name']);
                $category->save();
                echo json_encode(array(
                    'id'        => $category->id,
                    'name'      => $category->name,
                    'parent_id' => $category->parent_id
                ));
            }
        }
    }
}