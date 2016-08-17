<?php
namespace Multiple\Backend\Models;
use Phalcon\Mvc\Model;

class Ufas extends Model
{
    public $id;
    public $name;
    public $number;
    public $address;
    public $phone;
    public $email;

    public function initialize()
    {
        $this->setSource('ufas');
    }

    public function getSource()
    {
        return 'ufas';
    }

}