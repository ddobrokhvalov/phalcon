<?php
namespace Multiple\Frontend\Controllers;

use Phalcon\Mvc\Controller;
use Multiple\Frontend\Models\Applicant;
use Multiple\Library\TrustedLibrary;
use Multiple\Frontend\Models\Documents;
use Phalcon\Di;

class AjaxController extends Controller{

    public function indexAction(){
        $Documents = Documents::findFirst(
            array(
                "type = :type:",
                'bind' => array(
                    'type' => 0,
                ),
                "order" => "id DESC",
            )
        );
        echo $Documents->id;
    }

//    public function trustedAction(){
//        ini_set('display_errors', false);
//        TrustedLibrary::trusted_esign();
//    }
    
    public function getlastAction(){
        $Documents = Documents::findFirst(
            array(
                "TYPE = :type:",
                'bind' => array(
                    'type' => 0,
                ),
                "order" => "ID DESC",
            )
        );
        echo $Documents->ID;
        exit;
    }
    
    public function fileuploadAction(){
        ini_set('display_errors', true);
        $fileName = $_FILES['file']['name'];
        $fileType = $_FILES['file']['type'];
        $fileError = $_FILES['file']['error'];
        $fileTmpName = $_FILES['file']['tmp_name'];
        if($fileError == UPLOAD_ERR_OK){
            if(is_uploaded_file($fileTmpName)) {
                #todo: если имена совпадают, то нужно генерить случайное
                move_uploaded_file($fileTmpName, $_SERVER['DOCUMENT_ROOT'] . "/public/files/documents/" . $fileName);
                $db = DI::getDefault()->get('db');
                $db->query('INSERT INTO trn_documents (TIMESTAMP_X, ORIGINAL_NAME, SYS_NAME, PATH, SIGNERS, TYPE)
                      values(NOW(),"'.$fileName.'","'.$fileName.'","'.$_SERVER['DOCUMENT_ROOT'] . "/public/files/documents/" . $fileName.'","me","'.$fileType.'")');//todo: побеспокоится о безопасности запроса
               }
        }else{
            switch($fileError){
                case UPLOAD_ERR_INI_SIZE:
                    $message = 'Error al intentar subir un archivo que excede el tamaño permitido.';
                    break;
                case UPLOAD_ERR_FORM_SIZE:
                    $message = 'Error al intentar subir un archivo que excede el tamaño permitido.';
                    break;
                case UPLOAD_ERR_PARTIAL:
                    $message = 'Error: no terminó la acción de subir el archivo.';
                    break;
                case UPLOAD_ERR_NO_FILE:
                    $message = 'Error: ningún archivo fue subido.';
                    break;
                case UPLOAD_ERR_NO_TMP_DIR:
                    $message = 'Error: servidor no configurado para carga de archivos.';
                    break;
                case UPLOAD_ERR_CANT_WRITE:
                    $message= 'Error: posible falla al grabar el archivo.';
                    break;
                case  UPLOAD_ERR_EXTENSION:
                    $message = 'Error: carga de archivo no completada.';
                    break;
                default: $message = 'Error: carga de archivo no completada.';
                    break;
            }
            echo json_encode(array(
                'error' => true,
                'message' => $message
            ));
        }
        exit();
    }
}