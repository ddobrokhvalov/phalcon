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
//var_dump($mail);
//var_dump($adminsEmail);

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
	

$statuses = array("submitted"=>"Подана", "under_consideration"=>"На рассмотрении");

$complaints_sql = "select comp.id, comp.complaint_name, comp.auction_id, comp.purchases_name, comp.date_submit, comp.applicant_id, comp.user_id, 
							ap.type, ap.name_full, ap.name_short, ap.inn, ap.fio_applicant, comp.status, ap.user_id as ap_user_id
					from complaint comp
					inner join applicant ap on ap.id = comp.applicant_id
					where (comp.status = 'submitted') and comp.date_submit is not NULL";
$complaints = db::sql_select($complaints_sql);
//var_dump($complaints);

$success_text = '<strong>Успешные изменения статусов:</strong><br/>';

foreach($complaints as $comp){
	$zayavitel = trim($comp["name_short"]);
	$zayavitel = trim(preg_replace(array('/ООО|Общество с ограниченной ответственностью|Акционерное общество|ООО|ИП\s|АО\s/ui', '/[^а-яёa-z0-9 ]+/ui'), array('', ''), $zayavitel));
	$zayavitel = trim(preg_replace('/\s+/ui', ' ', $zayavitel));
	
	$imported_comp_sql = "select ic.id, ic.complaintNumber, ic.regNumber, ic.docNumber, ic.versionNumber, 
									ic.regDate, ic.createDate, ic.createUser, 
									ic.applicantType, ic.organizationName, ic.applicantNewfullName, ic.applicantNewcode, ic.applicantNewsingularName, 
									ic.purchaseNumber, ic.purchaseCode, ic.purchaseName, ic.returnInfobase, ic.returnInfodecision,
									ich.id as ch_id, ich.versionNumber as ich_version, ich.complaintResult, ic.planDecisionDate, ic.noticenumber, ic.noticeacceptDate
							from imported_complaint ic
							left join imported_checkresult ich on ich.complaintNumber = ic.complaintNumber and ich.purchaseNumber = ic.purchaseNumber
							where ic.purchaseNumber = :purchaseNumber
							order by ic.versionNumber asc, ich.versionNumber asc";
	$imported_comps = db::sql_select($imported_comp_sql, array("purchaseNumber"=>trim($comp["auction_id"])));
	$imported_comps2 = array();
	if(count($imported_comps)){
		foreach($imported_comps as $imported_comp){
			$lico = trim(preg_replace(array('/Общество с ограниченной ответственностью|Акционерное общество|ООО|ИП\s|АО\s/ui', '/[^а-яёa-z0-9 ]+/ui'), array('', ''), $imported_comp['applicantNewfullName']));
			$lico = trim(preg_replace('/\s+/ui', ' ', $lico));
			/*var_dump($comp["id"]);
			var_dump($comp["type"]);
			var_dump($imported_comp["id"]);
			var_dump($imported_comp["ch_id"]);
			var_dump($zayavitel);
			var_dump($lico);*/
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
			//var_dump($zayavitel_arr);
			//var_dump($lico_arr);
			if($comp["type"] == "ip" || $comp["type"] == "fizlico"){
				if(mb_stristr($zayavitel, $lico, false, "utf-8") || 
					(
						count($zayavitel_arr) == 3 && count($lico_arr) == 3 
						&& mb_stristr($zayavitel_arr[0], $lico_arr[0], false, "utf-8") && mb_stristr($zayavitel_arr[1], $lico_arr[1], false, "utf-8") && mb_stristr($zayavitel_arr[2], $lico_arr[2], false, "utf-8")
					)
				){
					$date_submit = date("Y-m-d H:i:s", strtotime($comp["date_submit"]));
					$date_submit_plus3 = date("Y-m-d H:i:s", strtotime($comp["date_submit"]." + 5 days"));
					$regdate = date("Y-m-d H:i:s", strtotime($imported_comp["regDate"]));
					//var_dump($date_submit);
					//var_dump($date_submit_plus3);
					//var_dump($regdate);
					if($regdate >= $date_submit && $regdate <= $date_submit_plus3 && !$imported_comp["ch_id"] && !$imported_comp["ich_version"] && !$imported_comp["complaintResult"] && !$imported_comp["returnInfobase"]){
						$imported_comps2[] = array("complaintNumber"=>$imported_comp["complaintNumber"], 
													"regDate"=>$imported_comp["regDate"],
													"applicantNewfullName"=>$imported_comp["applicantNewfullName"],
													"purchaseNumber"=>$imported_comp["purchaseNumber"],
													"planDecisionDate"=>$imported_comp["planDecisionDate"],
													"noticenumber"=>$imported_comp["noticenumber"],
													"noticeacceptDate"=>$imported_comp["noticeacceptDate"]);
					}
				}
			}else{
				if(mb_stristr($zayavitel, $lico, false, "utf-8")){
					$date_submit = date("Y-m-d H:i:s", strtotime($comp["date_submit"]));
					$date_submit_plus3 = date("Y-m-d H:i:s", strtotime($comp["date_submit"]." + 5 days"));
					$regdate = date("Y-m-d H:i:s", strtotime($imported_comp["regDate"]));
					/*var_dump($date_submit);
					var_dump($date_submit_plus3);
					var_dump($regdate);*/
					if($regdate >= $date_submit && $regdate <= $date_submit_plus3 && !$imported_comp["ch_id"] && !$imported_comp["ich_version"] && !$imported_comp["complaintResult"] && !$imported_comp["returnInfobase"]){
						$imported_comps2[] = array("complaintNumber"=>$imported_comp["complaintNumber"], 
													"regDate"=>$imported_comp["regDate"],
													"applicantNewfullName"=>$imported_comp["applicantNewfullName"],
													"purchaseNumber"=>$imported_comp["purchaseNumber"],
													"planDecisionDate"=>$imported_comp["planDecisionDate"],
													"noticenumber"=>$imported_comp["noticenumber"],
													"noticeacceptDate"=>$imported_comp["noticeacceptDate"]);
					}
				}
			}
		}
	}
	
	if(count($imported_comps2)){
		var_dump($comp["id"]);
		var_dump($imported_comps2);
		$new_status = false;
		foreach($imported_comps2 as $imported_comp2){
			
				$sql_update_status = "update complaint set status = 'under_consideration' where id = ".$comp["id"];
				$arr_moving_history = array("complaint_id"=>$comp["id"], "old_status"=>$comp["status"], "new_status"=>"under_consideration");
				$arr_message = array("to_uid"=>$comp["user_id"]?$comp["user_id"]:$comp["ap_user_id"], 
										"subject"=>"Изменение статуса жалобы", 
										"body"=>"Статус вашей жалобы на закупку №".$comp["auction_id"]." был изменен на 'На рассмотрении'",
										"time"=>date('Y-m-d H:i:s'),
										"stat_comp"=>"justified",
										"is_read"=>0,
										"is_deleted"=>0,
										"comp_id"=>$comp["id"],
										"history_id"=>0);
				$new_status = "На рассмотрении";
			
			
			db::sql_query($sql_update_status);
			db::insert_record("complaint_moving_history", $arr_moving_history);
			$history_id = db::last_insert_id("complaint_moving_history");
			$arr_message["history_id"] = $history_id;
			db::insert_record("messages", $arr_message);
		}
		
		if($new_status){
			$success_text .= '**Жалоба принята к рассмотрению: ' . "<br/>";
			$success_text .= ' | ID жалобы: ' . $comp['id'] . "<br/>";
			$success_text .= ' | Название жалобы: ' . $comp['complaint_name'] . "<br/>";
			$success_text .= ' | Номер извещения жалобы: ' . $comp['auction_id'] . "<br/>";
			$success_text .= ' | Имя заявителя: ' . $comp['name_short'] . "<br/>";
			$success_text .= ' | Дата подачи жалобы: ' . $comp['date_submit'] . "<br/>";
			$success_text .= ' | Ссылка: <a href="http://fas-online.ru/complaint/edit/'.$comp['id'].'">Перейти к жалобе</a>';
			$success_text .= ' | Время работы парсера: ' . date('Y-m-d H:i:s') . "<br/>";
			$success_text .= '<br/>';
			$success_text .= '<br/>';
			$success_text .= '---------------------------------<br/>';
		}else{
			//$success_text .= 'Cтатус жалобы не изменен: ' . $statuses[$comp["status"]] . "<br/>";
		}
		
	}else{
		//$success_text .= 'Cтатус жалобы не изменен: ' . $statuses[$comp["status"]] . "<br/>";
	}
	
	
	
}

$vlibMimeMail = new vlibMimeMail();
$vlibMimeMail->to("office@gos-partner.ru");
$vlibMimeMail->cc($adminsEmail["ufas"]);
$vlibMimeMail->cc("info@fasonline.ru");
$vlibMimeMail->bcc("ddobrokhvalov@gmail.com");
$vlibMimeMail->from($config['from']['email'], $config['from']['name']);
$vlibMimeMail->subject("Результат парсинга данных");
$vlibMimeMail->htmlBody($success_text);
$vlibMimeMail->send();