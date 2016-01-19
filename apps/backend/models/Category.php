<?php
namespace Multiple\Backend\Models;

use Phalcon\Mvc\Model;

class Category extends Model
{
    public $id;
    public $name;

    public function initialize()
    {
        $this->setSource("category_template");
    }

    public function getSource()
    {
        return "category_template";
    }

}