<?php
namespace Multiple\Backend\Models;

use Phalcon\Mvc\Model;
use Phalcon\Db\RawValue;

class User extends Model
{
    public $id;
    public $email;
    public $password;
    public $status;
    public $date_registration;
    public $admin_comment;
    public $firstname;
    public $lastname;
    public $patronymic;
    public $phone;
    public $activity;

    public function initialize()
    {
        $this->setSource('user');
        $this->allowEmptyStringValues(['admin_comment', 'firstname', 'lastname', 'patronymic', 'phone', 'status', 'date_registration', 'activity']);
        $this->status = new RawValue('default');
        $this->date_registration = new RawValue('Now()');//todo: check when edit
        $this->activity = new RawValue('default');
    }

    public function getSource()
    {
        return 'user';
    }
    
    public function getComplaintColor($status){
        $applicant = new Applicant();
        return $applicant->getComplaintColor($status);
    }
    
    public function getComplaintStatus($status, $short){
        $applicant = new Applicant();
        return $applicant->getComplaintStatus($status, $short);
    }
    
    public function getAllStatuses($index){
        $applicant = new Applicant();
        return $applicant->getAllStatuses($index);
    }
}