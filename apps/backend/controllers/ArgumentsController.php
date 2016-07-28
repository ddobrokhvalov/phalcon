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
//            $obj = new ArgumentsCategory();
//            $data = $obj->getAllCategory();
//            $data = $obj->buildTreeArray( $data );
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

    public function getAjaxCategoryAction(){
        $obj = new ArgumentsCategory();
        $data = $obj->getAllCategory();
        $data = $obj->buildTreeArray( $data );
        echo json_encode( $data );
    }


    public function ajaxRemoveCatAction(){
        $category = $this->request->get('category');
        $arguments = $this->request->get('arguments');
        if($category != '') $category = json_decode($category);
        if($category != '') $category = json_decode($arguments);

        if(count($arguments)){
            $arguments = Arguments::find(
                array(
                    'id IN ({ids:array})',
                    'bind' => array(
                        'ids' => $arguments
                    )
                )
            )->delete();
        }

        if(count($category)){
            $categories = ArgumentsCategory::find(
                array(
                    'id IN ({ids:array})',
                    'bind' => array(
                        'ids' => $category
                    )
                )
            )->delete();
        }
    }

    public function ajaxRemoveArgAction(){
        $arguments = $this->request->get('arguments');
        if($arguments != '') $category = json_decode($arguments);

        if(count($arguments)){
            $arguments = Arguments::find(
                array(
                    'id IN ({ids:array})',
                    'bind' => array(
                        'ids' => $arguments
                    )
                )
            )->delete();
        }
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
                'category_id' => $argument->category_id,
            );
        }

        echo json_encode($result);
    }

}