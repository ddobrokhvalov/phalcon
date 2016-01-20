<?php
namespace Multiple\Backend\Models;
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
    public function findByUserId($user_id){
        $result = Applicant::find(
            array(
                "user_id = :user_id: ",
                'bind' => array(
                    'user_id' => $user_id
                )
            )
        );

        return $result;
    }
}