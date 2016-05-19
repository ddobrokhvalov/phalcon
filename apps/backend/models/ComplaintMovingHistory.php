<?php
namespace Multiple\Backend\Models;

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

    public static function delete_history($complaint_id){
        $complaintmovinghistory_arr = self::find(array(
                "complaint_id = :complaint_id:",
                "bind" => array("complaint_id" => $complaint_id)
            )
        );
        foreach ($complaintmovinghistory_arr as $item) $item->delete(); //todo: make through db query this
    }
}