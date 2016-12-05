<?php
namespace Multiple\Frontend\Models;
use Multiple\Library\Parser;
use Phalcon\Mvc\Model;
use Multiple\Frontend\Models\Messages;
use Multiple\Frontend\Models\UsersArguments;

class Complaint extends Model
{
    public $id;
    public $applicant_id;
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
    public $complaint_text_order;
    public $ufas_id;
    public $date_submit;
    public $user_id;

    public function initialize()
    {
        $this->setSource('complaint');
    }

    public function getSource()
    {
        return 'complaint';
    }


    public function findUserComplaints($user_id, $status, $applicant_id = false, $search = false)
    {
        $db = $this->getDi()->getShared('db');
        $sql = "SELECT c.*, ap.name_short as apname FROM complaint as c
         LEFT JOIN applicant ap ON(c.applicant_id = ap.id AND ap.is_blocked = 1)
         LEFT JOIN user u ON(ap.user_id = u.id )
         WHERE u.id =$user_id  "; //todo: do we really need LEFT JOIN if the filter on the last RIGHT table? It will return something ONLY if u.id is not NULL!

        if($search){
            $sql .= "AND (c.complaint_name LIKE '%{$search}%' OR c.auction_id LIKE '%{$search}%')";
        }
        if ($status) {
            $sql .= " AND c.status = '$status'";
        }
        if($applicant_id && $applicant_id != 'All'){
            $temp = explode(',' , $applicant_id);
            foreach($temp as $key => $val){
                if($temp[$key] == 'All' || $temp[$key] == ''){
                    unset($temp[$key]);
                }
            }
            $temp = implode(',', $temp);
            if($temp != '') {
                $sql .= " AND ap.id IN($temp)";
            }
        }
        $sql .= 'ORDER BY c.date DESC';
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
            if($k == 'complaint_text' && $v == '<p>Пользовательский текст</p>'){
                $v = '<p>'.str_replace($v,'Пользовательский текст', '').'</p>';
            }
            if($k == 'complaint_text' && $v == '<p>Вам необходимо выбрать хотябы одну обязательную жалобу!</p>'){
                $v = '<p>'.str_replace($v,'Вам необходимо выбрать хотябы одну обязательную жалобу!', '').'</p>';
            }
            $this->$k = $v;
        }

    }

    public function findCountUserComplaints($user_id, $applicant_id = 'All')
    {
        $db = $this->getDi()->getShared('db');
        $sql = "SELECT COUNT(c.id) as num, c.status  FROM complaint as c
         LEFT JOIN applicant ap ON(c.applicant_id = ap.id  AND ap.is_blocked = 1)
         LEFT JOIN user u ON(ap.user_id = u.id )
         WHERE u.id = $user_id  ";
          //todo: do we really need LEFT JOIN if the filter on the last RIGHT table? It will return something ONLY if u.id is not NULL!
        if($applicant_id != 'All' && $applicant_id != ''){
            if($applicant_id[0] == ','){
                $applicant_id[0] = ' ';
            }
            $sql .= ' AND ap.id IN('.$applicant_id.') ';
        }
        $sql .= ' GROUP BY c.status ';
        $result = $db->query($sql);
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

    public function getComplaintMovingStatus($user_id) {
        $db = $this->getDi()->getShared('db');
        $result = $db->query("SELECT c.id AS complaint_id, c.auction_id AS auction_id FROM complaint c LEFT JOIN applicant a ON c.applicant_id = a.id WHERE a.user_id = {$user_id}");
        $ids = $result->fetchAll();
        $compl_ids = array();
        foreach ($ids as $id) {
            $compl_ids["{$id['complaint_id']}"] = $id['auction_id'];
        }
        return $compl_ids;
    }

    public function getCurrentStatusRussian($status, $short = TRUE) {
        switch ($status) {
            case 'draft':
                if ($short)
                    return 'Черновик';
                return '<span data-status="draft" class="jl-status jl-chernov">Черновик</span>';
            case 'activate':
                if ($short)
                    return 'Черновик';
                return '<span data-status="draft" class="jl-status jl-chernov">Черновик</span>';
            case 'justified':
                if ($short)
                    return 'Обоснована';
                return '<span data-status="justified" class="jl-status jl-done">Обоснована</span>';
            case 'unfounded':
                if ($short)
                    return 'Необоснована';
                return '<span data-status="unfounded" class="jl-status jl-notdone">Необоснована</span>';
            case 'under_consideration':
                if ($short)
                    return 'На рассмотрении';
                return '<span data-status="under_consideration" class="jl-status jl-rassmotr">На рассмотрении</span>';
            case 'submitted':
                if ($short)
                    return 'Подана';
                return '<span data-status="submitted" class="jl-status jl-podana">Подана</span>';
            case 'recalled':
                if ($short)
                    return 'Отозвана';
                return '<span data-status="recalled" class="jl-status jl-fail">Отозвана</span>';
            case 'archive':
                if ($short)
                    return 'Архив';
                return '<span data-status="archive" class="jl-status jl-archive">Архив</span>';
            default:
                return '';
        }
    }

    public function getComplaintColor($status){
        switch ($status) {
            case 'draft':
            case 'submitted':
            case 'recalled':
            case 'archive':
                return ' box-status-black ';
            case 'justified':
                return ' box-status-green ';
            case 'unfounded':
                return ' box-status-red ';
            case 'under_consideration':
                return ' box-status-blue ';
            default:
                return '';
        }
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
            $history_id = null;
            $complaint = Complaint::findFirstById($id);
            if(!$complaint || $complaint->status==$status) {
                continue;
            } elseif ($status == 'activate' ) {  //This return from arhive. We need to check history and set last status.
                $complainthistory = ComplaintMovingHistory::findFirst(array("complaint_id = :complaint_id:", "bind" => array("complaint_id" => $id), "order" => "date desc"));
                if($complainthistory){
                    $this->changeStatus($complainthistory->old_status, [$id]);
                    $history_id = $complainthistory->id;
                } else {
                    $this->changeStatus('draft', [$id]);
                    $complainthistory = ComplaintMovingHistory::findFirst(array("complaint_id = :complaint_id:", "bind" => array("complaint_id" => $id), "order" => "date desc"));
                    $history_id = $complainthistory->id;
                }
            } elseif ($status == 'delete') {
               // $stat = "удалена";
                ComplaintMovingHistory::delete_history($id);
                $complaint->delete();
            } elseif ($status == 'copy') {
               // $stat = "скопирована";
                $newComplaint = new Complaint();
                foreach($complaint as $k=>$v)
                    $newComplaint->$k = $v;
                $newComplaint->id = NULL;
                $newComplaint->complaint_name .= ' (Копия)';
                $newComplaint->status = 'draft';
                $newComplaint->fid = serialize(array());
                $newComplaint->date = date('Y-m-d H:i:s');
                $newComplaint->save();
                $arguments =  UsersArguments::find(array(
                    'complaint_id = '.$id
                ));
                foreach ($arguments as $key){
                    $arg = new UsersArguments();
                    $arg->argument_id = $key->argument_id;
                    $arg->argument_category_id = $key->argument_category_id;
                    $arg->text = $key->text;
                    $arg->complaint_id = $newComplaint->id;
                    $arg->argument_order = $key->argument_order;
                    $arg->save();
                }
                return $newComplaint->id;
            } elseif ($status == 'recalled' && $complaint->status == 'submitted'){
                $complaintmovinghistory = new ComplaintMovingHistory();
                $complaintmovinghistory->save(['complaint_id' => $id, 'old_status' => $complaint->status, 'new_status' => $status]);
                $complaint->status = 'recalled';
                $complaint->save();
                $history_id = $complaintmovinghistory->id;
            } elseif ($status == 'archive') {
                $complaintmovinghistory = new ComplaintMovingHistory();
                $complaintmovinghistory->save(['complaint_id' => $id, 'old_status' => $complaint->status, 'new_status' => $status]);
                $complaint->status = 'archive';
                $complaint->save();
                $history_id = $complaintmovinghistory->id;
            } else {
                $complaintmovinghistory = new ComplaintMovingHistory();
                $complaintmovinghistory->save(['complaint_id' => $id, 'old_status' => $complaint->status, 'new_status' => $status]);
                $complaint->status = $status;
                if($complaint->status == 'submitted'){
                    $complaint->date_submit = date('d.m.Y');
                }

                $complaint->save();
                $history_id = $complaintmovinghistory->id;
            }
            if($status != 'delete' && $status != 'copy') {
                if($status == 'justified') $stat = 'Обоснована';
                if($status == 'draft' || $status == 'activate') $stat = 'Черновик';
                if($status == 'unfounded') $stat = 'Необоснована';
                if($status == 'under_consideration') $stat = 'На рассмотрении';
                if($status == 'submitted') $stat = 'Подана';
                if($status == 'recalled') $stat = 'Отозвана';
                if($status == 'archive') $stat = 'Архив';
                //if($status == 'activate') $stat = 'Активирована';

                var_dump($user_id, $complaint->user_id);

                $message = new Messages();
                $message->to_uid = ($user_id != false) ? $user_id : $complaint->user_id;
                $message->subject = "Изменение статуса жалобы";
                $message->body = "Статус вашей жалобы на закупку №{$complaint->auction_id} был изменен на '{$stat}'";
                $message->time = date('Y-m-d H:i:s');
                $message->stat_comp = $status;
                $message->is_read = 0;
                $message->is_deleted = 0;
                $message->comp_id = $id;
                $message->history_id = $history_id;
                $message->save();
            }
        }


    }

    public function saveComplaint($data){
        //$this->type = $data['type'];
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