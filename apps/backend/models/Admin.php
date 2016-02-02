<?php
namespace Multiple\Backend\Models;
use Phalcon\Mvc\Model;
use  Multiple\Library\PHPImageWorkshop\ImageWorkshop;

class Admin extends Model
{
    public $id;
    public $email;
    public $password;
    public $surname;
    public $name;
    public $patronymic;
    public $avatar;

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
    public function getPassword()
    {
        return $this->password;
    }
    public function getFullName(){
        return $this->surname.' '.$this->name.' '.$this->patronymic;
    }
    public function getSurnameAndInitials(){
        return $this->surname.' '.substr($this->name,0,1).'.'.substr($this->patronymic,0,1).'.';
    }
    public function saveAvatar($avatar){


        $layer = ImageWorkshop::initFromPath($avatar['tmp_name']);
        var_dump($layer); exit;
    }
}