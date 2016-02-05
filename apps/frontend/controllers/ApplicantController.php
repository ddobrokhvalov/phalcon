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
    public function editAction($id)
    {
        $applicant = Applicant::findFirstById($id);
        if(!$applicant || $applicant->user_id != $this->user->id)
            return $this->forward('complaint/index');


        $applicantFiles =  $applicant->getApplicantFiles($applicant->id);
        $this->view->setTemplateAfter('menu');
        $this->view->applicant = $applicant;
        $this->view->afiles = $applicantFiles;

    }
    public function addAction()
    {
        $this->view->setTemplateAfter('menu');
    }
    public function createAction()
    {

        if (!$this->request->isPost()) {
            return $this->forward('applicant/add');
        }
        $data = $this->request->getPost();
        if(isset($data['id'])){
            $applicant = Applicant::findFirstById($data['id']);
            unset($data['id']);
            if(!$applicant)
                return $this->forward('complaint/index');
        }else {
            $applicant = new Applicant();
        }
        $applicant->addApplicant($this->user->id,$data);

      if($applicant->save()){
          if(strlen($_FILES['file']['name'][0])>0)
              $applicant->saveFiles($_FILES['file']);
          return $this->forward('complaint/index');
      }else{
          return $this->forward('applicant/add');
      }

    }
    public function delfileAction($id){
        $applicant = new Applicant();
        $applicantFile = $applicant->checkFileOwner($this->user->id, $id);
       // var_dump($applicantFile); exit;
        if($applicantFile){
            $applicant->deleteFile($applicantFile);
            return $this->forward('applicant/edit/'.$applicantFile['app_id']);
        }else{
            return $this->forward('complaint/index');
        }
    }
    public function deleteAction($id){
        $applicant = Applicant::findFirstById($id);
        if(!$applicant || $applicant->user_id != $this->user->id)
            return $this->forward('complaint/index');


        $appFiles = $applicant->getApplicantFiles($id);
        foreach($appFiles as $file){
            $applicant->deleteFile($file);
        }

        $applicant->delete();
        return $this->forward('complaint/index');
    }

}