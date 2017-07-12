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
    public $conversion;
    public $phone;
    public $mobile_phone;
    public $activity;
    public $users_applicants;
    public $users_complaints;
    public $tarif_id;
    public $tarif_date_activate;
    public $tarif_count;
    public $tarif_active;

    public function initialize()
    {
        $this->setSource('user');
        $this->allowEmptyStringValues(['admin_comment', 'firstname', 'lastname', 'patronymic', 'phone', 'status', 'date_registration', 'activity']);
        $this->status = new RawValue('default');
        $this->date_registration = new RawValue('Now()');//todo: check when edit
        $this->activity = new RawValue('default');
    }

    public function afterFetch() {
        $complaints = new Complaint();
        $applicants = new Applicant();
		//$tarif = new Tarif();
		$this->user_tarif = Tarif::findFirstById($this->tarif_id);
		$this->user_tarif->tarif_name = mb_substr($this->user_tarif->tarif_name, 0, 1, mb_detect_encoding($this->user_tarif->tarif_name));
		if($this->user_tarif->tarif_type == "complaint"){
			$this->users_complaints_tarif = count($complaints->findUserComplaints($this->id, false, false, $this->tarif_date_activate));
			$this->users_complaints_av = $this->tarif_count - $this->users_complaints_tarif;
			if($this->users_complaints_av < 0) $this->users_complaints_av = 0;
			$this->sub_count = $this->users_complaints_av . " жалоб";
		}else{
			$this->sub_count = date("d.m.Y", strtotime($this->tarif_date_activate . " +".$this->tarif_count." months"));
		}
        $this->users_complaints = count($complaints->findUserComplaints($this->id, false));
        $this->users_applicants = count($applicants->findByUserIdWithAdditionalInfo($this->id));
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
	
	public function updateUser($data){
		
		if($data["id"]){
			$id = $data["id"];
			unset($data["id"]);
			$db = $this->getDi()->getShared('db');
			if(count($data)){
				$sql = "update user set ";
				$fields = array();
				foreach($data as $key=>$val){
					if($val){
						$fields[] = $key." = '".$val."'";
					}
				}
				
				if(count($fields)){
					$fields = implode(", ", $fields);
					$sql .= $fields." where id = ".$id;
					/*print_r("<pre>");
					print_r($sql);
					print_r("</pre>");*/
					return $db->query($sql);
				}
			}
		}
		return false;
	}
	
	public function saveActive(){
		if($this->id){
			$db = $this->getDi()->getShared('db');
			$sql = "update user set tarif_active = ".$this->tarif_active.", tarif_date_activate = '".date("Y-m-d H:i:s")."' where id = ".$this->id;
			$db->query($sql);
		}
	}
	public function saveTarif(){
		if($this->id){
			$db = $this->getDi()->getShared('db');
			$sql = "update user 
					set tarif_id = ".$this->tarif_id.", 
						tarif_count = ".$this->tarif_count.",
						tarif_date_activate = '".$this->tarif_date_activate."', 
						tarif_active = ".$this->tarif_active." 
					where id = ".$this->id;
			$db->query($sql);
		}
	}
}