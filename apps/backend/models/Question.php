<?php
namespace Multiple\Frontend\Models;

use Phalcon\Mvc\Model;
use Multiple\Frontend\Models\Admin;
use Multiple\Frontend\Models\Answer;

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
    public function getComplainQuestionAndAnswer($complaint_id){
        $result = Question::find(
            array(
                "complaint_id = :complaint_id: ",
                'bind' => array(
                    'complaint_id' => $complaint_id
                )
            )
        );
        $qa = array();
        $answerM = new Answer();
        foreach($result as $v){

            $answer = $answerM->findByQuestion($v->id);

            $qa[] = array(
                'question' => $v,
                'answer' => $answer,
                'admin' => (isset($answer['id']))?Admin::findFirstById($answer['admin_id']):false

            );

        }
        return $qa;
    }
}