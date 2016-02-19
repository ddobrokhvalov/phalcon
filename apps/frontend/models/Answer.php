<?php
namespace Multiple\Frontend\Models;

use Phalcon\Mvc\Model;

class Answer extends Model
{
    public $id;
    public $question_id;
    public $admin_id;
    public $text;
    public $date;

    public function initialize()
    {
        $this->setSource("answer");
    }

    public function getSource()
    {
        return "answer";
    }
    public  function findByQuestion($question_id)
    {
        $db = $this->getDi()->getShared('db');
        $result=$db->query("SELECT * FROM `answer` WHERE `question_id`=$question_id");
        return $result->fetch();


    }
}