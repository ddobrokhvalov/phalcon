<?php
namespace Multiple\Backend\Controllers;

use Phalcon\Mvc\Controller;
use Multiple\Library\Parser;
use Multiple\Backend\Models\Complaint;

class PurchaseController extends Controller
{
    public function parsestatusAction(){

        $comp = new Complaint();
        $complaints = $comp->findForParser();
        $parser = new Parser();


        foreach($complaints as $v){

            $fDate =date('d.m.Y', strtotime($v['date']));

            $result = $parser->getComplaint($v['auction_id'],$v['name_full'], $fDate);
        //  echo '<pre>';
         //   var_dump($result);
         //  echo '</pre>'; echo '<hr>';
            if(isset($result['satus']) && !$result['satus'])
                continue;
           // var_dump($result['complaint']['status'][0]);


            if(isset($result['complaint']['status']) ){
               if(count($result['complaint']['status']) == 1 && strripos ($result['complaint']['status'][0] ,'Рассматривается')!== false
               ){
                   echo 'Жалоба по закупке '.$v['auction_id']. ' На рассмотрении. <br>';
                   $comp->changeStatus('under_consideration',array($v['id']), 'parser');
               }
                if(count($result['complaint']['status']) > 1 && strripos ($result['complaint']['status'][1],'обоснованной' )!== false
                     ){
                    echo 'Жалоба по закупке '.$v['auction_id']. '  Обоснована. <br>';
                    $comp->changeStatus('justified',array($v['id']), 'parser');
                }
                //
                if(count($result['complaint']['status']) == 1 && strripos ($result['complaint']['status'][0] ,'Возвращена')!== false
                    ){
                    echo 'Жалоба по закупке '.$v['auction_id']. ' Возвращена <br>';
                    $comp->changeStatus('returned',array($v['id']), 'parser');
                }

                if(count($result['complaint']['status']) == 1 && strripos ($result['complaint']['status'][0] ,'Отозвана')!== false
                ){
                    echo 'Жалоба по закупке '.$v['auction_id']. ' Отозвана <br>';
                    $comp->changeStatus('recalled',array($v['id']), 'parser');
                }

            }
        }

        echo  "Готово. Жалоб обработано ".count($complaints);
        exit;
    }

