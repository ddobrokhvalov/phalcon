<?php
namespace Multiple\Backend\Models;
use Phalcon\Mvc\Model;

class Arguments extends Model
{
    public $id;
    public $name;
    public $argument_status;
    public $type;
    public $comment;

    public function initialize()
    {
        $this->setSource('arguments');
        $this->allowEmptyStringValues(['comment']);
    }

    public function getSource()
    {
        return 'arguments';
    }

}