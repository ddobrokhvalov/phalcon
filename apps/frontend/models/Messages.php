<?php
namespace Multiple\Frontend\Models;

use Phalcon\Mvc\Model;

class Messages extends Model
{
    public $id;
    public $from_uid;
    public $to_uid;
    public $subject;
    public $body;
    public $time;
    public $stat_comp;

    public function initialize()
    {
        $this->setSource('messages');
    }

    public function getSource()
    {
        return 'messages';
    }

}