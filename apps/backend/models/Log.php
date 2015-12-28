<?php
namespace Multiple\Backend\Models;
use Phalcon\Mvc\Model;

class Log extends Model
{
    public $id;
    public $admin_id;
    public $target;
    public $log_type;
    public $user_id;
    public $date;
    public function initialize()
    {
        $this->setSource("log");
    }
    public function getSource()
    {
        return "log";
    }

}