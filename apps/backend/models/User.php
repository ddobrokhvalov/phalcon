<?php
namespace Multiple\Backend\Models;

use Phalcon\Mvc\Model;

class User extends Model
{
    public $id;
    public $email;
    public $password;
    public $status;
    public $date_registration;

    public function initialize()
    {
        $this->setSource('user');
    }

    public function getSource()
    {
        return 'user';
    }
}