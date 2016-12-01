<?php

use Multiple\Backend\Models\Complaint;
use Multiple\Backend\Models\Applicant;
use Phalcon\Config\Adapter\Ini as ConfigIni;
require_once('../vendor/autoload.php');

class StatusTask extends \Phalcon\Cli\Task{
    function Parser() {
    libxml_use_internal_errors(true);
    }

    // передавать строку, не число!
    function parseAuction($auctionId) {
        $auction = array('info' => array(), 'contact' => array(), 'procedura' => array(), 'zakazchik' => array());
        $data = $this->getUrl('http://new.zakupki.gov.ru/epz/order/notice/ea44/view/common-info.html?regNumber='.$auctionId);
        libxml_use_internal_errors(true);
        $doc = new DOMDocument();
        $doc->loadHTML($data);
        $xpath = new DOMXpath($doc);

        $auction['info']['type'] = trim($xpath->evaluate('string(//h2[text()="Общая информация о закупке"]/following-sibling::div[1]/table[1]//tr/td[contains(text(),"Способ определения поставщика")]/following-sibling::td[1]/text()[1])'));
        $auction['info']['platform'] = trim($xpath->evaluate('string(//h2[text()="Общая информация о закупке"]/following-sibling::div[1]/table[1]//tr/td[contains(text(),"Наименование электронной площадки")]/following-sibling::td[1]/text()[1])'));
        $auction['info']['zakupku_osushestvlyaet'] = trim($xpath->evaluate('string(//h2[text()="Общая информация о закупке"]/following-sibling::div[1]/table[1]//tr/td[contains(text(),"Закупку осуществляет") or contains(text(),"Размещение осуществляет")]/following-sibling::td[1]/text()[1])'));
        $auction['info']['object_zakupki'] = trim($xpath->evaluate('string(//h2[text()="Общая информация о закупке"]/following-sibling::div[1]/table[1]//tr/td[contains(text(),"Объект закупки")]/following-sibling::td[1]/span[@id="notice_orderName"]/text())'));

        //        $auction['contact']['name'] = trim($xpath->evaluate('string(//h2[text()="Контактная информация"]/following-sibling::div[1]/table[1]//tr/td[contains(text(),"Наименование организации")]/following-sibling::td[1]/text())'));
        $auction['contact']['name'] = trim($xpath->evaluate('string(//h2[text()="Общая информация о закупке"]/following-sibling::div[1]/table[1]//tr/td[contains(text(),"Закупку осуществляет") or contains(text(),"Размещение осуществляет")]/following-sibling::td[1]/a/text())'));
        $auction['contact']['pochtovy_adres'] = trim($xpath->evaluate('string(//h2[text()="Контактная информация" or contains(text(),"Информация об организации")]/following-sibling::div[1]/table[1]//tr/td[contains(text(),"Почтовый адрес")]/following-sibling::td[1]/text())'));
        $auction['contact']['mesto_nahogdeniya'] = trim($xpath->evaluate('string(//h2[text()="Контактная информация" or contains(text(),"Информация об организации")]/following-sibling::div[1]/table[1]//tr/td[contains(text(),"Место нахождения")]/following-sibling::td[1]/text())'));
        $auction['contact']['dolg_lico'] = trim($xpath->evaluate('string(//h2[text()="Контактная информация" or contains(text(),"Информация об организации")]/following-sibling::div[1]/table[1]//tr/td[contains(text(),"должностное лицо")]/following-sibling::td[1]/text())'));
        $auction['contact']['email'] = trim($xpath->evaluate('string(//h2[text()="Контактная информация" or contains(text(),"Информация об организации")]/following-sibling::div[1]/table[1]//tr/td[contains(text(),"электронной почты")]/following-sibling::td[1]/text())'));
        $auction['contact']['tel'] = trim($xpath->evaluate('string(//h2[text()="Контактная информация" or contains(text(),"Информация об организации")]/following-sibling::div[1]/table[1]//tr/td[contains(text(),"контактного телефона")]/following-sibling::td[1]/text())'));
        $auction['contact']['fax'] = trim($xpath->evaluate('string(//h2[text()="Контактная информация" or contains(text(),"Информация об организации")]/following-sibling::div[1]/table[1]//tr/td[contains(text(),"Факс")]/following-sibling::td[1]/text())'));

        $auction['procedura']['nachalo_podachi'] = trim($xpath->evaluate('string(//h2[contains(text(),"Информация о процедуре закупки")]/following-sibling::div[1]/table[1]//tr/td[contains(text(),"Дата и время начала подачи")]/following-sibling::td[1]/text())'));
        $auction['procedura']['okonchanie_podachi'] = trim($xpath->evaluate('string(//h2[contains(text(),"Информация о процедуре закупки")]/following-sibling::div[1]/table[1]//tr/td[contains(text(),"Дата и время окончания подачи")]/following-sibling::td[1]/text())'));
        //?
        $auction['procedura']['vskrytie_konvertov'] = trim($xpath->evaluate('string(//h2[contains(text(),"Информация о процедуре закупки")]/following-sibling::div[1]/table[1]//tr/td[contains(text(),"Дата и время вскрытия конвертов")]/following-sibling::td[1]/text()[1])'));
        //?
        $auction['procedura']['data_rassmotreniya'] = trim($xpath->evaluate('string(//h2[contains(text(),"Информация о процедуре закупки")]/following-sibling::div[1]/table[1]//tr/td[contains(text(),"рассмотрения и оценки заявок")]/following-sibling::td[1]/text()[1])'));
        $auction['procedura']['okonchanie_rassmotreniya'] = trim($xpath->evaluate('string(//h2[contains(text(),"Информация о процедуре закупки")]/following-sibling::div[1]/table[1]//tr/td[contains(text(),"Дата окончания срока рассмотрения первых частей заявок участников")]/following-sibling::td[1]/text())'));
        $auction['procedura']['data_provedeniya'] = trim($xpath->evaluate('string(//h2[contains(text(),"Информация о процедуре закупки")]/following-sibling::div[1]/table[1]//tr/td[contains(text(),"Дата проведения аукциона в электронной форме")]/following-sibling::td[1]/text())'));
        $auction['procedura']['vremya_provedeniya'] = trim($xpath->evaluate('string(//h2[contains(text(),"Информация о процедуре закупки")]/following-sibling::div[1]/table[1]//tr/td[contains(text(),"Время проведения аукциона")]/following-sibling::td[1]/text())'));
        foreach($xpath->query('//h3[contains(text(),"Требования")]') as $zakazchik) {
            $zakazchik_name = trim($xpath->evaluate('string(./text())', $zakazchik));
            $zakazchik_name = trim(preg_replace('/Требования заказчика/ui', '', $zakazchik_name));
            $auction['zakazchik'][] = $this->getZakazchikInfo($zakazchik_name);
        }
        return $auction;
    }

