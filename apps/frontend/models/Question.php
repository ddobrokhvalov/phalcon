<?php
namespace Multiple\Frontend\Models;

use Phalcon\Mvc\Model;

class Question extends Model
{
    public $id;
    public $user_id;
    public $complaint_id;
    public $text;
    public $date;

    public function initialize()
    {
        $this->setSource("question");
    }

    public function getSource()
    {
        return "question";
    }
}