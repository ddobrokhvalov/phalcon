<?php
namespace Multiple\Frontend\Controllers;

use Phalcon\Mvc\Controller;
use Multiple\Library\Parser;

class PurchaseController extends Controller
{

    public function getAction()
    {
        if (!$this->request->isPost()) {
            echo 'error';
            exit;
        }
        $postData = $this->request->getPost();
        $auction_id = $postData['auction_id'];
        $purchase = new Parser();
        $data = $purchase->parseAuction($auction_id);
       // var_dump($data); exit;
        echo json_encode($data);

        exit;
    }
    public function getcomplaintAction(){
        $p = new Parser();
     /*   echo '<pre>';
        var_dump($p->getComplaint('0373200099715000611', 'ООО «Агат»', '30.11.2015', '01731000090/02.12.2015/29155'));
        echo '</pre>';
        exit;  */
         if(strlen($_POST['complaintnum'])>5)
             $cn = $_POST['complaintnum'];
        else
            $cn = false;


        $d =$p->getComplaint($_POST['auction_id'], $_POST['zayavitel'], $_POST['date'], $cn);
        $html ="<table>
           <tr>
           <td>Лицо</td><td>".$d['complaint']['lico']."</td>
           </tr>
           <tr>
           <td>номер (id)</td><td>".$d['complaint']['complaint_id']."</td>
           </tr>
           <tr>
           <td>номер</td><td>".$d['complaint']['complaintNum']."</td>
           </tr>
           <tr>
           <td>дата</td><td>".$d['complaint']['date']."</td>
           </tr>
           </table>";
        $html.='<br>Статусы<br>';
        foreach($d['complaint']['status'] as $v){
            $html.='<span>-'.$v.'</span><br>';
        }
        $html .="<br><table>
           <tr>
           <td>Номер</td><td>".$d['complaint']['info']['number']."</td>
           </tr>
           <tr>
           <td>Дата</td><td>".$d['complaint']['info']['date']."</td>
           </tr>
           <tr>
           <td>дата оргинизации</td><td>".$d['complaint']['info']['date_organ']."</td>
           </tr>
           <tr>
           <td>дата сведения</td><td>".$d['complaint']['info']['date_svedeniya']."</td>
           </tr>
            <tr>
           <td>дата решения</td><td>".$d['complaint']['info']['date_resheniya']."</td>
           </tr>
            <tr>
           <td>дата рассмотрения</td><td>".$d['complaint']['info']['date_rassmotreniya']."</td>
           </tr>
            <tr>
           <td>дата обновления</td><td>".$d['complaint']['info']['date_obnovleniya']."</td>
           </tr>
           </table>
        ";
        echo $html; exit;
    }
   public function indexAction(){

   }
}
