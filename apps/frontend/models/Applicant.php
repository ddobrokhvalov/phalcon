<?php
namespace Multiple\Frontend\Models;

use Phalcon\Mvc\Model;

class Applicant extends Model
{
    public $id;
    public $user_id;
    public $type;
    public $name_full;
    public $name_short;
    public $inn;
    public $kpp;
    public $address;
    public $position;
    public $fio_applicant;
    public $fio_contact_person;
    public $telefone;
    public $email;

    public function initialize()
    {
        $this->setSource('applicant');
    }

    public function getSource()
    {
        return 'applicant';
    }

    public function addApplicant($user_id, $data)
    {
        $this->name_short = '-';
        $this->name_full = '-';
        $this->position = '-';
        $this->inn = '-';
        $this->kpp = '-';
        $this->user_id = $user_id;
        foreach ($data as $k => $v) {
            $this->$k = $v;
        }
    }

    public function findByUserId($user_id)
    {
        $result = Applicant::find(
        array(
            "user_id = :user_id: AND is_blocked = 1",
            'bind' => array(
                'user_id' => $user_id
            )
        )
    );

        return $result;
    }

    public function saveFiles($files)
    {
      for($i=0;$i<count($files['name']); $i++){
          if($files['size'][$i] > (1000000  * 50))
              continue;

          $info = pathinfo($files['name'][$i]);
          $ext = $info['extension'];
          $newname = $this->user_id.$this->id.'_'.$info['basename'];
          $target = 'files/applicant/'.$newname;
          if(move_uploaded_file( $files['tmp_name'][$i], './'.$target)){
              $this->saveFileInDb($newname,$target);
          }
      }

    }
    public function getApplicantFiles($id){
        $this->db=$this->getDi()->getShared('db');
        $result=$this->db->query("SELECT * FROM applicant_file WHERE applicant_id=$id");
        return $result->fetchAll();
    }
    private function saveFileInDb($name, $path){

        $sql = "INSERT INTO applicant_file (`applicant_id`, `name`,`path`) VALUES('$this->id', '$name', '$path')";
        $db = $this->getDi()->getShared('db');
        $db->query($sql);
    }
    public function checkFileOwner($user_id, $id){
        $db = $this->getDi()->getShared('db');
        $result = $db->query("SELECT ap.id as app_id,ap.user_id, af.path, af.id as id FROM applicant_file af
        LEFT JOIN applicant ap ON(af.applicant_id = ap.id )
        WHERE af.id=$id");
        $result = $result->fetch();
        if($result && $result['user_id'] == $user_id)
            return $result;
        return false;
    }
    public function deleteFile($applicantFile){

        unlink('./'.$applicantFile['path']);
        $db = $this->getDi()->getShared('db');
        return $db->query("DELETE FROM applicant_file  WHERE id=".$applicantFile['id']);
    }
    public function checkInn($inn){
        $result = Applicant::find(
            array(
                "inn = :inn: ",
                'bind' => array(
                    'inn' => $inn
                )
            )
        );

        return $result->count();
    }
}