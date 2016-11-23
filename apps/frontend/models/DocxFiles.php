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

}