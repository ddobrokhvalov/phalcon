<?php
namespace Multiple\Backend\Models;

use Phalcon\Mvc\Model;

class Files extends Model
{
    public $id;
    public $file_path;
    public $file_size;
    public $file_type;

    public function initialize()
    {
        $this->setSource("files");
    }

    public function getSource()
    {
        return "files";
    }

    public function checkAllFiles($request, $obb) {
        foreach ($request->getUploadedFiles() as $file) {
            if (!$this->isAllowedExtension($file->getType(), $obb)) {
                return FALSE;
            }
        }
        return TRUE;
    }
    
    public function isAllowedExtension($ext, $obb) {
        $obb->flashSession->error($ext);
        $extensions = array(
            'image/jpeg',
            'image/png',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/pdf',
            'application/octet-stream',
            'application/x-rar-compressed',
            'application/zip',
        );
        return in_array($ext, $extensions);
    }

    public function getFileClass($file_type) {
        $extensions = array(
            'image/jpeg'                                                                => 'image-file',
            'image/png'                                                                 => 'image-file',
            'application/msword'                                                        => 'msword-file',
            'application/pdf'                                                           => 'pdf-file',
            'application/octet-stream'                                                  => 'msword-file',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'   => 'msword-file',
            'application/x-rar-compressed'                                              => 'archive-file',
            'application/zip'                                                           => 'archive-file',
        );
        return $extensions["{$file_type}"];
    }
    
    public function getFilesHtml($file, $applicant) {
        $html = '';
        $html .= "<div class=\"attached-file-row\">";
        $html .=    "<div class=\"file-icon {$this->getFileClass($file->file_type)}\">";
        $html .=    '</div>';
        $html .=    "<a class=\"file-link\" href=\"/files/applicant/{$file->file_path}\" title=\"Скачать файл\">";
        $html .=        '<div>';
        $html .=            $file->file_path;
        $html .=            " ({$file->file_size} Кб)";
        $html .=        '</div>';
        $html .=    '</a>';
        $html .=    '<div class="delete-file" title="Удалить файл">';
        $html .=        "<input type=\"hidden\" name=\"file-id\" id=\"file-id\" value=\"{$file->id}\">";
        $html .=        "<input type=\"hidden\" name=\"applicant-id\" id=\"applicant-id\" value=\"{$applicant}\">";
        $html .=    '</div>';
        $html .=    '<div style="clear:both;"></div>';
        $html .= '</div>';
        return $html;
    }
}