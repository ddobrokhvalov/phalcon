<?php
namespace Multiple\Frontend\Models;

use Phalcon\Mvc\Model;

class Configurations extends Model
{
    public $id;
    public $group_name;
    public $name;
    public $value;

    public function initialize()
    {
        $this->setSource("configurations");
    }

    public function getSource()
    {
        return "configurations";
    }
}