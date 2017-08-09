<?php
namespace Multiple\Frontend\Models;
use Phalcon\Mvc\Model;

class DocxFiles extends Model
{
    public $docx_id;
    public $docx_file_name;
    public $complaint_id;
    public $complaint_name;
    public $user_id;
    public $format;
    public $recall;
    public $created_at;

    public function initialize() {
        $this->setSource('docx_files');
    }

    public function getSource() {
        return 'docx_files';
    }
	
	/*public function getFileByComplaintId($complaint_id){
		$db = $this->getDi()->getShared('db');
		$sql = "select * from docx_files where complaint_id = {$complaint_id} and format = 1 order by docx_id desc";
		$result = $db->query($sql);
		if($result && count($result)){
			return $result[0];
		}else{
			return false;
		}
	}*/

}