    function getZakazchikInfo($name) {
        $zakazchik = array();
        $zakazchik['name'] = $name;
        $name = trim(preg_replace('/«|»|"/ui', '', $name));
        $name = trim(preg_replace('/\s+/ui', ' ', $name));
        $data = $this->getUrl('http://new.zakupki.gov.ru/epz/organization/quicksearch/search.html?searchString=' . urlencode($name) . '&pageNumber=1&sortDirection=false&recordsPerPage=_10&sortBy=PO_NAZVANIYU&fz94=on&fz223=on&regions=');
        libxml_use_internal_errors(true);
        $doc = new DOMDocument();
        $doc->loadHTML($data);
        $xpath = new DOMXpath($doc);

        $url = trim($xpath->evaluate('string(//div[@id="exceedSphinxPageSizeDiv"]/div[1]//tr/td[@class="descriptTenderTd"]//a/@href)'));
        if(strlen($url) > 0) {
        $data = $this->getUrl($url);
        $doc = new DOMDocument();
        $doc->loadHTML('<?xml encoding="UTF-8">' . $data);
                    $xpath = new DOMXpath($doc);
                    $zakazchik['tel'] = trim($xpath->evaluate('string(//h2/span[text()="Контактная информация"]/../following-sibling::div[1]/table[1]//tr/td/span[contains(text(),"Телефон")]/../following-sibling::td[1]//text())'));
            $zakazchik['fax'] = trim($xpath->evaluate('string(//h2[span[text()="Контактная информация"]]/following-sibling::div[1]/table[1]//tr/td[contains(span/text(),"Факс")]/following-sibling::td[1]//text())'));
                    $zakazchik['pochtovy_adres'] = trim($xpath->evaluate('string(//h2[span[text()="Контактная информация"]]/following-sibling::div[1]/table[1]//tr/td[contains(span/text(),"Почтовый адрес")]/following-sibling::td[1]//text())'));
                    $zakazchik['email'] = trim($xpath->evaluate('string(//h2[span[text()="Контактная информация"]]/following-sibling::div[1]/table[1]//tr/td[contains(span/text(),"адрес электронной почты")]/following-sibling::td[1]//text())'));
                    $zakazchik['kontaktnoe_lico'] = trim($xpath->evaluate('string(//h2[span[text()="Контактная информация"]]/following-sibling::div[1]/table[1]//tr/td[contains(span/text(),"Контактное лицо")]/following-sibling::td[1]//text())'));
                }
        return $zakazchik;
    }

