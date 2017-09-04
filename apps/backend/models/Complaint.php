<?php
namespace Multiple\Backend\Models;

use Phalcon\Exception;
use Phalcon\Mvc\Model;
use Multiple\Backend\Models\Question;
use Multiple\Backend\Models\ComplaintMovingHistory;
use Multiple\Backend\Models\Messages;

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
    public $fid;
    public $date_submit;
    public $user_id;
	public $deleted;
	public $incoming_number;

    public function initialize()
    {
        $this->setSource('complaint');
        $this->hasMany('id', 'Multiple\Backend\Models\Question', 'complaint_id', array('alias' => 'Question'));
        $this->hasOne('applicant_id', 'Multiple\Backend\Models\Applicant', 'id', array('alias' => 'Applicant'));
    }

    public function getSource()
    {
        return 'complaint';
    }

    public function getComplaintQuestion($not_read = array('y', 'n')){
        return Question::find(array(
            "complaint_id = :complaint_id: AND is_read IN ({read:array})",
            'bind' => array(
                'complaint_id' => $this->id,
                'read' => $not_read,
            ),
            "order" => "id DESC",
        ));
    }
    
    public function getComplaintQuestionAnswer($question_id){
        $answers = Answer::find(array(
            "question_id = :question_id:",
            'bind' => array(
                'question_id' => $question_id,
            ),
        ));
        return $answers;
    }

    public function getAnswerOwner($admin_id){
        $db_object = Admin::find(array(
            "id = :id:",
            'bind' => array(
                'id' => $admin_id,
            ),
        ))->toArray();
        $answer_owner = array();
        $answer_owner['photo'] = $db_object[0]['avatar'];
        $answer_owner['user'] = "{$db_object[0]['name']} {$db_object[0]['surname']}";
        return $answer_owner;
    }
    
    public function getCountQuestions($question, $only_count = FALSE) {
        $rows = $question->toArray();
        if ($only_count) {
            return count($rows);
        }
        return count($rows) ? '<span class="has-questions"></span>' : '<span class="no-questions"></span>';
    }

    public function getComplaintOwner($id) {
        $db = $this->getDi()->getShared('db');
        $result = $db->query("SELECT a.user_id FROM complaint c LEFT JOIN applicant a ON c.applicant_id = a.id WHERE c.id = {$id}");
        $result = $result->fetch();
        if ($result) {
            return $result['user_id'];
        }
        return false;
    }

    public function findUserComplaints($user_id, $status, $applicant_id = false, $from_date = false)
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
		if($from_date){
			$sql .= " AND c.date >= '$from_date'";
		}

        $sql .= ' ORDER BY c.date DESC';
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

    public function getComplaintStatus($status, $short = FALSE){
        switch ($status) {
            case 'draft':
                if ($short)
                    return '<span data-status="draft" class="jl-status jl-status-short jl-chernov"></span>';
                return '<span data-status="draft" class="jl-status jl-chernov">Черновик</span>';
            case 'justified':
                if ($short)
                    return '<span data-status="justified" class="jl-status jl-status-short jl-done"></span>';
                return '<span data-status="justified" class="jl-status jl-done">Обоснована</span>';
            case 'unfounded':
                if ($short)
                    return '<span data-status="unfounded" class="jl-status jl-status-short jl-notdone"></span>';
                return '<span data-status="unfounded" class="jl-status jl-notdone">Необоснована</span>';
            case 'under_consideration':
                if ($short)
                    return '<span data-status="under_consideration" class="jl-status jl-status-short jl-rassmotr"></span>';
                return '<span data-status="under_consideration" class="jl-status jl-rassmotr">На рассмотрении</span>';
            case 'submitted':
                if ($short)
                    return '<span data-status="submitted" class="jl-status jl-status-short jl-podana"></span>';
                return '<span data-status="submitted" class="jl-status jl-podana">Подана</span>';
            case 'recalled':
                if ($short)
                    return '<span data-status="recalled" class="jl-status jl-status-short jl-fail"></span>';
                return '<span data-status="recalled" class="jl-status jl-fail">Отозвана</span>';
			case 'returned':
                if ($short)
                    return '<span data-status="returned" class="jl-status jl-status-short jl-fail"></span>';
                return '<span data-status="returned" class="jl-status jl-fail">Возвращена</span>';
            case 'archive':
                if ($short)
                    return '<span data-status="archive" class="jl-status jl-status-short jl-archive"></span>';
                return '<span data-status="archive" class="jl-status jl-archive">Архив</span>';
            default:
                return '';
        }
    }
    
    public function getAllStatuses($index){
        $statuses = array(
            '0' => 'draft',
            '1' => 'justified',
            '2' => 'unfounded',
            '3' => 'under_consideration',
            '4' => 'submitted',
            '5' => 'recalled',
            '6' => 'archive',
			'7' => 'returned',
        );
        return $this->getComplaintStatus($statuses[$index]);
    }

    public function getComplaintColor($status){
        switch ($status) {
            case 'draft':
            case 'submitted':
            case 'recalled':
            case 'archive':
                return ' status-black ';
            case 'justified':
                return ' status-green ';
            case 'unfounded':
                return ' status-red ';
            case 'under_consideration':
                return ' status-blue ';
            default:
                return '';
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
        foreach ($data as $id) {
            $history_id = null;
            $message_text = '';
            $complaint = Complaint::findFirstById($id);
            if(!$complaint || $complaint->status==$status) {
                continue;
            } elseif ($status == 'activate' ) {  //This return from arhive. We need to check history and set last status.
                $complainthistory = ComplaintMovingHistory::findFirst(array("complaint_id = :complaint_id:", "bind" => array("complaint_id" => $id), "order" => "date desc"));
                if($complainthistory) {
                    $test = new Complaint();
                    $test->changeStatus($complainthistory->old_status, array($id));
                } else {
                    $test = new Complaint();
                    $test->changeStatus('draft', array($id));
                }
            } elseif ($status == 'delete') {
                ComplaintMovingHistory::delete_history($id);
                $complaint->delete();
            } elseif ($status == 'copy') {
                $newComplaint = new Complaint();
                foreach($complaint as $k=>$v)
                    $newComplaint->$k = $v;
                $newComplaint->id = NULL;
                $newComplaint->complaint_name .= ' (Копия)';
                $newComplaint->status = 'draft';
                $newComplaint->fid = serialize(array());
                $newComplaint->save();
				
				//$user_arg = new UsersArguments();
				//$user_arg->copyUsersArguments($id, $newComplaint->id);
				
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
                $complaint->save();
                $history_id = $complaintmovinghistory->id;
            }
            if($status != 'delete' && $status != 'copy') {
                if($status == 'justified'){
                    $stat = 'Обоснована';
                    $message_text .= 'обоснована';
                }
                if($status == 'draft' || $status == 'activate'){
                    $stat = 'Черновик';
                    $message_text .= 'помещена в черновик';
                }
                if($status == 'unfounded'){
                    $stat = 'Необоснована';
                    $message_text .= 'необоснована';
                }
                if($status == 'under_consideration'){
                    $stat = 'На рассмотрении';
                    $message_text .= 'помещена на рассмотрение';
                }
                if($status == 'submitted'){
                    $stat = 'Подана';
                    $message_text .= 'подана';
                }
                if($status == 'recalled'){
                    $stat = 'Отозвана';
                    $message_text .= 'отозвана';
                }
                if($status == 'archive'){
                    $stat = 'Архив';
                    $message_text .= 'помещена в архив';
                }

                $comp = new Complaint();
                $user_id = $comp->getComplaintOwner( $complaint->id );
				if($user_id){
					$message = new Messages();
					$message->to_uid = $user_id;
					$message->subject = "Изменение статуса жалобы";
					$message->body = "Статус вашей жалобы был изменен на '{$stat}' администратором.";
					$message->time = date('Y-m-d H:i:s');
					$message->is_read = 0;
					$message->is_deleted = 0;
					$message->stat_comp = $status;
					$message->comp_id = $id;
					$message->history_id = $history_id;
					$message->save();

					$user = User::findFirst(array("id={$user_id}"));
					if($user && $user->notifications == 1) {
						$message = $this->mailer->createMessageFromView('../views/emails/status', array(
							'status' => $message_text,
							'host' => $this->request->getHttpHost(),
							'conversion' => $user->conversion,
							'auction_id' => $complaint->auction_id,
							'comp_id' => $complaint->id,
							'comp_name' => $complaint->complaint_name
						))
							->to($user->email)
							->subject('Изменение статуса жалобы в системе ФАС-Онлайн');
						$message->send();
					}
				}
            }
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
	
	public function getUserTarif($user_id){
		$db = $this->getDi()->getShared('db');
	}
	
	public function getTarifs($priced = false){
		$db = $this->getDi()->getShared('db');
		$sql = "select * from tarifs";
		if($priced){
			$sql .= " where tarif_price > 0";
		}
		$result = $db->query($sql);
         return $result->fetchAll();
	}
	
	public function findImportedResult(){
		$db = $this->getDi()->getShared('db');
		
		$applicant = Applicant::findFirstById($this->applicant_id);
		
		$zayavitel = trim($applicant->name_short);
		$zayavitel = trim(preg_replace(array('/ООО|Общество с ограниченной ответственностью|Акционерное общество|ООО|ИП\s|АО\s/ui', '/[^а-яёa-z0-9 ]+/ui'), array('', ''), $zayavitel));
		$zayavitel = trim(preg_replace('/\s+/ui', ' ', $zayavitel));
		
		$imported_comp_sql = "select ic.id, ic.complaintNumber, ic.regNumber, ic.docNumber, ic.versionNumber, 
									ic.regDate, ic.createDate, ic.createUser, ic.planDecisionDate, ic.noticenumber, ic.noticeacceptDate, 
									ic.decisionPlace, ic.considerationKOfullName, 
									ic.applicantType, ic.organizationName, ic.applicantNewfullName, ic.applicantNewcode, ic.applicantNewsingularName, 
									ic.purchaseNumber, ic.purchaseCode, ic.purchaseName, ic.returnInfobase, ic.returnInfodecision, 
									ich.id as ch_id, ich.versionNumber as ich_version, ich.complaintResult, 
									ic.attachments, ich.decisionattachments, ic.printFormurl, 
									icc.id as icc_id, icc.complaintNumber as icc_complaintNumber, icc.registrationKOfullName as icc_registrationKOfullName, icc.regDate as icc_regDate, icc.printFormurl as icc_printFormurl, icc.attachments as icc_attachments
							from imported_complaint ic
							left join imported_checkresult ich on ich.complaintNumber = ic.complaintNumber and ich.purchaseNumber = ic.purchaseNumber and ich.decisionDate > ic.regDate
							left join imported_complaint_cancel icc on icc.complaintNumber = ic.complaintNumber 
																		and icc.registrationKOfullName = ic.registrationKOfullName 
																		and icc.regDate = ic.regDate
																		and icc.attachments = ic.attachments
							where ic.purchaseNumber = '".trim($this->auction_id)."'
							order by ic.versionNumber asc, ich.versionNumber asc";
		$imported_comps = $db->query($imported_comp_sql);
		$imported_comps = $imported_comps->fetchAll();
		
		$imported_comps2 = array();
		foreach($imported_comps as $imported_comp){
			$lico = trim(preg_replace(array('/Общество с ограниченной ответственностью|Акционерное общество|ООО|ИП\s|АО\s/ui', '/[^а-яёa-z0-9 ]+/ui'), array('', ''), $imported_comp['applicantNewfullName']));
			$lico = trim(preg_replace('/\s+/ui', ' ', $lico));
			if($applicant->type == "ip" || $applicant->type == "fizlico"){
				$zayavitel_arr = explode(" ", $zayavitel);
				$lico_arr = explode(" ", $lico);
				
				if(count($lico_arr) == 3){
					$lico_arr[1] = mb_substr($lico_arr[1], 0, 1, "utf-8");
					$lico_arr[2] = mb_substr($lico_arr[2], 0, 1, "utf-8");
				}
				if(count($lico_arr) == 2){
					$lico_arr[2] = mb_substr($lico_arr[1], 1, 1, "utf-8");
					$lico_arr[1] = mb_substr($lico_arr[1], 0, 1, "utf-8");
				}
				
				if(mb_stristr($zayavitel, $lico, false, "utf-8") || 
					(count($zayavitel_arr) == 3 && count($lico_arr) == 3 
						&& mb_stristr($zayavitel_arr[0], $lico_arr[0], false, "utf-8") && mb_stristr($zayavitel_arr[1], $lico_arr[1], false, "utf-8") && mb_stristr($zayavitel_arr[2], $lico_arr[2], false, "utf-8")
					)
				){
					$date_submit = date("Y-m-d H:i:s", strtotime($this->date_submit));
					$date_submit_plus3 = date("Y-m-d H:i:s", strtotime($this->date_submit." + 5 days"));
					$regdate = date("Y-m-d H:i:s", strtotime($imported_comp["regDate"]));
					$imported_comp["zayavitel"] = $zayavitel;
					$imported_comp["lico"] = $lico;
					$imported_comp["zayavitel_arr"] = $zayavitel_arr;
					$imported_comp["lico_arr"] = $lico_arr;
					$imported_comp["date_submit"] = $date_submit;
					$imported_comp["date_submit_plus3"] = $date_submit_plus3;
					$imported_comp["regdate"] = $regdate;
					if($regdate >= $date_submit && $regdate <= $date_submit_plus3){
						$imported_comps2[] = $imported_comp;
					}
					
				}
			}else{
				if(mb_stristr($zayavitel, $lico, false, "utf-8")){
					$date_submit = date("Y-m-d H:i:s", strtotime($this->date_submit));
					$date_submit_plus3 = date("Y-m-d H:i:s", strtotime($this->date_submit." + 5 days"));
					$regdate = date("Y-m-d H:i:s", strtotime($imported_comp["regDate"]));
					$imported_comp["zayavitel"] = $zayavitel;
					$imported_comp["lico"] = $lico;
					$imported_comp["date_submit"] = $date_submit;
					$imported_comp["date_submit_plus3"] = $date_submit_plus3;
					$imported_comp["regdate"] = $regdate;
					if($regdate >= $date_submit && $regdate <= $date_submit_plus3){
						$imported_comps2[] = $imported_comp;
					}
					
				}
			}
		}
		return $imported_comps2;
	}

}