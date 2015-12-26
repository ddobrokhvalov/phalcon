<?php
namespace Multiple\Backend\Models;
use Phalcon\Mvc\Model;

class Admin extends Model
{
    public $id;
    public $email;
    public $role;

    public function initialize()
    {
        $this->setSource("admin");
    }
    public function getSource()
    {
        return "admin";
    }
    public function getId()
    {
        return $this->id;
    }
    public function getEmail()
    {
        return $this->email;
    }
    public function getRole()
    {
        return $this->role;
    }
}