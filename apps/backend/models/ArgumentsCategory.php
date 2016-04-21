<?php
namespace Multiple\Backend\Models;
use Phalcon\Mvc\Model;

class ArgumentsCategory extends Model
{
    public $id;
    public $name;

    public function initialize()
    {
        $this->setSource('arguments_categoty');
    }

    public function getSource()
    {
        return 'arguments_categoty';
    }
}