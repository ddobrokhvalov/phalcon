<?php
namespace Multiple\Frontend\Models;

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

        $this->hasMany(
            "id",
            "Orders",
            "user_id"
        );
    }

    public function getSource()
    {
        return 'user';
    }

    public function getSurnameAndInitials() {
        return $this->surname.' '.substr($this->name,0,1).'.'.substr($this->patronymic,0,1).'.';
    }

    public function getEmail() {
        return $this->email;
    }
}