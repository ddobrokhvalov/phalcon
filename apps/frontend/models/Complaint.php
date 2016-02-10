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

    public function addComplaint($data,$status = 'draft')
    {

        $this->status = $status;
        $this->date = date('Y-m-d H:i:s');
        foreach ($data as $k => $v) {
            $this->$k = $v;
        }
    }

}