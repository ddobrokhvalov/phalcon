<?php
namespace Multiple\Frontend\Controllers;

use Phalcon\Mvc\Controller;
use Multiple\Frontend\Models\Applicant;

class ApplicantController extends ControllerBase
{
    public function indexAction()
    {
        $this->view->setTemplateAfter('menu');
    }
    public function editAction()
    {

    }
    public function addAction()
    {
        $this->view->setTemplateAfter('menu');
    }
    public function createAction()
    {
        echo '<pre>';

       //var_dump($_POST);
        echo '<hr>';
        var_dump($_FILES);
        echo '</pre>'; exit;
        if (!$this->request->isPost()) {
            return $this->forward('applicant/add');
        }
        $data = $this->request->getPost();
        $applicant = new Applicant();
        $applicant->addApplicant($this->user->id,$data);
      if($applicant->save()){

          if(strlen($_FILES['file']['name'][0])>0)
              $applicant->saveFiles($_FILES['file']);


          return $this->forward('complaint/index');
      }else{
          return $this->forward('applicant/add');
      }

    }

}