<?php
namespace Multiple\Backend\Controllers;

use Phalcon\Mvc\Controller;
use Phalcon\Mvc\Model;
use Multiple\Backend\Models\ArgumentsCategory;
use Multiple\Library\PaginatorBuilder;
use Multiple\Backend\Models\Arguments;

class ArgumentsController  extends ControllerBase
{

    public function indexAction(){
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
        $this->view->ArgumentsCategory = ArgumentsCategory::find();
        $this->setMenu();
    }

    public function addAction(){
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

    public function getFieldName($database_name){
        $fields = array(
            'name' => 'Заголовок довода',
            'text' => 'Текст довода',
            'category_id' => 'Категория довода',
        );
        return $fields[$database_name];
    }

    public function deleteAction(){
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
        $this->view->disable();

        $data = "ok";
        echo json_encode($data);
    }

    public function deleteCategoryAction(){
        $data = "ok";
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
            }
        }
        $this->view->disable();
        echo json_encode($data);
    }
    
    public function addCategoryAction(){
        $category_name = $this->request->getPost("name");
        if ($category_name) {
            $category = new ArgumentsCategory();
            $category->name = $category_name;
            $category->create();
        }

        $this->view->disable();
        echo json_encode("");
    }

    public function hideShowAction(){
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
        }
        $this->view->disable();

        $data = "ok";
        echo json_encode($data);
    }
}