    function getComplaint($auctionId, $zayavitel ,$date, $complaintNum = false) {
        if($complaintNum) {
            $complaintNum = trim(preg_replace('/№/ui', '', $complaintNum));
            $data = $this->getUrl('http://zakupki.gov.ru/epz/complaint/quicksearch/search.html?searchString=' . $complaintNum . '&strictEqual=on&pageNumber=1&sortDirection=false&recordsPerPage=_10&fz94=on&regarded=on&considered=on&returned=on&cancelled=on&hasDecision=on&noDecision=on&dateOfReceiptStart=&dateOfReceiptEnd=&updateDateFrom=&updateDateTo=&sortBy=PO_NOMERU');
        }
        else {
            $data = $this->getUrl('http://zakupki.gov.ru/epz/complaint/quicksearch/search.html?searchString=' . $auctionId . '&pageNumber=1&sortDirection=false&recordsPerPage=_10&fz94=on&regarded=on&considered=on&returned=on&cancelled=on&hasDecision=on&noDecision=on&dateOfReceiptStart=&dateOfReceiptEnd=&updateDateFrom=&updateDateTo=&sortBy=PO_NOMERU');
        }
        libxml_use_internal_errors(true);
        $doc = new DOMDocument();
        $doc->loadHTML($data);
        $xpath = new DOMXpath($doc);

        $response = array();

        $complaints = array();
        $reglamentTime = $this->reglamentTime($date);

//        $zayavitel2 = trim(preg_replace(array('/ООО/ui', '/«|»|\"/ui'), array('', ''), $zayavitel));
        $zayavitel2 = trim(preg_replace(array('/ООО/ui', '/[^а-яёa-z0-9 ]+/ui'), array('', ''), $zayavitel));
        $zayavitel2 = trim(preg_replace('/\s+/ui', ' ', $zayavitel2));
        foreach($xpath->query('//div[contains(@class,"registerBox")]') as $tender) {
            $complaint = array();
            $complaint['lico'] = trim($xpath->evaluate('string(.//td[@class="descriptTenderTd"]//tr/td[contains(text(),"Лицо, подавшее жалобу:")]/following-sibling::td[1]//text())', $tender));
            $lico = trim(preg_replace(array('/ООО/ui', '/[^а-яёa-z0-9 ]+/ui'), array('', ''), $complaint['lico']));
            $lico = trim(preg_replace('/\s+/ui', ' ', $lico));
            if(!mb_stristr($lico, $zayavitel2, false, "utf-8")) {
                continue;
            }
            $complaint['complaint_id'] = trim($xpath->evaluate('string(.//td[@class="descriptTenderTd"]/table[1]//tr[1]/td[1]//a/@href)', $tender));
            preg_match('/complaintId=(\d+)/ui', $complaint['complaint_id'], $matches);
            $complaint['complaint_id'] = $matches[1];
            $complaint['complaintNum'] = trim($xpath->evaluate('string(.//td[@class="descriptTenderTd"]/table[1]//tr[1]/td[1]//a/span/text())', $tender));
            $complaint['date'] = trim($xpath->query('.//td[contains(@class,"amountTenderTd")]//label[contains(text(),"Дата поступления:")]', $tender)->item(0)->nextSibling->nodeValue);
            $complaint['status'] = array();
            foreach($xpath->query('.//td[@class="tenderTd"]//dd', $tender) as $dd) {
                $complaint['status'][] = trim(preg_replace('/\s+/ui', ' ', $dd->nodeValue));
            }
            $complaints[] = $complaint;
        }
        //print_r($complaints);
        if(count($complaints) == 0) {
            if($reglamentTime <= time()) {
                $response['error'] = 'Ничего не найдено. Вышел регламентированный срок принятия к рассмотрению.';
            }
            else {
                $response['error'] = 'Ничего не найдено';
            }
            return $response;
        }
        elseif(count($complaints) == 1) {
            $response['complaint'] = $complaints[0];
            $response['complaint']['info'] = $this->getComplaintInfo($complaints[0]['complaint_id']);
            if($reglamentTime <= $this->getTime($complaints[0]['date'])) {
                $response['error'] = 'Нарушение регламентного срока!';
            }
            return $response;
        }
        else {
            $nbd = $this->nextBusinessDay($date);
            $indexes = array();
            foreach($complaints as $k => &$c) {
                //echo date('d.m.Y', $reglamentTime) . " " . $c['date'] . " " . $date . "\n";
                if($reglamentTime > $this->getTime($c['date']) && $this->getTime($c['date']) > $this->getTime($date)) {
                    $indexes[] = $k;
                    $c['info'] = $this->getComplaintInfo($complaint['complaint_id']);
                }
            }
            $complaints['index'] = $indexes;
            if(count($complaints['index']) == 0) {
                $response['error'] = 'С нужной датой ничего нет';
            }
            elseif(count($complaints['index']) == 1) {
                $response['complaint'] = $complaints[$complaints['index'][0]];
                return $response;
            }
            else {
                $response['error'] = 'Много данных с нужной датой';
            }
            return $response;
        }
    }

