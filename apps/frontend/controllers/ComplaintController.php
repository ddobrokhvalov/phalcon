<?php

namespace Multiple\Frontend\Controllers;

use Multiple\Frontend\Models\ApplicantECP;
use Multiple\Frontend\Models\Applicant;
use Multiple\Frontend\Models\Category;
use Multiple\Frontend\Models\Complaint;
use Multiple\Frontend\Models\ComplaintMovingHistory;
use Multiple\Backend\Models\Log as LogModel;
use Multiple\Frontend\Models\Question;
use Multiple\Frontend\Models\UsersArguments;
use Multiple\Frontend\Models\DocxFiles;
use Multiple\Frontend\Models\Files;
use Multiple\Backend\Models\Ufas;
use Multiple\Library\Log;
use Multiple\Library\Calendar\BasicDataRu;
use Multiple\Library\Parser;
use Phalcon\Acl\Exception;
use Phalcon\Mvc\Controller;
use \Phalcon\Paginator\Adapter\NativeArray as Paginator;
use Multiple\Library\PaginatorBuilder;
use Multiple\Frontend\Models\Arguments;
use Multiple\Frontend\Models\ArgumentsCategory;
use  Phalcon\Mvc\Model\Query\Builder;
use Multiple\Frontend\Models\Messages;
use Phalcon\Mvc\Url;
use Multiple\Library\Translit;
use Multiple\Frontend\Models\User;
use Multiple\Library\Calendar\Calendar;


class ComplaintController extends ControllerBase
{
    const STEP_ONE = 1;
    const STEP_TWO = 2;
    const STEP_THREE = 3;
    const STEP_FOUR = 4;
    const STEP_SEARCH = 6;

    public function indexAction()
    {
        if (!$this->user) {
            $this->flashSession->error('Вы не залогинены в системе');
            return $this->response->redirect('/');
        }
        $search = $this->request->get('search');
        $search = preg_replace("/[^a-zA-ZА-Яа-я0-9\s]/u", "", $search);


        $this->setMenu();
        $complaint = new Complaint();
        $status = 0;
        $numberPage = $this->request->getQuery("page", "int");
        if ($numberPage === null) $numberPage = 1;
        if (isset($_GET['status']))
            $status = $_GET['status'];

        $complaints = $complaint->findUserComplaints($this->user->id, $status, $this->applicant_id, $search);
        #$this->view->complaints = $complaints;
        $this->view->status = $status;
        $paginator = new Paginator(array(
            "data" => $complaints,
            "limit" => 10,
            "page" => $numberPage
        ));
        $pages = $paginator->getPaginate();
        if ($status) {
            $url = '/complaint/index?status=' . $status;
        } else {
            $url = '/complaint/index';
        }

        $this->view->searchurl = $url;
        $this->view->searhparam = $search;
        $this->view->page = $pages;
        $this->view->paginator_builder = PaginatorBuilder::buildPaginationArray($numberPage, $pages->total_pages);
        $this->view->index_action = true;
        $this->view->count_items = count($complaints);
        $this->view->status = $status;

    }

    public function deleteFileAction()
    {
        $file_id = $this->request->getPost('file_id');
        $complaint_id = $this->request->getPost('complaint_id');
        if ($file_id && $complaint_id) {
            $complaint = Complaint::findFirstById($complaint_id);
            if ($complaint) {
                $file = Files::findFirstById($file_id);
                if ($file) {
                    $file->delete();
                    $complaint_files = unserialize($complaint->fid);
                    if (count($complaint_files)) {
                        unset($complaint_files[array_search($file_id, $complaint_files)]);
                        $complaint->fid = serialize(array_values($complaint_files));
                    } else {
                        $complaint->fid = serialize(array());
                    }
                    $complaint->save();
                    //$this->flashSession->success('Файл удален');
                }
            }
        }
        $this->view->disable();
        $data = "ok";
        echo json_encode($data);
    }

    public function editAction($id)
    {
        $complaint = Complaint::findFirstById($id);
        if (!$complaint || !$complaint->checkComplaintOwner($id, $this->user->id))
            return $this->forward('complaint/index');
        $applicant = Applicant::findFirstById($complaint->applicant_id);
        $this->session->set('save_applicant', $this->session->get('applicant'));
        $this->session->set('applicant', array('applicant_id' => $complaint->applicant_id));
        // Load arguments
        $category = new Category();
        $arguments = $category->getArguments();
        $this->view->arguments = $arguments;
        // Load users arguments
        $arguments = UsersArguments::find(
            array(
                'complaint_id = :complaint_id:',
                'bind' => [
                    'complaint_id' => $id,
                ]
            )
        );
        $user_arguments = '';
        $argument_order = 0;
        $categories_id = [];
        $arguments_id = [];
        $arr_sub_cat = array();

        foreach ($arguments as $argument) {
            $text = $argument->text;
            $categories_id[] = $argument->argument_category_id;
            $arguments_id[] = $argument->argument_id;
            $text = preg_replace('/[\r\n\t]/', '', $text);
            $text = str_replace("'", '"', $text);
            $arr_users_arg[$argument->argument_id] = $text;

            /*if ($argument_order == $complaint->complaint_text_order) {
                $user_arguments .= $complaint->complaint_text . '</br>';
                $user_arguments .= $argument->text . '</br>';
            } else {*/
            $user_arguments .= $argument->text . '</br>';
            //}
            $arr_sub_cat[] = array(
                'id' => $argument->argument_id,
                'text' => preg_replace('/[\r\n\t]/', '', $text)
            );
            ++$argument_order;
        }

        if (!empty($arr_sub_cat)) {
            $this->view->arr_sub_cat = $arr_sub_cat;
        }
        $this->view->arr_users_arg = $arr_users_arg;
        $this->view->categories_id = implode(',', $categories_id);
        $this->view->arguments_id = implode(',', $arguments_id);
       

        //$this->view->complaint_text_order = $complaint->complaint_text_order;

        $files_html = [];
        if ($complaint->fid) {
            $file_ids = unserialize($complaint->fid);
            if (count($file_ids)) {
                $file_model = new Files();
                $files = Files::find(
                    array(
                        'id IN ({ids:array})',
                        'bind' => array(
                            'ids' => $file_ids
                        )
                    )
                );
                foreach ($files as $file) {
                    $files_html[] = $file_model->getFilesHtml($file, $id, 'complaints');
                }
            }
        }
        $action = $this->request->get('action');
        if (isset($action) && $action == 'edit') {
            $this->view->edit_now = TRUE;
            $ufas = Ufas::find();
            $this->view->ufas = $ufas;

            $dayofsendufas = LogModel::findFirst("customer_email = '{$this->user->email}' and type='Отправка в УФАС' and additionally= '{$complaint->auction_id}'");

            $this->view->dayofsendufas=(strtotime($dayofsendufas->date) > strtotime("-1 day"))?1:0;
            $this->view->dateofsendufas=$dayofsendufas->date;
        } else {
            $this->view->edit_now = FALSE;
        }

        $m_user_id = $this->user->id;
        $messages = Messages::find("comp_id = {$id} AND to_uid = {$m_user_id} ORDER BY time DESC");
        if ($messages) {
            foreach ($messages as $mess) {
                $mess->is_read = 1;
                $mess->update();
            }
        }
        $this->view->user_arguments = $user_arguments;
        $history = ComplaintMovingHistory::find("complaint_id = $id ORDER BY date DESC");
        if ($history) {
            foreach ($history as $hist) {
                $hist->is_read = 1;
                $hist->update();
            }
        }
        $this->view->attached_files = $files_html;
        $complaint->purchases_name = str_replace("\r\n", " ", $complaint->purchases_name);
        $question = new Question();
        $complaintQuestion = $question->getComplainQuestionAndAnswer($id);
        $this->setMenu();

        $this->view->applicant_session = $applicant->id;
        $this->view->checkUser = $this->checkUser();
        $this->applicant_id = $applicant->id;
//        $parser = new Parser();
//        $data = $parser->parseAuction((string)$complaint->auction_id);
//
//        $complaint->nachalo_podachi =           isset($data['procedura']['nachalo_podachi'])            ? $data['procedura']['nachalo_podachi']         : null;
//        $complaint->okonchanie_podachi =        isset($data['procedura']['okonchanie_podachi'])         ? $data['procedura']['okonchanie_podachi']      : null;
//        $complaint->okonchanie_rassmotreniya =  isset($data['procedura']['okonchanie_rassmotreniya'])   ? $data['procedura']['okonchanie_rassmotreniya']: null;
//        $complaint->data_provedeniya =          isset($data['procedura']['data_provedeniya'])           ? $data['procedura']['data_provedeniya']        : null;
//        $complaint->vremya_provedeniya =        isset($data['procedura']['vremya_provedeniya'])         ? $data['procedura']['vremya_provedeniya']      : null;
//        $complaint->vskrytie_konvertov =        isset($data['procedura']['vskrytie_konvertov'])         ? $data['procedura']['vskrytie_konvertov']      : null;
//        $complaint->data_rassmotreniya =        isset($data['procedura']['data_rassmotreniya'])         ? $data['procedura']['data_rassmotreniya']      : null;

        $this->view->ufas_name = 'Уфас не определен';
        $this->view->comp_inn = 'null';
        if ($complaint->ufas_id != null) {
            $ufas_name = Ufas::findFirst(array(
                "id={$complaint->ufas_id}"
            ));
            if ($ufas_name) {
                $this->view->ufas_name = $ufas_name->name;
                $this->view->comp_inn = $ufas_name->number;
            }
        }


        if (is_null($complaint->date_start)) $complaint->date_start = $complaint->nachalo_podachi;
        $this->view->date_end = $this->checkDateEndSendApp($complaint->okonchanie_podachi);
        $this->view->edit_mode = 1;
        $this->view->complaint = $complaint;
        $this->view->complaint_question = $complaintQuestion;
        $this->view->action_edit = false;
        if (isset($_GET['action']) && $_GET['action'] == 'edit' && $complaint->status == 'draft')
            $this->view->action_edit = true;
        unset($data);
    }

