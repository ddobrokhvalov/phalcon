<?php
namespace Multiple\Frontend\Models;

use Phalcon\Mvc\Model;

class ApplicantECP extends Model
{
    public $id;
    public $applicant_id;
    public $thumbprint;
    public $activ;
    public $name_ecp;


    public function initialize()
    {
        $this->setSource('applicant_ecp');
    }

    public function getSource()
    {
        return 'applicant_ecp';
    }
    public function deactiveOtherECP($ecp_id, $applicant_id){
        $sql =" UPDATE `applicant_ecp`
        SET `activ`= 0 WHERE applicant_id = $applicant_id AND  id <> $ecp_id ";

        $db = $this->getDi()->getShared('db');
        $result = $db->query($sql);
    }

}