    function getComplaintInfo($id) {
        $data = $this->getUrl('http://new.zakupki.gov.ru/controls/public/action/complaint/info?source=epz&complaintId='.$id);
        libxml_use_internal_errors(true);
        $doc = new DOMDocument();
        $doc->loadHTML('<?xml encoding="UTF-8">' . $data);
        $xpath = new DOMXpath($doc);

        $complaint = array();
        $complaint['number'] = trim($xpath->evaluate('string(//h1/span/text())'));
        $complaint['date'] = trim($xpath->evaluate('string(//h2/span[text()="Описание жалобы"]/../following-sibling::div[1]/table[1]//tr/td/span[text()="Дата поступления жалобы"]/../following-sibling::td[1]//text())'));
        $complaint['date_organ'] = trim($xpath->evaluate('string(//h2/span[text()="Описание жалобы"]/../following-sibling::div[1]/table[1]//tr/td/span[contains(text(),"Дата поступления жалобы в уполномоченный орган")]/../following-sibling::td[1]//text())'));
        $complaint['date_svedeniya'] = trim($xpath->evaluate('string(//h2/span[text()="Описание жалобы"]/../following-sibling::div[1]/table[1]//tr/td/span[contains(text(),"Дата и время размещения сведений о жалобе")]/../following-sibling::td[1]//text())'));
        $complaint['date_resheniya'] = trim($xpath->evaluate('string(//h2/span[text()="Описание жалобы"]/../following-sibling::div[1]/table[1]//tr/td/span[contains(text(),"Дата и время размещения решения по жалобе")]/../following-sibling::td[1]//text())'));
        $complaint['date_rassmotreniya'] = trim($xpath->evaluate('string(//h2/span[text()="Описание жалобы"]/../following-sibling::div[1]/table[1]//tr/td/span[contains(text(),"Дата и время рассмотрения жалобы")]/../following-sibling::td[1]//text())'));
        $complaint['date_obnovleniya'] = trim($xpath->evaluate('string(//h2/span[text()="Описание жалобы"]/../following-sibling::div[1]/table[1]//tr/td/span[contains(text(),"Дата и время последнего обновления")]/../following-sibling::td[1]//text())'));
        $complaint['dop_docs'] = array();
        foreach($xpath->query('//h2/span[text()="Содержание жалобы"]/../following-sibling::div[1]/table[1]//tr/td/span[contains(text(),"Дополнительные документы")]/../following-sibling::td[1]//table//tr/td[position()=last()]/a') as $a) {
            $complaint['dop_docs'][] = array(
                'name' => trim($xpath->evaluate('string(./text())', $a)),
                'url' => trim($xpath->evaluate('string(./@href)', $a))
            );
        }
        $complaint['reshenie_po_galobe'] = trim($xpath->evaluate('string(//h2/span[text()="Сведения о решении по жалобе"]/../following-sibling::div[1]/table[1]//tr/td/span[contains(text(),"Решение по жалобе")]/../following-sibling::td[1]//text())'));
        $complaint['date_prinyatiya_resheniya'] = trim($xpath->evaluate('string(//h2/span[text()="Сведения о решении по жалобе"]/../following-sibling::div[1]/table[1]//tr/td/span[contains(text(),"Дата принятия решения")]/../following-sibling::td[1]//text())'));
        $complaint['predpisanie'] = trim($xpath->evaluate('string(//h2/span[text()="Сведения о решении по жалобе"]/../following-sibling::div[1]/table[1]//tr/td/span[contains(text(),"Предписание")]/../following-sibling::td[1]//text()[1])'));
        $complaint['reshenie'] = array(
            'name' => trim($xpath->evaluate('string(//h2/span[text()="Сведения о решении по жалобе"]/../following-sibling::div[1]/table[1]//tr/td/span[text()="Решение"]/../following-sibling::td[1]//table//tr/td[position()=last()]/a/text())')),
            'url' => trim($xpath->evaluate('string(//h2/span[text()="Сведения о решении по жалобе"]/../following-sibling::div[1]/table[1]//tr/td/span[text()="Решение"]/../following-sibling::td[1]//table//tr/td[position()=last()]/a/@href)')));
        $complaint['predpisanie'] = array(
            'name' => trim($xpath->evaluate('string(//h2/span[text()="Сведения о решении по жалобе"]/../following-sibling::div[1]/table[1]//tr/td/span[text()="Предписание"]/../following-sibling::td[1]//table//tr/td[position()=last()]/a/text())')),
            'url' => trim($xpath->evaluate('string(//h2/span[text()="Сведения о решении по жалобе"]/../following-sibling::div[1]/table[1]//tr/td/span[text()="Предписание"]/../following-sibling::td[1]//table//tr/td[position()=last()]/a/@href)')));
        return $complaint;
    }

