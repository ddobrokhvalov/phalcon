<?php
namespace Multiple\Backend\Models;

use Phalcon\Mvc\Model;
use Multiple\Backend\Models\Question;

class Complaint extends Model
{
    public $id;
    public $applicant_id;
    public $auction_id;
    public $type;
    public $purchases_made;
    public $purchases_name;
    public $contact;
    public $complaint_name;
    public $complaint_text;
    public $status;
    public $date;
    public $nachalo_podachi;
    public $okonchanie_podachi;
    public $vskrytie_konvertov;
    public $data_rassmotreniya;
    public $data_provedeniya;
    public $okonchanie_rassmotreniya;
    public $vremya_provedeniya;
    public $text;

    public function initialize()
    {
        $this->setSource('complaint');
        $this->hasMany('id', 'Question', 'complaint_id');
    }

    public function getSource()
    {
        return 'complaint';
    }

    public function getComplainQuestion(){
        return Question::find(array(
            "complaint_id = :complaint_id:",
            'bind' => array(
                'complaint_id' => $this->id,
            ),
            "order" => "id DESC",
        ));
    }

    public function findUserComplaints($user_id, $status,$applicant_id =false)
    {
        $db = $this->getDi()->getShared('db');
        $sql = "SELECT c.*, ap.name_short as apname FROM complaint as c
         LEFT JOIN applicant ap ON(c.applicant_id = ap.id )
         LEFT JOIN user u ON(ap.user_id = u.id )
         WHERE u.id =$user_id "; //todo: do we really need LEFT JOIN if the filter on the last RIGHT table? It will return something ONLY if u.id is not NULL!
        if ($status) {
            $sql .= " AND c.status = '$status'";
        }
        if($applicant_id && $applicant_id != 'All'){
            $sql .= " AND ap.id = $applicant_id";
        }
        $result = $db->query($sql);
        return $result->fetchAll();

    }

    public function findApplicantComplaints($applicant_id)
    {
        $db = $this->getDi()->getShared('db');
        $sql = "SELECT c.*, ap.name_short as apname FROM complaint as c
         INNER JOIN applicant ap ON(c.applicant_id = ap.id )
         WHERE ap.id = $applicant_id "; 
        $result = $db->query($sql);
        return $result->fetchAll();

    }

    public function addComplaint($data, $status = 'draft')
    {
        // test mode
        //$status = 'submitted';
            //
        $this->status = $status;
        $this->date = date('Y-m-d H:i:s');
        foreach ($data as $k => $v) {

            $this->$k = $v;
        }
    }

    public function findCountUserComplaints($user_id)
    {
        $db = $this->getDi()->getShared('db');
        $result = $db->query("SELECT COUNT(c.id) as num, c.status  FROM complaint as c
         LEFT JOIN applicant ap ON(c.applicant_id = ap.id )
         LEFT JOIN user u ON(ap.user_id = u.id )
         WHERE u.id =$user_id GROUP BY c.status ");  //todo: do we really need LEFT JOIN if the filter on the last RIGHT table? It will return something ONLY if u.id is not NULL!
        $result = $result->fetchAll();
        $total = 0;
        $complaints_num = array();
        foreach ($result as $v) {
            $total += $v['num'];
            $complaints_num[$v['status']] = $v['num'];
        }
        return array(
            'total' => $total,
            'complaints_num' => $complaints_num
        );
    }

    public function checkComplaintOwner($id, $user_id)
    {
        $db = $this->getDi()->getShared('db');
        $result = $db->query("SELECT c.id, u.id as user_id FROM complaint as c
         LEFT JOIN applicant ap ON(c.applicant_id = ap.id )
         LEFT JOIN user u ON(ap.user_id = u.id )
         WHERE c.id =$id");
        $result = $result->fetch();
        if ($result && $result['user_id'] == $user_id)
            return true;
        return false;
    }
    public function findForParser(){
        $db = $this->getDi()->getShared('db');
        $result = $db->query("SELECT c.id, c.auction_id, c.date, ap.name_full, c.status FROM complaint as c
         LEFT JOIN applicant ap ON(c.applicant_id = ap.id )
         WHERE c.status = 'under_consideration' OR c.status = 'submitted'");
         return $result->fetchAll();
    }

    public function changeStatus($status, $data, $user_id = false)//todo: do we need user_id
    {
        foreach ($data as $id) { //todo: make through db query this. 'id IN ()' is faster then ORM
                                 //todo: $complaint->checkComplaintOwner($v, $this->user->id) add this
         //   if ($this->checkComplaintOwner($id, $user_id)) {
                $complaint = Complaint::findFirstById($id);
                if(!$complaint || $complaint->status==$status || ($complaint->status!='submitted' && $status=='recall' && $user_id!='parser'))
                    continue;
                elseif ($status == 'activate') {  //This return from arhive. We need to check history and set last status.
                    $complainthistory = ComplaintMovingHistory::findFirst(array("complaint_id = :complaint_id:", "bind" => array("complaint_id" => $id), "order" => "date desc"));
                    if($complainthistory)
                        $this->changeStatus($complainthistory->old_status, [$id]);
                    else
                        $this->changeStatus('draft', [$id]);
                }
                elseif ($status == 'delete') {
                    ComplaintMovingHistory::delete_history($id);                      
                    $complaint->delete();
                }elseif ($status == 'copy') {
                    $newComplaint = new Complaint();
                    foreach($complaint as $k=>$v)
                        $newComplaint->$k = $v;
                    $newComplaint->id = NULL;
                    $newComplaint->complaint_name .= ' (Копия)';
                    $newComplaint->status = 'draft';
                    $newComplaint->save();
                    return $newComplaint->id;
                } else {
                    $complaintmovinghistory = new ComplaintMovingHistory();
                    $complaintmovinghistory->save(['complaint_id'=>$id, 'old_status'=>$complaint->status, 'new_status'=>$status]);
                    $complaint->status = $status;
                    $complaint->save();
                }
          //  } else {
         //       continue;
          //  }
        }
    }

    public function saveComplaint($data){
        $this->type = $data['type'];
        $this->purchases_made = $data['purchases_made'];
        $this->purchases_name = $data['purchases_name'];
        $this->contact = $data['contact'];
        if(isset($data['auction_id'])) $this->auction_id = $data['auction_id'];
        $this->nachalo_podachi = $data['nachalo_podachi'];
        $this->okonchanie_podachi = $data['okonchanie_podachi'];
        $this->vskrytie_konvertov = $data['vskrytie_konvertov'];
        $this->data_rassmotreniya = $data['data_rassmotreniya'];
        $this->data_provedeniya = $data['data_provedeniya'];
        $this->okonchanie_rassmotreniya = $data['okonchanie_rassmotreniya'];
        $this->vremya_provedeniya = $data['vremya_provedeniya'];
        $this->complaint_text = $data['complaint_text'];

        return $this->save();
    }

}