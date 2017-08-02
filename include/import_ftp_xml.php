<?php
error_reporting(E_ERROR);
var_dump("Start ".date("Y-m-d H:i:s"));

include_once(dirname(__FILE__)."/lib/lib_abstract.php");

//exit;

$host = "ftp.zakupki.gov.ru";
$connect = ftp_connect($host);
$login_result = ftp_login($connect, "free", "free");
ftp_chdir($connect, "/fcs_fas/complaint/currMonth/");
var_dump("Текущая директория: " . ftp_pwd($connect));
$contents = ftp_nlist($connect, ".");
//var_dump($contents);
$self_file_path = realpath(dirname(__FILE__)."/../imported_ftp_files/");
//var_dump($self_file_path);
foreach($contents as $ftp_file){
	if(!file_exists($self_file_path."/".$ftp_file) && strstr($ftp_file, "complaint") && strstr($ftp_file, ".zip")){
		$local_file = $self_file_path."/".$ftp_file;
		ftp_get($connect, $local_file, $ftp_file, FTP_BINARY);
		var_dump($local_file);
		$zip = new ZipArchive();
		//var_dump($zip);
		$zip->open($local_file);
		$zip->extractTo($self_file_path."/unpacked/");
		foreach (glob($self_file_path."/unpacked/*.xml") as $filename) {
			
			if(strstr($filename, "complaint_")){
			
				var_dump( "$filename size " . filesize($filename));
				$processed_file = db::sql_select("select * from imported_ftp_files where zipname = :zipname and filename = :filename", array("zipname"=>$ftp_file, "filename"=>basename($filename)));
				if($processed_file && count($processed_file)){
					unlink($filename);
					continue;
				}else{
					db::insert_record("imported_ftp_files", array("zipname"=>$ftp_file, "filename"=>basename($filename)));
				}
			
				$file_data = file_get_contents($filename);
				$file_data = str_replace("oos:", "", $file_data);
				$file_data = str_replace("ns2:", "", $file_data);
				$file_data = simplexml_load_string($file_data);
				//var_dump($file_data);
				$fields = array();
				
				if($file_data->complaint->commonInfo->complaintNumber) $fields["complaintNumber"] = $file_data->complaint->commonInfo->complaintNumber->__toString();
				if($file_data->complaint->commonInfo->regNumber) $fields["regNumber"] = $file_data->complaint->commonInfo->regNumber->__toString();
				if($file_data->complaint->commonInfo->docNumber) $fields["docNumber"] = $file_data->complaint->commonInfo->docNumber->__toString();
				if($file_data->complaint->commonInfo->versionNumber) $fields["versionNumber"] = $file_data->complaint->commonInfo->versionNumber->__toString();
				if($file_data->complaint->commonInfo->planDecisionDate) $fields["planDecisionDate"] = $file_data->complaint->commonInfo->planDecisionDate->__toString();
				if($file_data->complaint->commonInfo->decisionPlace) $fields["decisionPlace"] = $file_data->complaint->commonInfo->decisionPlace->__toString();
				
				if($file_data->complaint->commonInfo->registrationKO->regNum) $fields["registrationKOregNum"] = $file_data->complaint->commonInfo->registrationKO->regNum->__toString();
				if($file_data->complaint->commonInfo->registrationKO->fullName) $fields["registrationKOfullName"] = $file_data->complaint->commonInfo->registrationKO->fullName->__toString();
				if($file_data->complaint->commonInfo->registrationKO->INN) $fields["registrationKOINN"] = $file_data->complaint->commonInfo->registrationKO->INN->__toString();
				if($file_data->complaint->commonInfo->registrationKO->KPP) $fields["registrationKOKPP"] = $file_data->complaint->commonInfo->registrationKO->KPP->__toString();
				
				if($file_data->complaint->commonInfo->considerationKO->regNum) $fields["considerationKOregNum"] = $file_data->complaint->commonInfo->considerationKO->regNum->__toString();
				if($file_data->complaint->commonInfo->considerationKO->fullName) $fields["considerationKOfullName"] = $file_data->complaint->commonInfo->considerationKO->fullName->__toString();
				if($file_data->complaint->commonInfo->considerationKO->INN) $fields["considerationKOINN"] = $file_data->complaint->commonInfo->considerationKO->INN->__toString();
				if($file_data->complaint->commonInfo->considerationKO->KPP) $fields["considerationKOKPP"] = $file_data->complaint->commonInfo->considerationKO->KPP->__toString();
				
				if($file_data->complaint->commonInfo->regType) $fields["regType"] = $file_data->complaint->commonInfo->regType->__toString();
				if($file_data->complaint->commonInfo->regDate) $fields["regDate"] = $file_data->complaint->commonInfo->regDate->__toString();
				
				if($file_data->complaint->commonInfo->notice->number) $fields["noticenumber"] = $file_data->complaint->commonInfo->notice->number->__toString();
				if($file_data->complaint->commonInfo->notice->acceptDate) $fields["noticeacceptDate"] = $file_data->complaint->commonInfo->notice->acceptDate->__toString();
				if($file_data->complaint->commonInfo->notice->info) $fields["noticeinfo"] = $file_data->complaint->commonInfo->notice->info->__toString();
				
				if($file_data->complaint->commonInfo->contactPhone) $fields["contactPhone"] = $file_data->complaint->commonInfo->contactPhone->__toString();
				
				if($file_data->complaint->commonInfo->modification->info) $fields["modification"] = $file_data->complaint->commonInfo->modification->info->__toString();
				
				if($file_data->complaint->commonInfo->printFormInfo->createDate) $fields["createDate"] = $file_data->complaint->commonInfo->printFormInfo->createDate->__toString();
				if($file_data->complaint->commonInfo->printFormInfo->createOrganization->regNum) $fields["createOrganizationregNum"] = $file_data->complaint->commonInfo->printFormInfo->createOrganization->regNum->__toString();
				if($file_data->complaint->commonInfo->printFormInfo->createOrganization->fullName) $fields["createOrganizationfullName"] = $file_data->complaint->commonInfo->printFormInfo->createOrganization->fullName->__toString();
				if($file_data->complaint->commonInfo->printFormInfo->createOrganization->INN) $fields["createOrganizationINN"] = $file_data->complaint->commonInfo->printFormInfo->createOrganization->INN->__toString();
				if($file_data->complaint->commonInfo->printFormInfo->createOrganization->KPP) $fields["createOrganizationKPP"] = $file_data->complaint->commonInfo->printFormInfo->createOrganization->KPP->__toString();
				if($file_data->complaint->commonInfo->printFormInfo->createUser) $fields["createUser"] = $file_data->complaint->commonInfo->printFormInfo->createUser->__toString();
				
				if($file_data->complaint->commonInfo->printFormInfo->publishDate) $fields["publishDate"] = $file_data->complaint->commonInfo->printFormInfo->publishDate->__toString();
				if($file_data->complaint->commonInfo->printFormInfo->publishOrganization->regNum) $fields["publishOrganizationregNum"] = $file_data->complaint->commonInfo->printFormInfo->publishOrganization->regNum->__toString();
				if($file_data->complaint->commonInfo->printFormInfo->publishOrganization->fullName) $fields["publishOrganizationfullName"] = $file_data->complaint->commonInfo->printFormInfo->publishOrganization->fullName->__toString();
				if($file_data->complaint->commonInfo->printFormInfo->publishOrganization->INN) $fields["publishOrganizationINN"] = $file_data->complaint->commonInfo->printFormInfo->publishOrganization->INN->__toString();
				if($file_data->complaint->commonInfo->printFormInfo->publishOrganization->KPP) $fields["publishOrganizationKPP"] = $file_data->complaint->commonInfo->printFormInfo->publishOrganization->KPP->__toString();
				if($file_data->complaint->commonInfo->printFormInfo->publishUser) $fields["publishUser"] = $file_data->complaint->commonInfo->printFormInfo->publishUser->__toString();
				
				if($file_data->complaint->indicted->authority->regNum) $fields["indictedauthorityregNum"] = $file_data->complaint->indicted->authority->regNum->__toString();
				if($file_data->complaint->indicted->authority->fullName) $fields["indictedauthorityfullName"] = $file_data->complaint->indicted->authority->fullName->__toString();
				if($file_data->complaint->indicted->authority->INN) $fields["indictedauthorityINN"] = $file_data->complaint->indicted->authority->INN->__toString();
				if($file_data->complaint->indicted->authority->KPP) $fields["indictedauthorityKPP"] = $file_data->complaint->indicted->authority->KPP->__toString();
				
				if($file_data->complaint->indicted->customer->regNum) $fields["indictedcustomerregNum"] = $file_data->complaint->indicted->customer->regNum->__toString();
				if($file_data->complaint->indicted->customer->fullName) $fields["indictedcustomerfullName"] = $file_data->complaint->indicted->customer->fullName->__toString();
				if($file_data->complaint->indicted->customer->INN) $fields["indictedcustomerINN"] = $file_data->complaint->indicted->customer->INN->__toString();
				if($file_data->complaint->indicted->customer->KPP) $fields["indictedcustomerKPP"] = $file_data->complaint->indicted->customer->KPP->__toString();
				
				if($file_data->complaint->applicantNew->legalEntity){
					if($file_data->complaint->applicantNew->legalEntity->fullName) $fields["applicantNewfullName"] = $file_data->complaint->applicantNew->legalEntity->fullName->__toString();
					if($file_data->complaint->applicantNew->legalEntity->legalForm->code) $fields["applicantNewcode"] = $file_data->complaint->applicantNew->legalEntity->legalForm->code->__toString();
					if($file_data->complaint->applicantNew->legalEntity->legalForm->singularName) $fields["applicantNewsingularName"] = $file_data->complaint->applicantNew->legalEntity->legalForm->singularName->__toString();
				}
				if($file_data->complaint->applicantNew->individualPerson){
					if($file_data->complaint->applicantNew->individualPerson->name) $fields["applicantNewfullName"] = $file_data->complaint->applicantNew->individualPerson->name->__toString();
					if($file_data->complaint->applicantNew->individualPerson->name) $fields["applicantNewsingularName"] = $file_data->complaint->applicantNew->individualPerson->name->__toString();
				}
				if($file_data->complaint->applicantNew->individualBusinessman){
					if($file_data->complaint->applicantNew->individualBusinessman->name) $fields["applicantNewfullName"] = $file_data->complaint->applicantNew->individualBusinessman->name->__toString();
					if($file_data->complaint->applicantNew->individualBusinessman->name) $fields["applicantNewsingularName"] = $file_data->complaint->applicantNew->individualBusinessman->name->__toString();
				}
				
				if($file_data->complaint->applicant->applicantType) $fields["applicantType"] = $file_data->complaint->applicant->applicantType->__toString();
				if($file_data->complaint->applicant->organizationName) $fields["organizationName"] = $file_data->complaint->applicant->organizationName->__toString();
				
				if($file_data->complaint->object->purchase->purchaseNumber) $fields["purchaseNumber"] = $file_data->complaint->object->purchase->purchaseNumber->__toString();
				if($file_data->complaint->object->purchase->purchaseCode) $fields["purchaseCode"] = $file_data->complaint->object->purchase->purchaseCode->__toString();
				if($file_data->complaint->object->purchase->purchaseName) $fields["purchaseName"] = $file_data->complaint->object->purchase->purchaseName->__toString();
				if($file_data->complaint->object->purchase->purchasePlacingDate) $fields["purchasePlacingDate"] = $file_data->complaint->object->purchase->purchasePlacingDate->__toString();
				
				if($file_data->complaint->text) $fields["text"] = $file_data->complaint->text->__toString();
				if($file_data->complaint->printForm->url) $fields["printFormurl"] = $file_data->complaint->printForm->url->__toString();
				
				if($file_data->complaint->attachments){
					if($file_data->complaint->attachments->attachment){
						$attachments = array();
						if(is_array($file_data->complaint->attachments->attachment)){
							foreach($file_data->complaint->attachments->attachment as $attachment){
								$attachments[] = array("publishedContentId"=>$attachment->publishedContentId->__toString(),
														"fileName"=>$attachment->fileName->__toString(),
														"docDescription"=>$attachment->docDescription->__toString(),
														"url"=>$attachment->url->__toString(),);
							}
						}else{
							$attachment = $file_data->complaint->attachments->attachment;
							$attachments[] = array("publishedContentId"=>$attachment->publishedContentId->__toString(),
														"fileName"=>$attachment->fileName->__toString(),
														"docDescription"=>$attachment->docDescription->__toString(),
														"url"=>$attachment->url->__toString());
						}
						$fields["attachments"] = json_encode($attachments);
					}
				}
				
				if($file_data->complaint->returnInfo->base) $fields["returnInfobase"] = $file_data->complaint->returnInfo->base->__toString();
				if($file_data->complaint->returnInfo->decision) $fields["returnInfodecision"] = $file_data->complaint->returnInfo->decision->__toString();
				
				db::insert_record("imported_complaint", $fields);
				//break;
			}
			if(strstr($filename, "complaintCancel_")){
				
				var_dump( "$filename size " . filesize($filename));
				$processed_file = db::sql_select("select * from imported_ftp_files where zipname = :zipname and filename = :filename", array("zipname"=>$ftp_file, "filename"=>basename($filename)));
				if($processed_file && count($processed_file)){
					unlink($filename);
					continue;
				}else{
					db::insert_record("imported_ftp_files", array("zipname"=>$ftp_file, "filename"=>basename($filename)));
				}
			
				$file_data = file_get_contents($filename);
				$file_data = str_replace("oos:", "", $file_data);
				$file_data = str_replace("ns2:", "", $file_data);
				$file_data = simplexml_load_string($file_data);
				//var_dump($file_data);
				
				if(is_array($file_data->complaintCancel)){
					foreach($file_data->complaintCancel as $complaintCancel){
						$fields = array();
						if($complaintCancel->complaintNumber) $fields["complaintNumber"] = $complaintCancel->complaintNumber->__toString();
						if($complaintCancel->isGroupItem) $fields["isGroupItem"] = $complaintCancel->isGroupItem->__toString();
						if($complaintCancel->regDate) $fields["regDate"] = $complaintCancel->regDate->__toString();
						
						if($complaintCancel->registrationKO->regNum ) $fields["registrationKOregNum"] = $complaintCancel->registrationKO->regNum->__toString();
						if($complaintCancel->registrationKO->fullName ) $fields["registrationKOfullName"] = $complaintCancel->registrationKO->fullName->__toString();
						
						if($complaintCancel->name) $fields["name"] = $complaintCancel->name->__toString();
						if($complaintCancel->regType) $fields["regType"] = $complaintCancel->regType->__toString();
						if($complaintCancel->text) $fields["text"] = $complaintCancel->text->__toString();
						
						if($complaintCancel->attachments){
							if($complaintCancel->attachments->attachment){
								$attachments = array();
								if(is_array($complaintCancel->attachments->attachment)){
									foreach($complaintCancel->attachments->attachment as $attachment){
										$attachments[] = array("publishedContentId"=>$attachment->publishedContentId->__toString(),
																"fileName"=>$attachment->fileName->__toString(),
																"docDescription"=>$attachment->docDescription->__toString(),
																"url"=>$attachment->url->__toString(),);
									}
								}else{
									$attachment = $complaintCancel->attachments->attachment;
									$attachments[] = array("publishedContentId"=>$attachment->publishedContentId->__toString(),
																"fileName"=>$attachment->fileName->__toString(),
																"docDescription"=>$attachment->docDescription->__toString(),
																"url"=>$attachment->url->__toString());
								}
								$fields["attachments"] = json_encode($attachments);
							}
						}
						
						if($complaintCancel->printForm->url) $fields["printFormurl"] = $complaintCancel->printForm->url->__toString();
						db::insert_record("imported_complaint_cancel", $fields);
					}
					
					
				}else{
					$fields = array();
					if($file_data->complaintCancel->complaintNumber) $fields["complaintNumber"] = $file_data->complaintCancel->complaintNumber->__toString();
					if($file_data->complaintCancel->isGroupItem) $fields["isGroupItem"] = $file_data->complaintCancel->isGroupItem->__toString();
					if($file_data->complaintCancel->regDate) $fields["regDate"] = $file_data->complaintCancel->regDate->__toString();
					
					if($file_data->complaintCancel->registrationKO->regNum ) $fields["registrationKOregNum"] = $file_data->complaintCancel->registrationKO->regNum->__toString();
					if($file_data->complaintCancel->registrationKO->fullName ) $fields["registrationKOfullName"] = $file_data->complaintCancel->registrationKO->fullName->__toString();
					
					if($file_data->complaintCancel->name) $fields["name"] = $file_data->complaintCancel->name->__toString();
					if($file_data->complaintCancel->regType) $fields["regType"] = $file_data->complaintCancel->regType->__toString();
					if($file_data->complaintCancel->text) $fields["text"] = $file_data->complaintCancel->text->__toString();
					
					if($file_data->complaintCancel->attachments){
						if($file_data->complaintCancel->attachments->attachment){
							$attachments = array();
							if(is_array($file_data->complaintCancel->attachments->attachment)){
								foreach($file_data->complaintCancel->attachments->attachment as $attachment){
									$attachments[] = array("publishedContentId"=>$attachment->publishedContentId->__toString(),
															"fileName"=>$attachment->fileName->__toString(),
															"docDescription"=>$attachment->docDescription->__toString(),
															"url"=>$attachment->url->__toString(),);
								}
							}else{
								$attachment = $file_data->complaintCancel->attachments->attachment;
								$attachments[] = array("publishedContentId"=>$attachment->publishedContentId->__toString(),
															"fileName"=>$attachment->fileName->__toString(),
															"docDescription"=>$attachment->docDescription->__toString(),
															"url"=>$attachment->url->__toString());
							}
							$fields["attachments"] = json_encode($attachments);
						}
					}
					
					if($file_data->complaintCancel->printForm->url) $fields["printFormurl"] = $file_data->complaintCancel->printForm->url->__toString();
					db::insert_record("imported_complaint_cancel", $fields);
				}
				
				//break;
			}
			unlink($filename);
		}
		foreach (glob($self_file_path."/unpacked/*.sig") as $filename) {
			unlink($filename);
		}
		$zip->close();
		//break;
	}
}
ftp_close($connect);
var_dump("End ".date("Y-m-d H:i:s"));
