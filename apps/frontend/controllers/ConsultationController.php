<?php

namespace Multiple\Frontend\Controllers;

use Multiple\Frontend\Models\Question;
use Phalcon\Mvc\Controller;


class ConsultationController extends ControllerBase
{
    public function indexAction()
    {

    }

    public function addquestionAction()
    {
        if (!$this->request->isPost()) {
            echo 'error';
            exit;
        }
        $data = $this->request->getPost();
        if(isset($data['question_text']) && isset($data['complaint_id'])){

            $question = new Question();
            $question->user_id = $this->user->id;
            $question->complaint_id = $data['complaint_id'];
            $question->text = $data['question_text'];
            $question->date = date("Y-m-d H:i:s");
            $question->save();

            echo $question->id; exit;

        }else{
            echo 'error';
            exit;
        }


    }

}

