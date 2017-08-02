<?php
error_reporting(E_ERROR);
var_dump("Start ".date("Y-m-d H:i:s"));

include_once(dirname(__FILE__)."/lib/lib_abstract.php");

//exit;

$host = "ftp.zakupki.gov.ru";
$connect = ftp_connect($host);
$login_result = ftp_login($connect, "free", "free");
ftp_chdir($connect, "/fcs_fas/checkResult/currMonth/");
var_dump("Текущая директория: " . ftp_pwd($connect));
$contents = ftp_nlist($connect, ".");
//var_dump($contents);
$self_file_path = realpath(dirname(__FILE__)."/../imported_ftp_files2/");
//var_dump($self_file_path);
foreach($contents as $ftp_file){
	if(!file_exists($self_file_path."/".$ftp_file) && strstr($ftp_file, "checkResult_") && strstr($ftp_file, ".zip")){
		$local_file = $self_file_path."/".$ftp_file;
		ftp_get($connect, $local_file, $ftp_file, FTP_BINARY);
		var_dump($local_file);
		$zip = new ZipArchive();
		//var_dump($zip);
		$zip->open($local_file);
		$zip->extractTo($self_file_path."/unpacked/");
		$finded_xml = false;
		foreach (glob($self_file_path."/unpacked/*.xml") as $filename) {
			if(strstr($filename, "checkResult_")){
				$file_data = file_get_contents($filename);
				$file_data = str_replace("oos:", "", $file_data);
				$file_data = str_replace("ns2:", "", $file_data);
				$file_data = simplexml_load_string($file_data);
				
				if($file_data->checkResult->complaint){
					var_dump( "$filename size " . filesize($filename));
					$processed_file = db::sql_select("select * from imported_ftp_files2 where zipname = :zipname and filename = :filename", array("zipname"=>$ftp_file, "filename"=>basename($filename)));
					if($processed_file && count($processed_file)){
						unlink($filename);
						continue;
					}else{
						db::insert_record("imported_ftp_files2", array("zipname"=>$ftp_file, "filename"=>basename($filename)));
					}
				
					//var_dump($file_data);
					$fields = array();
					
					if($file_data->checkResult->commonInfo->checkResultNumber) $fields["checkResultNumber"] = $file_data->checkResult->commonInfo->checkResultNumber->__toString();
					if($file_data->checkResult->commonInfo->regNumber) $fields["regNumber"] = $file_data->checkResult->commonInfo->regNumber->__toString();
					if($file_data->checkResult->commonInfo->docNumber) $fields["docNumber"] = $file_data->checkResult->commonInfo->docNumber->__toString();
					if($file_data->checkResult->commonInfo->versionNumber) $fields["versionNumber"] = $file_data->checkResult->commonInfo->versionNumber->__toString();
					if($file_data->checkResult->commonInfo->createDate) $fields["createDate"] = $file_data->checkResult->commonInfo->createDate->__toString();
					if($file_data->checkResult->commonInfo->publishDate) $fields["publishDate"] = $file_data->checkResult->commonInfo->publishDate->__toString();
					
					if($file_data->checkResult->commonInfo->owner->regNum) $fields["ownerregNum"] = $file_data->checkResult->commonInfo->owner->regNum->__toString();
					if($file_data->checkResult->commonInfo->owner->fullName) $fields["ownerfullName"] = $file_data->checkResult->commonInfo->owner->fullName->__toString();
					if($file_data->checkResult->commonInfo->owner->INN) $fields["ownerINN"] = $file_data->checkResult->commonInfo->owner->INN->__toString();
					if($file_data->checkResult->commonInfo->owner->KPP) $fields["ownerKPP"] = $file_data->checkResult->commonInfo->owner->KPP->__toString();
					
					if($file_data->checkResult->commonInfo->modification->info) $fields["modification"] = $file_data->checkResult->commonInfo->modification->info->__toString();
					
					if($file_data->checkResult->commonInfo->printFormInfo->createOrganization->regNum) $fields["createOrganizationregNum"] = $file_data->checkResult->commonInfo->printFormInfo->createOrganization->regNum->__toString();
					if($file_data->checkResult->commonInfo->printFormInfo->createOrganization->fullName) $fields["createOrganizationfullName"] = $file_data->checkResult->commonInfo->printFormInfo->createOrganization->fullName->__toString();
					if($file_data->checkResult->commonInfo->printFormInfo->createOrganization->INN) $fields["createOrganizationINN"] = $file_data->checkResult->commonInfo->printFormInfo->createOrganization->INN->__toString();
					if($file_data->checkResult->commonInfo->printFormInfo->createOrganization->KPP) $fields["createOrganizationKPP"] = $file_data->checkResult->commonInfo->printFormInfo->createOrganization->KPP->__toString();
					
					if($file_data->checkResult->commonInfo->printFormInfo->createUser) $fields["createUser"] = $file_data->checkResult->commonInfo->printFormInfo->createUser->__toString();
					
					if($file_data->checkResult->commonInfo->printFormInfo->editDate) $fields["editDate"] = $file_data->checkResult->commonInfo->printFormInfo->editDate->__toString();
					
					if($file_data->checkResult->commonInfo->printFormInfo->editOrganization->regNum) $fields["editOrganizationregNum"] = $file_data->checkResult->commonInfo->printFormInfo->editOrganization->regNum->__toString();
					if($file_data->checkResult->commonInfo->printFormInfo->editOrganization->fullName) $fields["editOrganizationfullName"] = $file_data->checkResult->commonInfo->printFormInfo->editOrganization->fullName->__toString();
					if($file_data->checkResult->commonInfo->printFormInfo->editOrganization->INN) $fields["editOrganizationINN"] = $file_data->checkResult->commonInfo->printFormInfo->editOrganization->INN->__toString();
					if($file_data->checkResult->commonInfo->printFormInfo->editOrganization->KPP) $fields["editOrganizationKPP"] = $file_data->checkResult->commonInfo->printFormInfo->editOrganization->KPP->__toString();
					
					if($file_data->checkResult->commonInfo->printFormInfo->editUser) $fields["editUser"] = $file_data->checkResult->commonInfo->printFormInfo->editUser->__toString();
					
					if($file_data->checkResult->commonInfo->printFormInfo->publishOrganization->regNum) $fields["publishOrganizationregNum"] = $file_data->checkResult->commonInfo->printFormInfo->publishOrganization->regNum->__toString();
					if($file_data->checkResult->commonInfo->printFormInfo->publishOrganization->fullName) $fields["publishOrganizationfullName"] = $file_data->checkResult->commonInfo->printFormInfo->publishOrganization->fullName->__toString();
					if($file_data->checkResult->commonInfo->printFormInfo->publishOrganization->INN) $fields["publishOrganizationINN"] = $file_data->checkResult->commonInfo->printFormInfo->publishOrganization->INN->__toString();
					if($file_data->checkResult->commonInfo->printFormInfo->publishOrganization->KPP) $fields["publishOrganizationKPP"] = $file_data->checkResult->commonInfo->printFormInfo->publishOrganization->KPP->__toString();
					
					if($file_data->checkResult->commonInfo->printFormInfo->publishUser) $fields["publishUser"] = $file_data->checkResult->commonInfo->printFormInfo->publishUser->__toString();
					
					if($file_data->checkResult->complaint->complaintNumber) $fields["complaintNumber"] = $file_data->checkResult->complaint->complaintNumber->__toString();
					if($file_data->checkResult->complaint->regNumber) $fields["complaintregNumber"] = $file_data->checkResult->complaint->regNumber->__toString();
					if($file_data->checkResult->complaint->publishDate) $fields["complaintpublishDate"] = $file_data->checkResult->complaint->publishDate->__toString();
					
					if($file_data->checkResult->complaint->checkSubjects->subjectComplaint->customerNew->fullName) $fields["customerNewfullName"] = $file_data->checkResult->complaint->checkSubjects->subjectComplaint->customerNew->fullName->__toString();
					if($file_data->checkResult->complaint->checkSubjects->subjectComplaint->customerNew->shortName) $fields["customerNewshortName"] = $file_data->checkResult->complaint->checkSubjects->subjectComplaint->customerNew->shortName->__toString();
					if($file_data->checkResult->complaint->checkSubjects->subjectComplaint->customerNew->legalForm->code) $fields["customerNewlegalFormcode"] = $file_data->checkResult->complaint->checkSubjects->subjectComplaint->customerNew->legalForm->code->__toString();
					if($file_data->checkResult->complaint->checkSubjects->subjectComplaint->customerNew->legalForm->singularName) $fields["customerNewlegalFormsingularName"] = $file_data->checkResult->complaint->checkSubjects->subjectComplaint->customerNew->legalForm->singularName->__toString();
					if($file_data->checkResult->complaint->checkSubjects->subjectComplaint->customerNew->INN) $fields["customerNewINN"] = $file_data->checkResult->complaint->checkSubjects->subjectComplaint->customerNew->INN->__toString();
					if($file_data->checkResult->complaint->checkSubjects->subjectComplaint->customerNew->KPP) $fields["customerNewKPP"] = $file_data->checkResult->complaint->checkSubjects->subjectComplaint->customerNew->KPP->__toString();
					if($file_data->checkResult->complaint->checkSubjects->subjectComplaint->customerNew->registrationDate) $fields["customerNewregistrationDate"] = $file_data->checkResult->complaint->checkSubjects->subjectComplaint->customerNew->registrationDate->__toString();
					if($file_data->checkResult->complaint->checkSubjects->subjectComplaint->customerNew->OKPO) $fields["customerNewOKPO"] = $file_data->checkResult->complaint->checkSubjects->subjectComplaint->customerNew->OKPO->__toString();
					if($file_data->checkResult->complaint->checkSubjects->subjectComplaint->customerNew->address) $fields["customerNewaddress"] = $file_data->checkResult->complaint->checkSubjects->subjectComplaint->customerNew->address->__toString();
					if($file_data->checkResult->complaint->checkSubjects->subjectComplaint->customerNew->contactPhone) $fields["customerNewcontactPhone"] = $file_data->checkResult->complaint->checkSubjects->subjectComplaint->customerNew->contactPhone->__toString();
					if($file_data->checkResult->complaint->checkSubjects->subjectComplaint->customerNew->contactEMail) $fields["customerNewcontactEMail"] = $file_data->checkResult->complaint->checkSubjects->subjectComplaint->customerNew->contactEMail->__toString();
					
					if($file_data->checkResult->complaint->checkedObject->purchase->purchaseNumber) $fields["purchaseNumber"] = $file_data->checkResult->complaint->checkedObject->purchase->purchaseNumber->__toString();
					if($file_data->checkResult->complaint->checkedObject->purchase->purchaseCodes->purchaseCode) $fields["purchaseCode"] = $file_data->checkResult->complaint->checkedObject->purchase->purchaseCodes->purchaseCode->__toString();
					if($file_data->checkResult->complaint->checkedObject->purchase->purchaseName) $fields["purchaseName"] = $file_data->checkResult->complaint->checkedObject->purchase->purchaseName->__toString();
					
					if($file_data->checkResult->complaint->decision->decisionNumber) $fields["decisionNumber"] = $file_data->checkResult->complaint->decision->decisionNumber->__toString();
					if($file_data->checkResult->complaint->decision->decisionDate) $fields["decisionDate"] = $file_data->checkResult->complaint->decision->decisionDate->__toString();
					if($file_data->checkResult->complaint->decision->decisionText) $fields["decisionText"] = $file_data->checkResult->complaint->decision->decisionText->__toString();
					
					if($file_data->checkResult->complaint->decision->attachments){
						if($file_data->checkResult->complaint->decision->attachments->attachment){
							$attachments = array();
							if(is_array($file_data->checkResult->complaint->decision->attachments->attachment)){
								foreach($file_data->checkResult->complaint->decision->attachments->attachment as $attachment){
									$attachments[] = array("fileName"=>$attachment->fileName->__toString(),
															"docDescription"=>$attachment->docDescription->__toString(),
															"url"=>$attachment->url->__toString());
								}
							}else{
								$attachment = $file_data->checkResult->complaint->decision->attachments->attachment;
								$attachments[] = array("fileName"=>$attachment->fileName->__toString(),
															"docDescription"=>$attachment->docDescription->__toString(),
															"url"=>$attachment->url->__toString());
							}
							$fields["decisionattachments"] = json_encode($attachments);
						}
					}
					
					if($file_data->checkResult->complaint->complaintResult) $fields["complaintResult"] = $file_data->checkResult->complaint->complaintResult->__toString();
					//if($file_data->checkResult->complaint->checkResult) $fields["checkResult"] = $file_data->checkResult->complaint->checkResult->__toString();
					
					if($file_data->checkResult->printForm->url) $fields["printFormurl"] = $file_data->checkResult->printForm->url->__toString();
					
					$finded_xml = true;
					db::insert_record("imported_checkresult", $fields);
					//break;
				}
			}
			
			unlink($filename);
		}
		foreach (glob($self_file_path."/unpacked/*.sig") as $filename) {
			unlink($filename);
		}
		$zip->close();
		//if($finded_xml) break;
	}
}
ftp_close($connect);
var_dump("End ".date("Y-m-d H:i:s"));
