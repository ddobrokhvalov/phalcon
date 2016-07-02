<?php
namespace Multiple\Frontend\Models;

use Phalcon\Mvc\Model;

class UsersArguments extends Model
{
    public $id;
    public $argument_id;
    public $text;
    public $complaint_id;
    public $argument_order;
    public $argument_category_id;

    public function initialize()
    {
        $this->setSource('users_arguments');
    }

    public function getSource()
    {
        return 'users_arguments';
    }

}