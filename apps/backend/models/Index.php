<?php
namespace Multiple\Backend\Models;
use Phalcon\Mvc\Model;

class Index extends Model
{
    public $id;
    public $name;

    public function initialize()
    {
        $this->setSource("product_types");
    }
    public function getSource()
    {
        return "product_types";
    }
    public function getId()
    {
        return $this->id;
    }
    public function getName()
    {
        return $this->name;
    }
}