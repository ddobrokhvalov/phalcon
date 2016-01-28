<?php


namespace Multiple\Library;
/*
 * use
 * $p = new Parser();
		print_r($p->parseAuction('0340300022416000001'));
 */
class Parser {
    function Parser() {
        libxml_use_internal_errors(true);
    }

    // передавать строку, не число!
    function parseAuction($auctionId) {
        $auction = array('info' => array(), 'contact' => array(), 'procedura' => array(), 'zakazchik' => array());
        $data = $this->getUrl('http://new.zakupki.gov.ru/epz/order/notice/ea44/view/common-info.html?regNumber='.$auctionId);
        libxml_use_internal_errors(true);
        $doc = new \DOMDocument();
        $doc->loadHTML($data);
        $xpath = new \DOMXpath($doc);

        $auction['info']['type'] = trim($xpath->evaluate('string(//h2[text()="Общая информация о закупке"]/following-sibling::div[1]/table[1]//tr/td[contains(text(),"Способ определения поставщика")]/following-sibling::td[1]/text()[1])'));
        $auction['info']['platform'] = trim($xpath->evaluate('string(//h2[text()="Общая информация о закупке"]/following-sibling::div[1]/table[1]//tr/td[contains(text(),"Наименование электронной площадки")]/following-sibling::td[1]/text()[1])'));
        $auction['info']['zakupku_osushestvlyaet'] = trim($xpath->evaluate('string(//h2[text()="Общая информация о закупке"]/following-sibling::div[1]/table[1]//tr/td[contains(text(),"Закупку осуществляет")]/following-sibling::td[1]/text()[1])'));
        $auction['info']['object_zakupki'] = trim($xpath->evaluate('string(//h2[text()="Общая информация о закупке"]/following-sibling::div[1]/table[1]//tr/td[contains(text(),"Объект закупки")]/following-sibling::td[1]/span[@id="notice_orderName"]/text())'));

//        $auction['contact']['name'] = trim($xpath->evaluate('string(//h2[text()="Контактная информация"]/following-sibling::div[1]/table[1]//tr/td[contains(text(),"Наименование организации")]/following-sibling::td[1]/text())'));
        $auction['contact']['name'] = trim($xpath->evaluate('string(//h2[text()="Общая информация о закупке"]/following-sibling::div[1]/table[1]//tr/td[contains(text(),"Закупку осуществляет")]/following-sibling::td[1]/a/text())'));
        $auction['contact']['pochtovy_adres'] = trim($xpath->evaluate('string(//h2[text()="Контактная информация"]/following-sibling::div[1]/table[1]//tr/td[contains(text(),"Почтовый адрес")]/following-sibling::td[1]/text())'));
        $auction['contact']['mesto_nahogdeniya'] = trim($xpath->evaluate('string(//h2[text()="Контактная информация"]/following-sibling::div[1]/table[1]//tr/td[contains(text(),"Место нахождения")]/following-sibling::td[1]/text())'));
        $auction['contact']['dolg_lico'] = trim($xpath->evaluate('string(//h2[text()="Контактная информация"]/following-sibling::div[1]/table[1]//tr/td[contains(text(),"должностное лицо")]/following-sibling::td[1]/text())'));
        $auction['contact']['email'] = trim($xpath->evaluate('string(//h2[text()="Контактная информация"]/following-sibling::div[1]/table[1]//tr/td[contains(text(),"электронной почты")]/following-sibling::td[1]/text())'));
        $auction['contact']['tel'] = trim($xpath->evaluate('string(//h2[text()="Контактная информация"]/following-sibling::div[1]/table[1]//tr/td[contains(text(),"контактного телефона")]/following-sibling::td[1]/text())'));
        $auction['contact']['fax'] = trim($xpath->evaluate('string(//h2[text()="Контактная информация"]/following-sibling::div[1]/table[1]//tr/td[contains(text(),"Факс")]/following-sibling::td[1]/text())'));

        $auction['procedura']['nachalo_podachi'] = trim($xpath->evaluate('string(//h2[contains(text(),"Информация о процедуре закупки")]/following-sibling::div[1]/table[1]//tr/td[contains(text(),"Дата и время начала подачи")]/following-sibling::td[1]/text())'));
        $auction['procedura']['okonchanie_podachi'] = trim($xpath->evaluate('string(//h2[contains(text(),"Информация о процедуре закупки")]/following-sibling::div[1]/table[1]//tr/td[contains(text(),"Дата и время окончания подачи")]/following-sibling::td[1]/text())'));
        //?
        $auction['procedura']['vskrytie_konvertov'] = trim($xpath->evaluate('string(//h2[contains(text(),"Информация о процедуре закупки")]/following-sibling::div[1]/table[1]//tr/td[contains(text(),"Дата и время вскрытия конвертов")]/following-sibling::td[1]/text()[1])'));
        //?
        $auction['procedura']['data_rassmotreniya'] = trim($xpath->evaluate('string(//h2[contains(text(),"Информация о процедуре закупки")]/following-sibling::div[1]/table[1]//tr/td[contains(text(),"рассмотрения и оценки заявок")]/following-sibling::td[1]/text()[1])'));
        $auction['procedura']['okonchanie_rassmotreniya'] = trim($xpath->evaluate('string(//h2[contains(text(),"Информация о процедуре закупки")]/following-sibling::div[1]/table[1]//tr/td[contains(text(),"Дата окончания срока рассмотрения первых частей заявок участников")]/following-sibling::td[1]/text())'));
        $auction['procedura']['data_provedeniya'] = trim($xpath->evaluate('string(//h2[contains(text(),"Информация о процедуре закупки")]/following-sibling::div[1]/table[1]//tr/td[contains(text(),"Дата проведения аукциона в электронной форме")]/following-sibling::td[1]/text())'));
        $auction['procedura']['vremya_provedeniya'] = trim($xpath->evaluate('string(//h2[contains(text(),"Информация о процедуре закупки")]/following-sibling::div[1]/table[1]//tr/td[contains(text(),"Время проведения аукциона")]/following-sibling::td[1]/text())'));
        foreach($xpath->query('//h3[contains(text(),"Требования заказчика")]') as $zakazchik) {
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
        $doc = new \DOMDocument();
        $doc->loadHTML($data);
        $xpath = new \DOMXpath($doc);

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
}