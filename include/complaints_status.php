<?php
error_reporting(E_ERROR);

use Multiple\Frontend\Models\Complaint;
use Multiple\Frontend\Models\Applicant;
use Phalcon\Config\Adapter\Ini as ConfigIni;
defined('APP_PATH') || define('APP_PATH', realpath(dirname(__FILE__)));
require_once(APP_PATH.'/../vendor/autoload.php');

var_dump("Start ".date("Y-m-d H:i:s"));

$temp_conf = new ConfigIni(APP_PATH."/../apps/frontend/config/config.ini");

$mail = $temp_conf->mailer->toArray();
$adminsEmail = $temp_conf->adminsEmails->toArray();
var_dump($mail);
var_dump($adminsEmail);

include_once(dirname(__FILE__)."/lib/lib_abstract.php");

	$config = array();
	$config['driver'] = $mail['driver'];
	$config['host'] = $mail['host'];
	$config['port'] = $mail['port'];
	$config['encryption'] = $mail['encryption'];
	$config['username'] = $mail['username'];
	$config['password'] = $mail['password'];
	$config['from']['email'] = $mail['femail'];
	$config['from']['name'] = $mail['fname'];
	//$mailer = new \Phalcon\Ext\Mailer\Manager($config);
	
	/*$message = $mailer->createMessage()
            ->to("ddobrokhvalov@gmail.com")
            ->bcc('vadim-antropov@ukr.net')
            ->subject('Результат парсинга данных')
            ->content("Результат парсинга данных");
        $message->send();*/
		
	/*ini_set("SMTP", $mail['host']);
	ini_set("smtp_port", $mail['port']);
	ini_set("sendmail_from", $mail['femail']);
	
	$mailer = new vlibMimeMail();
	$mailer->to("ddobrokhvalov@gmail.com");
	$mailer->bcc("ddobrokhvalov@gmail.com");
	$mailer->from($config['from']['email'], $config['from']['name']);
	$mailer->subject("Результат парсинга данных");
	$mailer->htmlBody("<p>Результат парсинга данных</p><p>Результат парсинга данных</p><p>Результат парсинга данных</p>");
	$mailer->send();*/
	



$complaints_sql = "select comp.id, comp.complaint_name, comp.auction_id, comp.purchases_name, comp.date_submit, comp.applicant_id, comp.user_id, 
							ap.type, ap.name_full, ap.name_short, ap.inn, ap.fio_applicant, comp.status, ap.user_id as ap_user_id
					from complaint comp
					inner join applicant ap on ap.id = comp.applicant_id
					where (comp.status = 'submitted' OR comp.status = 'under_consideration') and comp.date_submit is not NULL";
$complaints = db::sql_select($complaints_sql);
//var_dump($complaints);

foreach($complaints as $comp){
	$zayavitel = trim($comp["name_short"]);
	$zayavitel = trim(preg_replace(array('/ООО|Общество с ограниченной ответственностью|Акционерное общество|ООО|ИП\s|АО\s/ui', '/[^а-яёa-z0-9 ]+/ui'), array('', ''), $zayavitel));
	$zayavitel = trim(preg_replace('/\s+/ui', ' ', $zayavitel));
	
	$imported_comp_sql = "select ic.id, ic.complaintNumber, ic.regNumber, ic.docNumber, ic.versionNumber, 
									ic.regDate, ic.createDate, ic.createUser, 
									ic.applicantType, ic.organizationName, ic.applicantNewfullName, ic.applicantNewcode, ic.applicantNewsingularName, 
									ic.purchaseNumber, ic.purchaseCode, ic.purchaseName,
									ich.id as ch_id, ich.versionNumber as ich_version, ich.complaintResult
							from imported_complaint ic
							inner join imported_checkresult ich on ich.complaintNumber = ic.complaintNumber and ich.purchaseNumber = ic.purchaseNumber
							where ic.purchaseNumber = :purchaseNumber
							order by ic.versionNumber asc, ich.versionNumber asc";
	$imported_comps = db::sql_select($imported_comp_sql, array("purchaseNumber"=>$comp["auction_id"]));
	if(count($imported_comps)){
		$imported_comps2 = array();
		foreach($imported_comps as $imported_comp){
			$lico = trim(preg_replace(array('/Общество с ограниченной ответственностью|Акционерное общество|ООО|ИП\s|АО\s/ui', '/[^а-яёa-z0-9 ]+/ui'), array('', ''), $imported_comp['applicantNewfullName']));
			$lico = trim(preg_replace('/\s+/ui', ' ', $lico));
			//var_dump($zayavitel);
			//var_dump($lico);
			if(mb_stristr($zayavitel, $lico, false, "utf-8")){
				$date_submit = date("Y-m-d H:i:s", strtotime($comp["date_submit"]));
				$date_submit_plus3 = date("Y-m-d H:i:s", strtotime($comp["date_submit"]." + 5 days"));
				$regdate = date("Y-m-d H:i:s", strtotime($imported_comp["regDate"]));
				/*var_dump($date_submit);
				var_dump($date_submit_plus3);
				var_dump($regdate);*/
				if($regdate >= $date_submit && $regdate <= $date_submit_plus3){
					$imported_comps2[] = $imported_comp;
				}
			}
		}
		if(count($imported_comps2)){
			var_dump($comp);
			var_dump($imported_comps2);
			foreach($imported_comps2 as $imported_comp2){
				if($imported_comp2["complaintResult"] == "COMPLAINT_VIOLATIONS" || $imported_comp2["complaintResult"] == "COMPLAINT_PARTLY_VALID"){
					$sql_update_status = "update complaint set status = 'justified' where id = ".$comp["id"];
					$arr_moving_history = array("complaint_id"=>$comp["id"], "old_status"=>$comp["status"], "new_status"=>"justified");
					$arr_message = array("to_uid"=>$comp["user_id"]?$comp["user_id"]:$comp["ap_user_id"], 
											"subject"=>"Изменение статуса жалобы", 
											"body"=>"Статус вашей жалобы на закупку №".$comp["auction_id"]." был изменен на 'Обоснована'",
											"time"=>date('Y-m-d H:i:s'),
											"stat_comp"=>"justified",
											"is_read"=>0,
											"is_deleted"=>0,
											"comp_id"=>$comp["id"],
											"history_id"=>0);
				}
				if($imported_comp2["complaintResult"] == "COMPLAINT_NO_VIOLATIONS"){
					$sql_update_status = "update complaint set status = 'unfounded' where id = ".$comp["id"];
					$arr_moving_history = array("complaint_id"=>$comp["id"], "old_status"=>$comp["status"], "new_status"=>"unfounded");
					$arr_message = array("to_uid"=>$comp["user_id"]?$comp["user_id"]:$comp["ap_user_id"], 
											"subject"=>"Изменение статуса жалобы", 
											"body"=>"Статус вашей жалобы на закупку №".$comp["auction_id"]." был изменен на 'Необоснована'",
											"time"=>date('Y-m-d H:i:s'),
											"stat_comp"=>"unfounded",
											"is_read"=>0,
											"is_deleted"=>0,
											"comp_id"=>$comp["id"],
											"history_id"=>0);
				}
				db::sql_query($sql_update_status);
				db::insert_record("complaint_moving_history", $arr_moving_history);
				$history_id = db::last_insert_id("complaint_moving_history");
				$arr_message["history_id"] = $history_id;
				db::insert_record("messages", $arr_message);
			}
		}
	}
}