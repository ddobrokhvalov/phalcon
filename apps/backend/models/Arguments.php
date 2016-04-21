<?php
namespace Multiple\Backend\Models;
use Phalcon\Mvc\Model;

class Arguments extends Model
{
    public $id;
    public $name;

    public function initialize()
    {
        $this->setSource('arguments');
    }

    public function getSource()
    {
        return 'arguments';
    }
}