<?php
namespace Multiple\Frontend\Models;

use Phalcon\Mvc\Model;

class Complaint extends Model
{
    public $id;
    public $applicant_id;
    public $type;
    public $purchases_made;
    public $purchases_name;
    public $contact;
    public $date_start;
    public $date_end;
    public $date_opening;
    public $date_review;
    public $complaint_name;
    public $complaint_text;
    public $status;
    public $date;

    public function initialize()
    {
        $this->setSource('complaint');
    }

    public function getSource()
    {
        return 'complaint';
    }

    public function findUserComplaints($user_id)
    {
        $db = $this->getDi()->getShared('db');
        $result = $db->query("SELECT c.*, ap.name_short as apname FROM complaint as c
         LEFT JOIN applicant ap ON(c.applicant_id = ap.id )
         LEFT JOIN user u ON(ap.user_id = u.id )
         WHERE u.id =$user_id ");
        return $result->fetchAll();

    }

    public function addComplaint($data, $status = 'draft')
    {

        $this->status = $status;
        $this->date = date('Y-m-d H:i:s');
        foreach ($data as $k => $v) {
            $this->$k = $v;
        }
    }

}