    function nextBusinessDay($date) {
        preg_match('/(\d+)\.(\d+)\.(\d+)/ui', $date, $matches);
        $date = "{$matches[3]}-{$matches[2]}-{$matches[1]}";
        $add_day = 0;
        do {
            $add_day++;
            $new_date = date('d.m.Y', strtotime("$date +$add_day Days"));
            $new_day_of_week = date('w', strtotime("$date +$add_day Days"));
        } while($new_day_of_week == 6 || $new_day_of_week == 0);

        return $new_date;
    }

    function reglamentTime($date, $add_day = 5) {
        for($i = 1; $i <= $add_day; $i++) {
            $date = $this->nextBusinessDay($date);
        }
        preg_match('/(\d+)\.(\d+)\.(\d+)/ui', $date, $matches);
        $date = "{$matches[3]}-{$matches[2]}-{$matches[1]}";
        return strtotime($date);
    }

    function getTime($date) {
        preg_match('/(\d+)\.(\d+)\.(\d+)/ui', $date, $matches);
        $date = "{$matches[3]}-{$matches[2]}-{$matches[1]}";
        return strtotime($date);
    }

    function getUrl($url, $ref = null, $save_cookie = false, $post = false, $add = array())
    {
        for($try = 0; $try < 5; $try++) {
            $ch = curl_init($url);

            //curl_setopt($ch, CURLOPT_VERBOSE, true);
            if($post) {
                curl_setopt($ch, CURLOPT_POST, TRUE);
                //if (is_array($post))
                //  curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
                //else
                curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
            }

            curl_setopt($ch, CURLOPT_TIMEOUT, 60);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_AUTOREFERER, true);
            curl_setopt($ch, CURLOPT_DNS_USE_GLOBAL_CACHE, true);
            //curl_setopt($ch, CURLOPT_COOKIESESSION, true);
            if($ref)
                curl_setopt($ch, CURLOPT_REFERER, $ref);
            else
                curl_setopt($ch, CURLOPT_REFERER, $url);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_COOKIEFILE, dirname(__FILE__) . '/cookie');
            if($save_cookie)
                curl_setopt($ch, CURLOPT_COOKIEJAR, dirname(__FILE__) . '/cookie');