    public function test1Action()
    {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);

        $signature = 'complaint_1493127933.docx';
        $signFileOriginName = 'complaint_1493127933.docx.sig';
        $baseLocation = $_SERVER['DOCUMENT_ROOT'].'/files/generated_complaints/user_' . $this->user->id . '/';
        // unlink($baseLocation . $signFileOriginName.'sig');

        $file = file_get_contents($baseLocation . $signFileOriginName);
        $sendData = array(
            "method" => 'Signature.verifyMessageSignature',
            "id" => 1,
            "params" => array(
                "signature" => $signature,
                "message" => base64_encode($file)
            )
        );

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, 'http://185.20.225.233/api/v1/json');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($sendData));
        $out = curl_exec($curl);

        print_R($out);
        die;

        //file_put_contents($baseLocation . $signFileOriginName . '.sig', base64_decode($signature));
        /*if(preg_match('/recall/', $signFileOriginName)){

            $this->SendToUfas(array(
                '../public/'.$baseLocation.$signFileOriginName.'.sig',
                '../public/'.$baseLocation.$signFileOriginName,
            ));
        }*/

        echo 'done';
        exit;
    }

    public function test2Action()
    {

        error_reporting(E_ALL);
        ini_set('display_errors', 1);

        header('Content-Type: text/html; charset=UTF-8');
        $value = '<p>На основании решения контрольного органа в сфере закупок <em>«указать реквизиты решения»</em> выдано предписание, согласно которому <em>«указать кому и какие действия предписаны».</em></p><p><img src="' . $_SERVER['DOCUMENT_ROOT'] . '/files/generated_complaints/user_169/2017.png"/></p><p>В силу пункта 2 части 22 статьи 99 Закона о контрактной системе предписания об устранении нарушений законодательства Российской Федерации и иных нормативных правовых актов о контрактной системе, выданные контрольным органом в сфере закупок обязательны для исполнения.</p><p>х.</p><p><br></p>';
        require_once $_SERVER['DOCUMENT_ROOT'] . '/public/phpdocx/classes/CreateDocx.inc';
        $docx = new \CreateDocxFromTemplate($_SERVER['DOCUMENT_ROOT'] . "/public/js/docx_generator/docx_templates/documentation_phpword.docx");

        $data['applicant_fio'] = ' "ООО ПСК ""СТРОЙПРОЕКТ"""';
        $data['applicant_fio2'] = ' Болквадзе Мамука Фридонович';
        $data['applicant_address'] = ' ГОРОД САНКТ-ПЕТЕРБУРГ,,,,ПРОСПЕКТ СЕВЕРНЫЙ,ДОМ 93,ЛИТЕР А,ПОМЕЩЕНИЕ 6Н';
        $data['applicant_phone'] = ' 34545';
        $data['applicant_position'] = ' Генеральный директор';
        $data['applicant_email'] = ' f-ree-z@inbox.ru';
        $data['tip_zakupki'] = ' Электронный аукцион';
        $data['ufas'] = ' Управление Федеральной антимонопольной службы по Санкт-Петербургу';
        $data['dovod'] = ' <p>132</p><br>';
        $data['zakaz_phone'] = ' +7 (812) 5710767';
        $data['zakaz_kontaktnoe_lico'] = ' Иванова Светлана Федоровна';
        $data['zakaz_kontaktnoe_name1'] = ' ';
        $data['zakaz_kontaktnoe_name2'] = '  «САНКТ-ПЕТЕРБУРГСКОЕ ГОСУДАРСТВЕННОЕ КАЗЕННОЕ УЧРЕЖДЕНИЕ "ЖИЛИЩНОЕ  АГЕНТСТВО АДМИРАЛТЕЙСКОГО РАЙОНА САНКТ-ПЕТЕРБУРГА"»';
        $data['kontakt'] = ' Название организации:';
        $data['zakaz_address'] = ' Российская Федерация, 190000, Санкт-Петербург, НАБ КАНАЛА ГРИБОЕДОВА, 83, ОФИС';
        $data['zakaz_mesto'] = '  «САНКТ-ПЕТЕРБУРГСКОЕ ГОСУДАРСТВЕННОЕ КАЗЕННОЕ УЧРЕЖДЕНИЕ "ЖИЛИЩНОЕ АГЕНТСТВО А ДМИРАЛТЕЙСКОГО РАЙОНА САНКТ-ПЕТЕРБУРГА"»';
        $data['organiz_fio1'] = ' АДМИНИСТРАЦИЯ АДМИРАЛТЕЙСКОГО РАЙОНА САНКТ-ПЕТЕРБУРГА';
        $data['organiz_fio2'] = ' Плескач Екатерина Николаевна';
        $data['organiz_phone'] = ' 7-812-5769715';
        $data['organiz_mesto'] = ' Российская Федерация, 190005, Санкт-Петербург, Измайловский, 10';
        $data['organiz_address'] = ' 190005, Санкт-Петербург Город, Измайловский Проспект, дом 10';
        $data['izveshchenie'] = ' 0172200003617000019';
        $data['zakupka_name'] = ' Выполнение работ по комплексному благоустройству дворов, не входящих в состав  общего имущества многоквартирных домов Адмиралтейского района Санкт-Петербурга, в 2017 году';
        $data['zayavitel'] = ' "ООО ПСК ""СТРОЙПРОЕКТ"""';

        foreach ($data as $key => $value) {
            if ($key == 'dovod') {
                /*preg_match_all('/<img.*?src\s*=(.*?)>/', $value, $out);
                if (count($out[1])) {
                    foreach ($out[1] as $key1 => $image) {
                        $explode = explode(" ", $image);
                        $image = trim($explode[0], '"');

                        $file_name = time() + rand();

                        if(substr_count($image, 'data:image'))
                            $value = str_replace($out[0][$key1], '<img src="' . $_SERVER['DOCUMENT_ROOT'] . "/files/generated_complaints/user_" . $this->user->id . "/" . $this->save_base64_image($image, time() + rand(), $_SERVER['DOCUMENT_ROOT'] . "/files/generated_complaints/user_" . $this->user->id . "/") . '"><br/>', $value);
                    }
                }*/

                $docx->replaceVariableByHTML($key, 'block', $value, array('isFile' => false, 'parseDivsAsPs' => true, 'downloadImages' => true));
            } else
                $docx->replaceVariableByHTML($key, 'inline', $value, array('isFile' => false, 'parseDivsAsPs' => true, 'downloadImages' => true));
        }

        $baseLocation = 'files/generated_complaints/user_' . $this->user->id . '/';
        $name = 'complaint_' . time() . '.docx';

        $docx->createDocx($baseLocation . $name);
    }

    /*function save_base64_image($base64_image_string, $output_file_without_extentnion, $path_with_end_slash = "")
    {
        //usage:  if( substr( $img_src, 0, 5 ) === "data:" ) {  $filename=save_base64_image($base64_image_string, $output_file_without_extentnion, getcwd() . "/application/assets/pins/$user_id/"); }      
        //
        //data is like:    data:image/png;base64,asdfasdfasdf
        $splited = explode(',', substr($base64_image_string, 5), 2);
        $mime = $splited[0];
        $data = $splited[1];

        $mime_split_without_base64 = explode(';', $mime, 2);
        $mime_split = explode('/', $mime_split_without_base64[0], 2);
        if (count($mime_split) == 2) {
            $extension = $mime_split[1];
            if ($extension == 'jpeg') $extension = 'jpg';
            //if($extension=='javascript')$extension='js';
            //if($extension=='text')$extension='txt';
            $output_file_with_extentnion .= $output_file_without_extentnion . '.' . $extension;
        }

        file_put_contents($path_with_end_slash . $output_file_with_extentnion, base64_decode($data));
        return $output_file_with_extentnion;
    }*/

    public function browseAction($id)
    {
        $dir = 'files/generated_complaints/user_' . $this->user->id . '/';
        //$sorted = scandir($dir);
        $file_read = array('docx');

        $files = array();
        foreach (scandir($dir) as $file) $files[$file] = filemtime("$dir/$file");
        asort($files);
        $sorted = array_keys($files);

        foreach ($sorted as $key => $value) {
            if (!in_array($value, array('.', '..'))) {
                $type = explode('.', $value);
                $type = array_reverse($type);
                if (!in_array($type[0], $file_read)) {
                    continue;
                }
                $file = $value;
            }
        }
        //$this->setMenu();
        //$this->view->url = 'https://view.officeapps.live.com/op/view.aspx?src='.'http%3A%2F%2Fufa.ru%2Fcomplaint_1488558920.docx';
        $this->view->url = 'https://view.officeapps.live.com/op/view.aspx?src=https://fasonline.ru/' . $dir . $file;
    }

    public function saveHtmlFileAction()
    {
        //error_reporting(E_ALL);
        //ini_set('display_errors', 1);

        $name = false;
        $format = 1;
        $recall = 0;
        $recall = $this->request->get('recall');

        if ($this->request->getPost('doc')) {
            $baseLocation = 'files/generated_complaints/user_' . $this->user->id . '/';
            if (strlen($this->request->getPost('doc'))) {
                if (!file_exists($baseLocation)) {
                    mkdir($baseLocation, 0777, true);
                }

                try {
                    if (empty($recall)) {
                        $unformatted = isset($_GET['unformatted']) ? 'unformatted_' : '';
                        if ($unformatted == 'unformatted_') {
                            $format = 0;
                        }
                        $name = 'complaint_' . $unformatted . time() . '.docx';
                        $data = json_decode($this->request->getPost('doc'));

                        require_once $_SERVER['DOCUMENT_ROOT'] . '/public/phpdocx/classes/CreateDocx.inc';
                        $docx = new \CreateDocxFromTemplate($_SERVER['DOCUMENT_ROOT'] . "/public/js/docx_generator/docx_templates/" . $this->request->getPost('file_to_load'));

                        foreach ($data as $key => $value) {
                            if ($key == 'dovod') {
                                /*preg_match_all('/<img.*?src\s*=(.*?)>/', $value, $out);
                                if (count($out[1])) {
                                    foreach ($out[1] as $key1 => $image) {
                                        $explode = explode(" ", $image);
                                        $image = trim($explode[0], '"');

                                        $file_name = time() + rand();

                                        if(substr_count($image, 'data:image'))
                                            $value = str_replace($out[0][$key1], '<img src="' . $_SERVER['DOCUMENT_ROOT'] . "/files/generated_complaints/user_" . $this->user->id . "/" . $this->save_base64_image($image, time() + rand(), $_SERVER['DOCUMENT_ROOT'] . "/files/generated_complaints/user_" . $this->user->id . "/") . '"><br/>', $value);
                                    }
                                }*/

                                if (trim($value) == '') $value = '  ';
                                $docx->replaceVariableByHTML($key, 'block', $value, array('isFile' => false, 'parseDivsAsPs' => true, 'downloadImages' => true));
                            } else
                                if (trim($value) == '') $value = '  ';
                            $docx->replaceVariableByHTML($key, 'inline', $value, array('isFile' => false, 'parseDivsAsPs' => true, 'downloadImages' => true));
                        }

                        //$templateProcessor->saveAs($baseLocation . $name);
                        $docx->createDocx($baseLocation . $name);

                        //$file->moveTo($baseLocation . $name);

                    } else {
                        $unformatted = isset($_GET['unformatted']) ? 'unformatted_' : '';
                        $name = 'recall_' . $unformatted . time() . '.docx';
                        $recall = 1;

                        $data = json_decode($this->request->getPost('doc'));
                        require_once $_SERVER['DOCUMENT_ROOT'] . '/public/phpdocx/classes/CreateDocx.inc';
                        $docx = new \CreateDocxFromTemplate($_SERVER['DOCUMENT_ROOT'] . "/public/js/docx_generator/docx_templates/" . $this->request->getPost('file_to_load'));

                        foreach ($data as $key => $value) {
                            if (trim($value) == '') $value = '  ';
                            $docx->replaceVariableByHTML($key, 'block', $value, array('isFile' => false, 'parseDivsAsPs' => true, 'downloadImages' => false));
                        }

                        $docx->createDocx($baseLocation . $name);
                        //$file->moveTo($baseLocation . $name);
                    }

                } catch (\Exception $e) {
                    print_R('ok=');
                    print_R($e);
                    die;
                }

            }
            $docx = new DocxFiles();
            if (!empty($recall)) {
                $docx->complaint_id = $this->request->get('complaint_id');
            }
            $docx->docx_file_name = $name;


            $tempCompPost = $this->request->getPost('complaint_id');
            $tempCompGet = $this->request->getQuery('complaint_id');
            if (is_numeric($tempCompPost)) {
                $compl_id = $tempCompPost;
            } elseif (is_numeric($tempCompGet)) {
                $compl_id = $tempCompGet;
            }

            $docx->complaint_name = $this->request->getPost('complaint_name');
            if (isset($compl_id) && $compl_id != 'undefined') {
                $delete_docx = DocxFiles::find("complaint_id = $compl_id");
                if (count($delete_docx) >= 2) {
                    foreach ($delete_docx as $del_docx) {
                        $del = @unlink($baseLocation . $del_docx->docx_file_name);
                        $del = @unlink($baseLocation . $del_docx->docx_file_name . '.sig');
                        $del_docx->delete();
                    }
                } else {
                    $delete_docx = DocxFiles::find("complaint_id = $compl_id AND recall = 1");
                    foreach ($delete_docx as $del_docx) {
                        $del = @unlink($baseLocation . $del_docx->docx_file_name);
                        $del = @unlink($baseLocation . $del_docx->docx_file_name . '.sig');
                        $del_docx->delete();
                    }
                }
            }
            $docx->created_at = date('Y-m-d H:i:s');
            $docx->recall = $recall;
            $docx->format = $format;
            $docx->complaint_id = $compl_id;
            $docx->user_id = $this->user->id;
            $docx->save();
        }
        $this->view->disable();
        if ($name) {
            $thumbprint = 0;
            if (isset($_POST['applicant_id'])) {
                $applicant_id = $_POST['applicant_id'];
                //"activ = 1 AND applicant_id = $applicant_id "

                $thumbprint = ApplicantECP::findFirst(array(
                    "conditions" => "activ = ?1 AND applicant_id = ?2",
                    "bind" => [
                        1 => 1,
                        2 => $applicant_id,
                    ],
                    'order' => 'id DESC'
                ));
                $thumbprint = $thumbprint->thumbprint;
            }
            $data = file_get_contents($baseLocation . $name);
            $File_data = base64_encode($data);
            echo json_encode([$File_data, $thumbprint, $name]);
        } else {
            echo 'error';
        }
        die();
        //0190300004615000296
        //  skyColor
    }

    public function saveBlobFileAction()
    {
        $name = false;
        $format = 1;
        $recall = 0;
        $recall = $this->request->get('recall');
        if ($this->request->hasFiles() == true) {
            $baseLocation = 'files/generated_complaints/user_' . $this->user->id . '/';
            foreach ($this->request->getUploadedFiles() as $file) {
                if (strlen($file->getName())) {
                    if (!file_exists($baseLocation)) {
                        mkdir($baseLocation, 0777, true);
                    }
                    if (empty($recall)) {
                        $unformatted = isset($_GET['unformatted']) ? 'unformatted_' : '';
                        if ($unformatted == 'unformatted_') {
                            $format = 0;
                        }
                        $name = 'complaint_' . $unformatted . time() . '.docx';
                        $file->moveTo($baseLocation . $name);
                    } else {
                        $unformatted = isset($_GET['unformatted']) ? 'unformatted_' : '';
                        $name = 'recall_' . $unformatted . time() . '.docx';
                        $recall = 1;
                        $file->moveTo($baseLocation . $name);
                    }
                }
            }
            $docx = new DocxFiles();
            if (!empty($recall)) {
                $docx->complaint_id = $this->request->get('complaint_id');
            }
            $docx->docx_file_name = $name;


            $tempCompPost = $this->request->getPost('complaint_id');
            $tempCompGet = $this->request->getQuery('complaint_id');
            if (is_numeric($tempCompPost)) {
                $compl_id = $tempCompPost;
            } elseif (is_numeric($tempCompGet)) {
                $compl_id = $tempCompGet;
            }

            $docx->complaint_name = $this->request->getPost('complaint_name');
            if (isset($compl_id) && $compl_id != 'undefined') {
                $delete_docx = DocxFiles::find("complaint_id = $compl_id");
                if (count($delete_docx) >= 2) {
                    foreach ($delete_docx as $del_docx) {
                        $del = @unlink($baseLocation . $del_docx->docx_file_name);
                        $del = @unlink($baseLocation . $del_docx->docx_file_name . '.sig');
                        $del_docx->delete();
                    }
                } else {
                    $delete_docx = DocxFiles::find("complaint_id = $compl_id AND recall = 1");
                    foreach ($delete_docx as $del_docx) {
                        $del = @unlink($baseLocation . $del_docx->docx_file_name);
                        $del = @unlink($baseLocation . $del_docx->docx_file_name . '.sig');
                        $del_docx->delete();
                    }
                }
            }
            $docx->created_at = date('Y-m-d H:i:s');
            $docx->recall = $recall;
            $docx->format = $format;
            $docx->complaint_id = $compl_id;
            $docx->user_id = $this->user->id;
            $docx->save();
        }
        $this->view->disable();
        if ($name) {
            $thumbprint = 0;
            if (isset($_POST['applicant_id'])) {
                $applicant_id = $_POST['applicant_id'];
                //"activ = 1 AND applicant_id = $applicant_id "

                $thumbprint = ApplicantECP::findFirst(array(
                    "conditions" => "activ = ?1 AND applicant_id = ?2",
                    "bind" => [
                        1 => 1,
                        2 => $applicant_id,
                    ],
                    'order' => 'id DESC'
                ));
                $thumbprint = $thumbprint->thumbprint;
            }
            $data = file_get_contents($baseLocation . $name);
            $File_data = base64_encode($data);
            echo json_encode([$File_data, $thumbprint, $name]);
        } else {
            echo 'error';
        }
        die();
        //0190300004615000296
        //  skyColor
    }

    public function signatureAction()
    {
        $signature = $this->request->getPost('signature');
        $signFileOriginName = $this->request->getPost('signFileOriginName');
        $baseLocation = 'files/generated_complaints/user_' . $this->user->id . '/';
        // unlink($baseLocation . $signFileOriginName.'sig');

        $file = file_get_contents($baseLocation . $signFileOriginName);
        $sendData = array(
            "method" => 'Signature.verifyMessageSignature',
            "id" => 1,
            "params" => array(
                "signature" => $signature,
                "message" => base64_encode($file)
            )
        );

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, 'http://185.20.225.233/api/v1/json');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($sendData));
        $out = curl_exec($curl);

        file_put_contents($baseLocation . $signFileOriginName . '.sig', base64_decode($signature));
        /*if(preg_match('/recall/', $signFileOriginName)){

            $this->SendToUfas(array(
                '../public/'.$baseLocation.$signFileOriginName.'.sig',
                '../public/'.$baseLocation.$signFileOriginName,
            ));
        }*/

        echo 'done';
        exit;
    }

    public function addAction()
    {
        $this->setMenu();
        $category = new Category();
        $arguments = $category->getArguments();
        $ufas = Ufas::find();

        $this->view->edit_mode = 0;
        $this->view->ufas = $ufas;
        $this->view->checkUser = $this->checkUser();
        $this->view->arguments = $arguments;
    }

    public function deleteAction($id)
    {
        $complaint = Complaint::findFirst($id);
        if (!$complaint || !$complaint->checkComplaintOwner($id, $this->user->id))
            return $this->forward('complaint/index');

        if ($complaint != false) {
            $complaint->delete();
        }
        $this->flashSession->success('Жалоба успешно удалена');
        return $this->response->redirect('/complaint/index');
    }

    public function createAction()
    {
        if (!$this->request->isPost()) {
            echo 'error';
            exit;
        }
        $users_arguments_ = [];
        $data = $this->request->getPost();
        $data['auctionData'] = explode('&', $data['auctionData']);
        $users_arguments = explode('_?_', $data['arguments_data']);

        $ufas_id = null;
        if (isset($data['ufas_id']) && is_numeric($data['ufas_id'])) {
            $ufas_id = Ufas::findFirst(array(
                "number={$data['ufas_id']}"
            ));
            if ($ufas_id) $ufas_id = $ufas_id->id;
        }
        $data['ufas_id'] = $ufas_id;


        unset($users_arguments[count($users_arguments) - 1]);
        foreach ($users_arguments as $key => $row) {
            $users_arguments[$key] = explode('?|||?', $row);
        }
        foreach ($users_arguments as $key => &$row) {
            //$cnt = count($row);
            foreach ($row as $data_) {
                $data_ = explode('===', $data_);
                $users_arguments_[$key][$data_[0]] = $data_[1];
                /*if (isset($users_arguments_[$key]['argument_id']) && $users_arguments_[$key]['argument_id'] == 'just_text') {
                    $data['complaint_text'] = $data_[1];
                    $data['complaint_text_order'] = $users_arguments_[$key]['order'];
                }*/
            }
            /*for ($ind = 0; $ind < $cnt; $ind++) {
                unset($row[$ind]);
            }*/
            //$users_arguments[$key] = explode('?|||?', $row);
        }
        foreach ($data['auctionData'] as $value) {
            $value = explode('=', $value);
            $data["{$value[0]}"] = $value[1];
        }
        $complaint = new Complaint();
        $complaint->addComplaint($data);

        if ($complaint->save() == false) {
            //$this->flashSession->error('Не выбран заявитель');
            foreach ($complaint->getMessages() as $message) {
                $this->flashSession->error($message);
            }
            return $this->response->redirect('/complaint/add');
            //$response = array('result' => 'error', 'message' => 'Ошибка при попытке сохранения жалобы');
        } else {
            $allow = TRUE;

            // Save users arguments.
            foreach ($users_arguments_ as $argument) {
                if ($argument['argument_id'] != 'just_text') {
                    $u_arg = new UsersArguments();
                    $u_arg->complaint_id = $complaint->id;
                    $u_arg->argument_id = $argument['argument_id'];
                    $u_arg->argument_order = $argument['order'];
                    $u_arg->text = $argument['argument_text'];
                    $u_arg->argument_category_id = $argument['category_id'];
                    $u_arg->save();
                }
            }

            // Check all files with needed rules.
            if ($this->request->hasFiles() == true) {
                $files_model = new Files();
                if (!$files_model->checkAllFiles($this->request)) {
                    $allow = FALSE;
                }
            }
            if ($allow) {
                $saved_files = array();
                if ($this->request->hasFiles() == true) {
                    $baseLocation = 'files/complaints/';
                    foreach ($this->request->getUploadedFiles() as $file) {
                        if (strlen($file->getName())) {
                            $applicant_file = new Files();
                            $name = explode('.', $file->getName())[0] . '_' . time() . '.' . explode('.', $file->getName())[1];
                            //$name = iconv("UTF-8", "cp1251", $name);
                            $applicant_file->file_path = Translit::rusToEng($name);
                            $applicant_file->file_size = round($file->getSize() / 1024, 2);
                            $applicant_file->file_type = $file->getType();
                            $applicant_file->save();
                            $saved_files[] = $applicant_file->id;
                            //Move the file into the application
                            $file->moveTo($baseLocation . Translit::rusToEng($name));
                        }
                    }
                }
                $complaint->fid = serialize($saved_files);
                //$this->flashSession->error($applicant->fid);
                $complaint->user_id = $this->user->id;
                $complaint->save();
                $docx_s = DocxFiles::find("complaint_name = '{$complaint->complaint_name}'");
                foreach ($docx_s as $docx) {
                    $docx->complaint_id = $complaint->id;
                    $docx->save();
                }
            }
            Log::addAdminLog("Создание жалобы", "Добавлена жалоба {$complaint->id}", $this->user, null, 'пользователь');
            $this->flashSession->success('Жалоба сохранена');
            echo json_encode(array(
                'complaint' => array(
                    'id' => $complaint->id
                )
            ));
            exit;
            //return $this->response->redirect('complaint/edit/' . $complaint->id . '?action=edit');
            //$response = array('result' => 'success', 'id' => $complaint->id);
        }
        /*header('Content-type: application/json');
        echo json_encode($response);
        exit;*/
    }

    public function updateAction()
    {
        if (!$this->request->isPost()) {
            echo 'error';
            exit;
        }
        $users_arguments_ = [];
        $data = $this->request->getPost();
        $users_arguments = explode('_?_', $data['arguments_data']);
        unset($users_arguments[count($users_arguments) - 1]);
        foreach ($users_arguments as $key => $row) {
            $users_arguments[$key] = explode('?|||?', $row);
        }

        foreach ($users_arguments as $key => &$row) {
            foreach ($row as $data_) {
                $data_ = explode('===', $data_);

                /* if ($data_[0] == 'argument_text') {
                     preg_match_all('/<img.*?src\s*=(.*?)>/', $data_[1], $out);
                     if (count($out[1])) {
                         foreach ($out[1] as $key => $image) {
                             $explode = explode(" ", $image);
                             $image = trim($explode[0], '"');

                             $file_name = time() + rand();

                             $data_[1] = str_replace($out[0][$key], '<img src="' . $_SERVER['DOCUMENT_ROOT'] . "/files/generated_complaints/user_" . $this->user->id . "/" . $this->save_base64_image($image, time() + rand(), $_SERVER['DOCUMENT_ROOT'] . "/files/generated_complaints/user_" . $this->user->id . "/") . '"><br/>', $data_[1]);
                         }
                     }
                 }*/

                $users_arguments_[$key][$data_[0]] = $data_[1];
                /*if (isset($users_arguments_[$key]['argument_id']) && $users_arguments_[$key]['argument_id'] == 'just_text') {
                    $data['complaint_text'] = $data_[1];
                    $data['complaint_text_order'] = $users_arguments_[$key]['order'];
                }*/
            }
        }
        $complaint = Complaint::findFirstById($data['update-complaint-id']);
        if ($complaint) {
            $complaint->complaint_name = $data['complaint_name'];
            //$complaint->complaint_text = $data['complaint_text'];
            //$complaint->complaint_text_order = $data['complaint_text_order'];
            $complaint->complaint_text = $data['complaint_text'];
            $complaint->complaint_text_order = $data['complaint_text_order'];
            $ufas = Ufas::findFirst(array(
                "number = {$data['ufas_id']}"
            ));
            if ($ufas) {
                $complaint->ufas_id = $ufas->id;
            }
        }

        if ($complaint->update() == false) {
            //$this->flashSession->error('Не выбран заявитель');
            foreach ($complaint->getMessages() as $message) {
                $this->flashSession->error($message);
            }
            return $this->response->redirect('/complaint/edit/' + $data['update-complaint-id']);
        } else {
            $allow = TRUE;

            // Remove all arguments
            $arguments_delete = UsersArguments::find(
                array(
                    'complaint_id = :complaint_id:',
                    'bind' => [
                        'complaint_id' => $data['update-complaint-id'],
                    ]
                )
            )->delete();

            // Save users arguments.
            foreach ($users_arguments_ as $argument) {
                if ($argument['argument_id'] != 'just_text') {
                    $u_arg = new UsersArguments();
                    $u_arg->complaint_id = $complaint->id;
                    $u_arg->argument_id = $argument['argument_id'];
                    $u_arg->argument_order = $argument['order'];
                    $u_arg->text = $argument['argument_text'];
                    $u_arg->argument_category_id = $argument['category_id'];
                    $u_arg->save();
                }
            }

            $data = $_FILES;
            // Check all files with needed rules.
            if ($this->request->hasFiles() == true) {
                $files_model = new Files();
                if (!$files_model->checkAllFiles($this->request)) {
                    $allow = FALSE;
                }
            }
            if ($allow) {
                $saved_files = array();
                if ($this->request->hasFiles() == true) {
                    $baseLocation = 'files/complaints/';
                    foreach ($this->request->getUploadedFiles() as $file) {
                        if (strlen($file->getName())) {
                            $applicant_file = new Files();
                            $name = explode('.', $file->getName())[0] . '_' . time() . '.' . explode('.', $file->getName())[1];
                            //$name = iconv("UTF-8", "cp1251", $name);
                            $applicant_file->file_path = Translit::rusToEng($name);
                            $applicant_file->file_size = round($file->getSize() / 1024, 2);
                            $applicant_file->file_type = $file->getType();
                            $applicant_file->save();
                            $saved_files[] = $applicant_file->id;
                            //Move the file into the application
                            $file->moveTo($baseLocation . Translit::rusToEng($name));
                        }
                    }
                }
                $old_files = unserialize($complaint->fid);
                if (count($old_files)) {
                    $complaint->fid = serialize(array_merge($old_files, $saved_files));
                } else {
                    $complaint->fid = serialize($saved_files);
                }
                $complaint->save();
                $docx_s = DocxFiles::find("complaint_name = '{$complaint->complaint_name}'");
                foreach ($docx_s as $docx) {
                    $docx->complaint_id = $complaint->id;
                    $docx->save();
                }
            }

            $this->flashSession->success('Жалоба обновлена');
            //return $this->response->redirect('complaint/edit/' . $complaint-
            echo json_encode(array(
                'status' => 'ok'
            ));
            exit;
        }
    }

    public function askQuestionAction()
    {
        $question = $this->request->getPost('new-question');
        $complaint_id = $this->request->getPost('complaint_id');
        if (isset($question) && strlen($question) && isset($complaint_id) && $complaint_id) {
            $new_question = new Question();
            $new_question->user_id = $this->user->id;
            $new_question->complaint_id = $complaint_id;
            $new_question->text = $question;
            $new_question->date = date('Y-m-d H:i:s');
            $new_question->is_read = 'n';
            $new_question->save();
            $this->flashSession->success('Ваш вопрос отправлен юристу');
            return $this->response->redirect('/complaint/edit/' . $complaint_id);
        }
        $this->flashSession->error('Поле с вопросом не заполнено');
        return $this->response->redirect('/complaint/edit/' . $complaint_id);
    }

    public function statusAction()
    {
        if (!$this->request->isPost()) {
            echo 'error';
            exit;
        }
        $data = $this->request->getPost();
        $complaint = new Complaint();
        $result = $complaint->changeStatus($data['status'], json_decode($data['complaints']), $this->user->id);
        //$this->flashSession->success('Копия жалобы создана');
        echo $result;
        exit;
    }

    function isComplaintNameUnicAction()
    {
        $this->view->disable();
        $complaint_name = $this->request->get('complaint_name');
        $complaint_id = $this->request->get('complaint_id');
        $and_complaint_where = '';
        if (isset($complaint_id)) {
            $and_complaint_where = " AND id != {$complaint_id}";
        }
        $response['name_unic'] = TRUE;
        if ($complaint_name) {
            $db = $this->getDi()->getShared('db');
            $result = $db->query("SELECT id FROM complaint WHERE complaint_name = '{$complaint_name}'{$and_complaint_where}");
            $id = $result->fetch();
            if ($id) {
                $response['name_unic'] = FALSE;
            }
        }
        header('Content-type: application/json');
        echo json_encode($response);
        die();
    }

    public function recallAction($id)
    {

        if ($id == '0') {
            $data = $this->request->getPost();
            $data = json_decode($data['complaints']);
        } else {
            $data = array($id);
        }

        //$complaint = new Complaint();
        //$complaint->changeStatus('recalled', $data, $this->user->id); //todo: refactor to this later
        foreach ($data as $v) { //todo: whole array can be passed in $complaint->changeStatus
            $complaint = Complaint::findFirstById($v);
            if (!$complaint)
                return $this->forward('complaint/index');
            if (!$complaint->checkComplaintOwner($v, $this->user->id))
                return $this->forward('complaint/index');
            if ($complaint->status == 'submitted') {
                $complaint = new Complaint();
                $complaint->changeStatus('recalled', [$v], $this->user->id);
            }
        }
        if ($id == '0') { //todo: maby we need json response
            echo 'true';
            exit;
        } else
            header('Location: http://' . $_SERVER['HTTP_HOST'] . '/complaint/edit/' . $id);


    }

    public function saveAction()
    {
        $data = $this->request->getPost();
        $complaint = Complaint::findFirstById($data['complaint_id']);
        if (!$complaint || $complaint->status != 'draft' || !$complaint->checkComplaintOwner($data['complaint_id'], $this->user->id)) {
            echo 'error';
            exit;
        }
        echo $complaint->saveComplaint($data);
        exit;

    }

    /* ADD COMPLICANT */
    public function ajaxStepsAddComplaintAction()
    {
        try {
            $data = array();
            $result = array(
                "cat_arguments" => array(),
                "arguments" => array(),
                "date" => 0
            );
            $CurrentStep = $this->request->get('step');
            $data['type'] = $this->request->getPost('type');
            $data['dateOff'] = $this->request->getPost('dateoff');

            //1 - пользователь выбрал обязательный довод  // 0 - не выбрал
            $data['checkRequired'] = $this->request->getPost('checkrequired');

            if (!$CurrentStep || !is_numeric($CurrentStep)) throw new Exception('bad step');
            if (!$data['type'] || !$this->checkTypePurchase($data['type'])) throw new Exception('bad type');
            if (!$data['dateOff'] || trim($data['dateOff']) == '') throw new Exception('bad date');

            // 0 - не просрочено // 1 - просрочено
            $data['checkDate'] = $this->checkDateEndSendApp($data['dateOff'], $result);

            switch ($CurrentStep) {
                case self::STEP_ONE:
                    $cat = new ArgumentsCategory();
                    $cat_arguments = $cat->getCategoryNotEmpty($data['type'], $data['checkDate'], $data['checkRequired']);
                    $temp_name = array();

                    foreach ($cat_arguments as $cat) {
                        if (!in_array($cat->lvl1, $temp_name)) {
                            $temp_name[] = $cat->lvl1;
                            $result['cat_arguments'][] = array(
                                'id' => $cat->lvl1_id,
                                'name' => $cat->lvl1,
                                'required' => $cat->lvl1_required,
                                'parent_id' => 0
                            );
                        }
                    }
                    echo json_encode($result);
                    break;
                case self::STEP_TWO:
                    $parent_id = $this->request->getPost('id');
                    if (!$parent_id || !is_numeric($parent_id)) throw new Exception('bad data');

                    $cat = new ArgumentsCategory();
                    $cat_arguments = $cat->getCategoryNotEmpty($data['type'], $data['checkDate'], $data['checkRequired']);
                    $temp_name = array();

                    foreach ($cat_arguments as $cat) {
                        if ($cat->lvl1_id == $parent_id) {
                            if (!in_array($cat->lvl2, $temp_name)) {
                                $temp_name[] = $cat->lvl2;
                                $result["cat_arguments"][] = array(
                                    "id" => $cat->lvl2_id,
                                    "name" => $cat->lvl2,
                                    'required' => $cat->lvl2_required,
                                    "parent_id" => $cat->lvl1_id,
                                );
                            }
                        }
                    }
                    echo json_encode($result);
                    break;
                case self::STEP_THREE:
                    $id = $this->request->getPost('id');

                    if (!$id || !is_numeric($id)) throw new Exception('bad data');
                    $parent_id = ArgumentsCategory::findFirst($id);
                    if (!$parent_id) throw new Exception('no cat');

                    //Получить не пустые категории (в которых есть доводы)
                    $cat_arguments = new Builder();
                    $cat_arguments->getDistinct();
                    $cat_arguments->addFrom('Multiple\Frontend\Models\ArgumentsCategory', 'ArgumentsCategory');
                    $cat_arguments->rightJoin('Multiple\Frontend\Models\Arguments', "ArgumentsCategory.id = category_id AND type LIKE '%{$data['type']}%'");
                    $cat_arguments->where("parent_id = {$id}");
                    if ($data['checkDate'] == 1 && $data['checkRequired'] == 0) {
                        $cat_arguments->andWhere("ArgumentsCategory.required = 1");
                        $cat_arguments->andWhere("Multiple\Frontend\Models\Arguments.required = 1");
                    } else if ($data['checkDate'] == 0) {
                        $cat_arguments->andWhere("ArgumentsCategory.required = 0");
                        $cat_arguments->andWhere("Multiple\Frontend\Models\Arguments.required = 0");
                    }
                    $cat_arguments->groupBy('ArgumentsCategory.id');
                    $cat_arguments = $cat_arguments->getQuery()->execute();

                    //Если категорий нет, то получаем доводы и добавляем их в результирующий массив $result
                    if (count($cat_arguments) == 0) {
                        $arguments = Arguments::query();
                        $arguments->where("category_id = {$id}");
                        $this->showRequiredOrNotRequired($arguments, $data);
                        $arguments->andWhere("type LIKE '%{$data['type']}%'");
                        $arguments = $arguments->execute();
                        $this->setArgumentsInResult($arguments, $data['type'], $result);
                    } else {
                        foreach ($cat_arguments as $cat) {
                            $result['cat_arguments'][] = array(
                                'id' => $cat->id,
                                'name' => $cat->name,
                                'required' => $cat->required,
                                'parent_id' => $cat->parent_id,
                            );
                        }
                    }
                    echo json_encode($result);
                    break;
                case self::STEP_FOUR:
                    $id = $this->request->getPost('id');
                    if (!$id || !is_numeric($id)) throw new Exception('bad data');

                    $arguments = Arguments::query();
                    $arguments->where("category_id = {$id}");
                    $this->showRequiredOrNotRequired($arguments, $data);
                    $arguments->andWhere("type LIKE '%{$data['type']}%'");
                    $arguments = $arguments->execute();

                    $this->setArgumentsInResult($arguments, $data['type'], $result);
                    echo json_encode($result);
                    break;
                case self::STEP_SEARCH:
                    $search = $this->request->getPost('search');
                    $search = (!empty($search)) ? trim($search) : '';

                    if (empty($search)) {
                        echo json_encode($result);
                        exit;
                    }

                    $arguments = Arguments::query();
                    $arguments->where('name LIKE :name:', array('name' => '%' . $search . '%'));
                    $this->showRequiredOrNotRequired($arguments, $data);
                    $arguments->andWhere("type LIKE '%{$data['type']}%'");
                    $arguments = $arguments->execute();

                    $this->setArgumentsInResult($arguments, $data['type'], $result);
                    echo json_encode($result);
                    break;
            }
        } catch (Exception $e) {
            echo json_encode(array(
                "error" => $e->getMessage()
            ));
        }
        exit;
    }

    private function checkTypePurchase($type)
    {
        $checkType = false;
        switch ($type) {
            case 'electr_auction':
                $checkType = true;
                break;
            case 'concurs':
                $checkType = true;
                break;
            case 'kotirovok':
                $checkType = true;
                break;
            case 'offer':
                $checkType = true;
                break;
        }
        return $checkType;
    }

    private function setArgumentsInResult($arguments, $type, &$result)
    {
        foreach ($arguments as $argument) {
            $result['arguments'][] = array(
                'id' => $argument->id,
                'text' => $argument->text,
                'name' => $argument->name,
                'category_id' => $argument->category_id,
                'comment' => $argument->comment,
                'required' => $argument->required,
                'type' => ($argument->type != '') ? explode(',', $argument->type) : array()
            );
        }
    }

    private function checkDateEndSendApp($dateOff, &$result = false)
    {
        $dateOff = strtotime($dateOff);
        $nowTime = strtotime("now");

        if ($nowTime > $dateOff) {
            if ($result) {
                $result['date'] = 1;
            }
            return 1;
        }
        return 0;
    }

    private function showRequiredOrNotRequired($arguments, $data)
    {
        if ($data['checkDate'] == 1 && $data['checkRequired'] == 0) $arguments->andWhere("required = 1");
        if ($data['checkDate'] == 0) $arguments->andWhere("required = 0");
    }

    private function checkUser()
    {
        $user = User::findFirstById($this->session->get('auth')['id']);

        if (!$user->conversion || !$user->mobile_phone) {
            return 1;
        }
        return 0;
    }

    public function checkDateComplaintAction()
    {
        $complaint_id = $this->request->getPost('complaint_id');
        $okonchanie_podachi = $this->request->getPost('okonchanie_podachi');
        $okonchanie_rassmotreniya = $this->request->getPost('okonchanie_rassmotreniya');
        if (!$okonchanie_rassmotreniya) {
            $okonchanie_rassmotreniya = $this->request->getPost('data_rassmotreniya');
        }
        if (!$okonchanie_rassmotreniya) {
            $okonchanie_rassmotreniya = $this->request->getPost('vskrytie_konvertov');
        }


        $complaint = Complaint::findFirst($complaint_id);

        $currentDate = new \DateTime('now');
        $okonchanie_podachi = new \DateTime($okonchanie_podachi);
        $complaintDate = new \DateTime($complaint->date);


        if ($complaintDate < $okonchanie_podachi) {
            if ($currentDate < $okonchanie_podachi) {
                echo json_encode([
                    'rule' => 1,
                    'status' => 0,
                ]);
                exit;
            } elseif ($currentDate > $okonchanie_podachi) {
                echo json_encode([
                    'rule' => 1,
                    'status' => 1,
                ]);
                exit;
            }
        } else {
            $calendar = new Calendar(new BasicDataRu(), 10);
            $result = $calendar->checkDateAddComplaint($okonchanie_rassmotreniya);
            echo json_encode(array(
                'status' => $result,
                'rule' => 2,
            ));
            exit;
        }
    }


    public function checkDateOnRecallComplaintAction()
    {
        $idComp = $this->request->getPost('date');
        if (empty($idComp)) {
            echo json_encode(array(
                'error' => 'empty'
            ));
            exit;
        }

        $complaint = Complaint::findFirst($idComp);

        $calendar = new Calendar(new BasicDataRu(), 5);
        $result = $calendar->checkDateAbortComplaint($complaint->date, 5);
        echo json_encode(array(
            'status' => $result,
            'complaint' => array(
                'auction_id' => $complaint->auction_id,
                'name' => $complaint->complaint_name,
            )
        ));
        exit;
    }

    public function getInfoComplaintAction()
    {
        $data = $this->request->getPost('date');
        $complaint = Complaint::findFirst($data);
        $applicant = null;
        $ecp = null;
        $ufas = null;
        if ($complaint) {
            $applicant = Applicant::findFirst($complaint->applicant_id);
            $ecp = ApplicantECP::findFirst(array(
                "applicant_id = " . $complaint->applicant_id
            ));
            $ufas = Ufas::findFirst($complaint->ufas_id);
        }

        $result = array(
            'applicant_name' => $applicant->name_short,
            'applicant_fio' => $applicant->fio_applicant,
            'applicant_id' => $applicant->id,
            'applicant_address' => $applicant->address,
            'applicant_phone' => $applicant->telefone,
            'applicant_email' => $applicant->email,
            'applicant_position' => $applicant->position,
            'auction_id' => $complaint->auction_id,
            'ufas_name' => $ufas->name,
            'date_create' => $complaint->date_submit,
            'date_now' => date('Y-m-d H:m'),
            'thumbprint' => $ecp->thumbprint,
        );

        echo json_encode($result);
        exit;
    }

    private function SendToUfas($files, $ufasEmail, $subject, $content, $additionally=null)
    {
        $message = $this->mailer->createMessage()
            ->to($ufasEmail)
            ->bcc($this->adminsEmails['ufas'])
            ->subject($subject)
            ->content($content);
        foreach ($files as $key) {
            $message->attachment($key);
        }
        Log::addAdminLog("Отправка в УФАС", $content, $this->user, $additionally, 'пользователь');
        $message->send();
    }

    public function sendComplaintToUfasAction()
    {
        $status = 'ok';

        $compId = $this->request->getPost('complId');
        $file = DocxFiles::findFirst(array(
            "complaint_id = {$compId} AND format = 1"
        ));

        $complaint = new Complaint();
        $complaint->changeStatus('submitted', array($compId), $this->user->id);
        $complaint = Complaint::findFirst($compId);

        $appFiles = Applicant::findFirst($complaint->applicant_id);
        $appFiles = unserialize($appFiles->fid);

        $attached = array(
            '../public/files/generated_complaints/user_' . $this->user->id . '/' . $file->docx_file_name . '.sig',
            '../public/files/generated_complaints/user_' . $this->user->id . '/' . $file->docx_file_name
        );

        $compFiles = unserialize($complaint->fid);
        foreach ($compFiles as $compfile) {
            $tempFile = Files::findFirst($compfile);
            $attached[] = '../public/files/complaints/' . $tempFile->file_path;
        }

        foreach ($appFiles as $file) {
            $tempFile = Files::findFirst($file);
            $attached[] = '../public/files/applicant/' . $tempFile->file_path;
        }

        $ufas = Ufas::findFirst($complaint->ufas_id);

        $content = 'Добрый день. <br/>
В соответствии со ст. 105 Федерального закона от 05.04.2013 № 44-ФЗ «О контрактной системе в сфере закупок товаров, работ, услуг для обеспечения государственных и муниципальных нужд» направляем в ваш адрес жалобу на закупку, опубликованную на официальном сайте Единой информационной системы в сфере закупок.<br/>
Прилагаемый файл жалобы в формате «docx» подписан электронной подписью заявителя.<br/><br/>
Формат подписи - квалифицированная отсоединенная подпись - «Разновидность электронной подписи, при создании которой, файл подписи создается отдельно от подписываемого файла. Поскольку подписываемый файл никак не изменяется, его можно читать, не прибегая к специальным программам, работающим с электронной подписью. Для проверки подписи нужно будет использовать программы либо сервисы, работающие с электронной подписью. При этом входными данными для таких программ будут служить файл с электронной подписью и подписанный ей файл.»<br/><br/>
Действительность подписи может быть проверена следующими способами:<br/><br/>
1. Онлайн - на сайте ГОСУСЛУГИ -  https://www.gosuslugi.ru/pgu/eds/. Для проверки выбрать раздел "отсоединенная, в формате PKCS#7», загрузить 2 файла (файл жалобы и файл подписи), после чего нажать «проверить»<br/>
2. Онлайн - https://crypto.kontur.ru/verify - следуя инструкции можно проверить подпись в 2 клика<br/>
3. Локально - при помощи БЕСПЛАТНОЙ программы http://www.taxcom.ru/upload/help/documents/uslugi/CryptoLine.zip <br/>
4. Локально - при помощи БЕСПЛАТНОЙ программы http://cryptoarm.ru/bitrix/redirect.php?event1=download&event2=cryptoarm5&goto=http://www.trusted.ru/wp-content/uploads/trusteddesktop.exe<br/>';

        try {
            $this->SendToUfas($attached, $ufas->email, 'Жалоба 44-ФЗ', $content, $complaint->auction_id);
            $status = 'ok';
        } catch (\Exception $e) {
            $status = 'error';
        }

        echo json_encode(array(
            'status' => $status,
            'complaint' => array(
                'auction_id' => $complaint->auction_id,
            )
        ));
        exit;
    }

    public function recallChangeStaAndSendUfasAction()
    {
        $compId = $this->request->getPost('complaint_id');
        $arrId = array();
        $arrId[] = $compId;
        $complaint = new Complaint();

        $file = DocxFiles::findFirst(array(
            "complaint_id = {$compId} AND recall=1"
        ));

        $attached = array(
            '../public/files/generated_complaints/user_' . $this->user->id . '/' . $file->docx_file_name . '.sig',
            '../public/files/generated_complaints/user_' . $this->user->id . '/' . $file->docx_file_name,
        );

        $complaint = Complaint::findFirst($compId);


        $appFiles = Applicant::findFirst($complaint->applicant_id);
        $appFiles = unserialize($appFiles->fid);

        foreach ($appFiles as $file) {
            $tempFile = Files::findFirst($file);
            $attached[] = '../public/files/applicant/' . $tempFile->file_path;
        }

        $ufas = Ufas::findFirst($complaint->ufas_id);


        $content = 'Просим отозвать поданную ранее жалобу на закупку. Прилагаемый файл отзыва жалобы в формате «docx» подписан электронной подписью заявителя.<br/><br/>
Формат подписи - квалифицированная отсоединенная подпись - «Разновидность электронной подписи, при создании которой, файл подписи создается отдельно от подписываемого файла. Поскольку подписываемый файл никак не изменяется, его можно читать, не прибегая к специальным программам, работающим с электронной подписью. Для проверки подписи нужно будет использовать программы либо сервисы, работающие с электронной подписью. При этом входными данными для таких программ будут служить файл с электронной подписью и подписанный ей файл.»<br/><br/>
Действительность подписи может быть проверена следующими способами:<br/>
1. Онлайн - на сайте ГОСУСЛУГИ -  https://www.gosuslugi.ru/pgu/eds/. Для проверки выбрать раздел "отсоединенная, в формате PKCS#7», загрузить 2 файла (файл жалобы и файл подписи), после чего нажать «проверить»<br/>
2. Онлайн - https://crypto.kontur.ru/verify - следуя инструкции можно проверить подпись в 2 клика<br/>
3. Локально - при помощи БЕСПЛАТНОЙ программы http://www.taxcom.ru/upload/help/documents/uslugi/CryptoLine.zip <br/>
4. Локально - при помощи БЕСПЛАТНОЙ программы http://cryptoarm.ru/bitrix/redirect.php?event1=download&event2=cryptoarm5&goto=http://www.trusted.ru/wp-content/uploads/trusteddesktop.exe<br/>';


        if ($complaint->status != 'recalled') {
            $this->SendToUfas($attached, $ufas->email, 'Отзыв жалобы 44-ФЗ', $content);
        }

        $complaint->changeStatus('recalled', $arrId, $this->user->id);
        echo json_encode(array(
            'status' => 'ok'
        ));
        exit;
    }

    public function uploadAction()
    {
        ini_set('display_errors', true);
        $fileName = $_FILES['upload']['name'];
        $fileType = $_FILES['upload']['type'];
        $fileError = $_FILES['upload']['error'];
        $fileTmpName = $_FILES['upload']['tmp_name'];
        if ($fileError == UPLOAD_ERR_OK) {
            if (is_uploaded_file($fileTmpName)) {
                #todo: если имена совпадают, то нужно генерить случайное
                if (move_uploaded_file($fileTmpName, $_SERVER['DOCUMENT_ROOT'] . "/public/files/generated_complaints/user_" . $this->user->id . "/" . $fileName)) {
                    $url = '/files/generated_complaints/user_' . $this->user->id . "/" . $fileName;

                    $this->create_thumbnail($_SERVER['DOCUMENT_ROOT'] . "/public/files/generated_complaints/user_" . $this->user->id . "/" . $fileName, 'true', 700);

                    $funcNum = $_GET['CKEditorFuncNum'];
                    // Optional: instance name (might be used to load a specific configuration file or anything else).
                    $CKEditor = $_GET['CKEditor'];
                    // Optional: might be used to provide localized messages.
                    $langCode = $_GET['langCode'];

                    echo "<script type='text/javascript'>window.parent.CKEDITOR.tools.callFunction($funcNum, '$url', '$message');</script>";
                }
            }
        }
        exit();
    }

    protected function create_thumbnail($path, $save, $width)
    {
        $info = \getimagesize($path); //получаем размеры картинки и ее тип
        $size = array($info[0], $info[1]); //закидываем размеры в массив

        if ($size[0] > 700) {
            $width = 700;
            $height = ($width / $size[0]) * $size[1];
        } else {
            $width = $size[0];
            $height = $size[1];
        }

        //В зависимости от расширения картинки вызываем соответствующую функцию
        if ($info['mime'] == 'image/png') {
            $src = \imagecreatefrompng($path); //создаём новое изображение из файла
        } else if ($info['mime'] == 'image/jpeg') {
            $src = \imagecreatefromjpeg($path);
        } else if ($info['mime'] == 'image/gif') {
            $src = \imagecreatefromgif($path);
        } else {
            return false;
        }

        $thumb = \imagecreatetruecolor($width, $height); //возвращает идентификатор изображения, представляющий черное изображение заданного размера
        $src_aspect = $size[0] / $size[1]; //отношение ширины к высоте исходника
        $thumb_aspect = $width / $height; //отношение ширины к высоте аватарки

        if ($src_aspect <= $thumb_aspect) {
            //узкий вариант (фиксированная ширина)      
            $scale = $width / $size[0];
            $new_size = array($width, $width / $src_aspect);
            $src_pos = array(0, ($size[1] * $scale - $height) / $scale / 2); //Ищем расстояние по высоте от края картинки до начала картины после обрезки   
        } else if ($src_aspect > $thumb_aspect) {
            //широкий вариант (фиксированная высота)
            $scale = $height / $size[1];
            $new_size = array($height * $src_aspect, $height);
            $src_pos = array(($size[0] * $scale - $width) / $scale / 2, 0); //Ищем расстояние по ширине от края картинки до начала картины после обрезки
        } else {
            //другое
            $new_size = array($width, $height);
            $src_pos = array(0, 0);
        }

        $new_size[0] = max($new_size[0], 1);
        $new_size[1] = max($new_size[1], 1);

        \imagecopyresampled($thumb, $src, 0, 0, $src_pos[0], $src_pos[1], $new_size[0], $new_size[1], $size[0], $size[1]);
        //Копирование и изменение размера изображения с ресемплированием

        if ($save === false) {
            return \imagepng($thumb); //Выводит JPEG/PNG/GIF изображение
        } else {
            return \imagepng($thumb, $path);//Сохраняет JPEG/PNG/GIF изображение
        }
    }
}