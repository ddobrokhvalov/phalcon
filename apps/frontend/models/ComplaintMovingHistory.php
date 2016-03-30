<?php
namespace Multiple\Frontend\Models;

use Phalcon\Mvc\Model;

class ComplaintMovingHistory extends Model
{
    public $id;
    public $complaint_id;
    public $old_status;
    public $new_status;
    public $date;
    //todo: make connection to Complaint
    public function initialize()
    {
        $this->setSource('complaint_moving_history');
    }

    public function getSource()
    {
        return 'complaint_moving_history';
    }
}