            curl_setopt($ch, CURLOPT_ENCODING, 'gzip,deflate');
            //$ua = 'Mozilla/5.0 (Windows NT 6.1; rv:30.0) Gecko/20100101 Firefox/30.0 AlexaToolbar/alxf-2.20';
            $ua = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10.11; rv:42.0) Gecko/20100101 Firefox/42.0";

            curl_setopt($ch, CURLOPT_USERAGENT, $ua);

            $headers = array(
                "Accept" => "text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
                "Accept-Language" => "ru-RU,ru;q=0.8,en-US;q=0.5,en;q=0.3",
                //'X-Requested-With: XMLHttpRequest'
            );
            $headers = array_merge($headers, $add);
            $h = array();
            foreach($headers as $k => $v) {
                $h[] = $k . ': '. $v;
            }
            curl_setopt($ch, CURLOPT_HTTPHEADER, $h);

            $res = curl_exec($ch);
            curl_close($ch);
            if($res !== FALSE)
                return $res;
        }
        echo "many try!!!";
        return FALSE;
    }

    public function mainAction(){
        $complaints = Complaint::find(array(
            "status = 'submitted'"
        ));
        $error_text = '';

        $temp_conf = new ConfigIni("../apps/frontend/config/config.ini");
        $mail = $temp_conf->mailer->toArray();
        $adminsEmail = $temp_conf->adminsEmails->toArray();
        $config = array();
        $config['driver'] = $mail['driver'];
        $config['host'] = $mail['host'];
        $config['port'] = $mail['port'];
        $config['encryption'] = $mail['encryption'];
        $config['username'] = $mail['username'];
        $config['password'] = $mail['password'];
        $config['from']['email'] = $mail['femail'];
        $config['from']['name'] = $mail['fname'];
        $mailer = new \Phalcon\Ext\Mailer\Manager($config);

        $message = $mailer->createMessage()
            ->to($adminsEmail['error'])
            ->bcc('vadim-antropov@ukr.net')
            ->subject('Работает парсер')
            ->content('Работает парсер');
        $message->send();

        foreach ($complaints as $comp) {
            $applicant = Applicant::findFirst($comp->applicant_id);
            $response = $this->getComplaint($comp->auction_id, $applicant->name_short, $comp->date_submit);
            if ($response['complaint']) {
                $status = $response['complaint']['status'];
                $changeStatus = new Complaint();
                switch ($status[1]) {
                    //No break
                    case 'Признана обоснованной':
                    case 'Признана обоснованной частично':
                        $changeStatus->changeStatus('justified', array($comp->id));
                        break;
                    case 'Рассмотрена':
                        $changeStatus->changeStatus('under_consideration', array($comp->id));
                        break;
                    case 'Признана необоснованной':
                        $changeStatus->changeStatus('unfounded', array($comp->id));
                        break;
                }
            } else {
                if ($response['error']) {
                    $error_text .= 'Текст ошибки: ' . $response['error'] . "<br/>";
                    $error_text .= ' | ID жалобы: ' . $comp->id . "<br/>";
                    $error_text .= ' | Номер извещения жалобы: ' . $comp->auction_id . "<br/>";
                    $error_text .= ' | Имя заявителя: ' . $applicant->name_short . "<br/>";
                    $error_text .= ' | Дата подачи жалобы: ' . $applicant->date_submit . "<br/>";
                    $error_text .= ' | Время работы парсера: ' . date('now') . "<br/>";
                    $error_text .= '<br/>';
                    $error_text .= '<br/>';
                    $error_text .= '---------------------------------';
                }
            }
            sleep(10);
        }

        if(strlen($error_text) > 0) {
            $message = $mailer->createMessage()
                ->to($adminsEmail['error'])
                ->bcc('vadim-antropov@ukr.net')
                ->subject('Ошибка при парсинге данных')
                ->content($error_text);
            $message->send();
        }

        $message = $mailer->createMessage()
            ->to($adminsEmail['error'])
            ->bcc('vadim-antropov@ukr.net')
            ->subject('Парсер завершил работу')
            ->content('Парсер завершил работу');
        $message->send();
    }
}