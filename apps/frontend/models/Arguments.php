<?php
namespace Multiple\Frontend\Models;

use Phalcon\Mvc\Model;

class Arguments extends Model
{
    public $id;
    public $name;
    public $text;
    public $category_id;
    public $date;
    public $argument_status;

    public function initialize()
    {
        $this->setSource('arguments');
    }

    public function getSource()
    {
        return 'arguments';
    }

}