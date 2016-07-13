<?php
namespace Multiple\Backend\Models;

use Phalcon\Mvc\Model;
use Multiple\Backend\Models\Admin;
use Multiple\Backend\Models\Answer;
use Multiple\Backend\Models\Complaint;


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
        $this->belongsTo('complaint_id', 'Multiple\Backend\Models\Complaint', 'id');
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

    public function countNonRead(){
        $count = Question::find("is_read='n'");
        return count($count);
    }
}