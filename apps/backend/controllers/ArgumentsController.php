<?php
namespace Multiple\Backend\Controllers;

use Phalcon\Mvc\Controller;
use Multiple\Backend\Models\ArgumentsCategory;
use Multiple\Library\PaginatorBuilder;

class ArgumentsController  extends ControllerBase
{

    public function indexAction(){
        $Arguments = $this->modelsManager->createBuilder()
            ->columns('Multiple\Backend\Models\Arguments.date, Multiple\Backend\Models\Arguments.name, Multiple\Backend\Models\ArgumentsCategory.name as catname')
            ->from('Multiple\Backend\Models\Arguments')
            ->join('Multiple\Backend\Models\ArgumentsCategory', 'Multiple\Backend\Models\ArgumentsCategory.id = Multiple\Backend\Models\Arguments.category_id')
            ->getQuery()
            ->execute();
        $ArgumentsCategory = ArgumentsCategory::find();
        $this->view->Arguments = $Arguments;
        $this->view->ArgumentsCategory = $ArgumentsCategory;
    }
}