    public function getAction()
    {
        if (!$this->request->isPost()) {
            echo 'error';
            exit;
        }
        $postData = $this->request->getPost();
        $auction_id = trim($postData['auction_id']);
        $purchase = new Parser();
        //$data = $purchase->parseAuction($auction_id);
		$xml_data = $purchase->getUrl("http://apis.multitender.ru/gospartner/getInfoByPurchaseNumber44?purchaseNumber=".$auction_id."&apikey=Xoh2liehai8Shoo4Gie1duoG8Iex7sea&format=xml");
		if($xml_data){
			$xml_data = simplexml_load_string($xml_data);
			
			$data = array(
							'contact'=>array(
											'dolg_lico'=>$xml_data->responsibleOrg->contactPerson->__toString(),
											'email'=>$xml_data->responsibleOrg->contactEmail->__toString(),
											'fax'=>$xml_data->responsibleOrg->contactPhone->__toString(),
											'mesto_nahogdeniya'=>$xml_data->responsibleOrg->factAddress->__toString(),
											'name'=>$xml_data->responsibleOrg->fullName->__toString(),
											'pochtovy_adres'=>$xml_data->responsibleOrg->factAddress->__toString(),
											'tel'=>$xml_data->responsibleOrg->contactPhone->__toString(),
											),
							'info'=>array(
											'object_zakupki'=>$xml_data->purchaseObject->__toString(),
											'platform'=>"",
											'type'=>$xml_data->placingWay->__toString(),
											'zakupku_osushestvlyaet'=>$xml_data->customer->fullname->__toString(),
											'zakupku_osushestvlyaet_inn'=>$xml_data->customer->inn->__toString(),
											),
							'procedura'=>array(
											'data_provedeniya'=>$xml_data->procedureInfo->bidding->__toString(),
											'data_rassmotreniya'=>$xml_data->procedureInfo->scoring->__toString(),
											'nachalo_podachi'=>$xml_data->procedureInfo->start->__toString(),
											'okonchanie_podachi'=>$xml_data->procedureInfo->end->__toString(),
											'okonchanie_rassmotreniya'=>$xml_data->procedureInfo->scoring->__toString(),
											'poryadok_podachi'=>"",//"Участник закупки вправе подать заявку на участие в аукционе в любое время, с момента размещения извещения о его проведении в единой информационной системе до предусмотренных документацией о таком аукционе даты и времени окончания срока подачи заявок на участие в аукционе."
											'vremya_provedeniya'=>"Время аукциона не определено",//"09:50"
											'vskrytie_konvertov'=>$xml_data->procedureInfo->opening->__toString(),
											),
							'zakazchik'=>array(
											'tel'=>$xml_data->customer->phone->__toString(),
											'fax'=>$xml_data->customer->phone->__toString(),
											'pochtovy_adres'=>$xml_data->customer->factualAddress->__toString(),
											'email'=>$xml_data->customer->email->__toString(),
											'kontaktnoe_lico'=>$xml_data->customer->contactPerson->__toString(),
											),
							);
			
			if(mb_strtolower( $data['info']['type'], mb_detect_encoding($data['info']['type']) ) == 'открытый конкурс' || mb_strtolower( $data['info']['type'], mb_detect_encoding($data['info']['type']) ) == 'закрытый аукцион'){
				$data['procedura']['data_rassmotreniya'] = $xml_data->procedureInfo->scoring->__toString();
			}
			
			if(mb_strtolower( $data['info']['type'], mb_detect_encoding($data['info']['type']) ) == 'электронный аукцион'){
			}
			
			if(mb_strtolower( $data['info']['type'], mb_detect_encoding($data['info']['type']) ) == 'конкурс с ограниченным участием'){
				$data['procedura']['data_provedeniya'] = $xml_data->procedureInfo->prequalification->__toString();
				$data['procedura']['data_rassmotreniya'] = $xml_data->procedureInfo->scoring->__toString();
			}
			
			if(mb_strtolower( $data['info']['type'], mb_detect_encoding($data['info']['type']) ) == 'запрос котировок'){
			}
			
			if(mb_strtolower( $data['info']['type'], mb_detect_encoding($data['info']['type']) ) == 'повторный конкурс с ограниченным участием'){
				$data['procedura']['data_provedeniya'] = $xml_data->procedureInfo->prequalification->__toString();
			}
			
			if(mb_strtolower( $data['info']['type'], mb_detect_encoding($data['info']['type']) ) == 'закрытый конкурс'){
				$data['procedura']['data_rassmotreniya'] = $xml_data->procedureInfo->scoring->__toString();
			}
			
			if(mb_strtolower( $data['info']['type'], mb_detect_encoding($data['info']['type']) ) == 'закрытый конкурс с ограниченным участием'){
				$data['procedura']['data_provedeniya'] = $xml_data->procedureInfo->prequalification->__toString();
			}
			
			if(mb_strtolower( $data['info']['type'], mb_detect_encoding($data['info']['type']) ) == 'запрос предложений'){
				$data['procedura']['data_rassmotreniya'] = $xml_data->procedureInfo->opening->__toString();
			}
			
			if(mb_strtolower( $data['info']['type'], mb_detect_encoding($data['info']['type']) ) == 'предварительный отбор'){
				$data['procedura']['data_provedeniya'] = $xml_data->procedureInfo->selecting->__toString();
			}
			
			if(mb_strtolower( $data['info']['type'], mb_detect_encoding($data['info']['type']) ) == 'двухэтапный конкурс'){
				$data['procedura']['data_rassmotreniya'] = $xml_data->procedureInfo->scoring->__toString();
			}
		}else{
			$data = $purchase->parseAuction($auction_id);
		}
       // var_dump($data); exit;
        echo json_encode($data);

        exit;
    }
    public function getcomplaintAction(){
        $p = new Parser();
        echo '<pre>';

        var_dump($p->getComplaint('0373200099715000611', 'ООО «Агат»', '30.11.2015', '01731000090/02.12.2015/29155'));
       // var_dump($p->getComplaint('01201000072/26.02.2016/2690', 'ООО «Прайд-А»', '26.02.2016', '01201000072/26.02.2016/2690'));

        echo '</pre>';
        exit;
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
