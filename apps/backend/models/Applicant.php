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
        $this->allowEmptyStringValues(['fio_applicant', 'name_short', 'name_full', 'position', 'inn', 'kpp']);
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

    public function findByUserIdWithAdditionalInfo($user_id){
        $db = $this->getDi()->getShared('db');
        $sql = "SELECT ap.*, count(c.id) as cnt FROM complaint as c
         INNER JOIN applicant ap ON(c.applicant_id = ap.id )         
         WHERE ap.user_id =$user_id
         GROUP BY ap.id";
        $result = $db->query($sql);
        return $result->fetchAll();
    }
}