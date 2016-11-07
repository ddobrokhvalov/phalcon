<?php

namespace Multiple\Library\Calendar;


class BasicDataRu implements ApiCalendar {
    private $data;
    private $url = 'http://basicdata.ru/api/json/calend/';

    public function __construct(){
        $this->data = null;
        $site = curl_init();
        curl_setopt($site, CURLOPT_URL, $this->url );
        curl_setopt($site, CURLOPT_RETURNTRANSFER,true);
        $temp = curl_exec($site);
        if(!empty($temp)){
            $this->data = json_decode($temp);
            $this->data = $this->data->data;
        }
    }

    public function checkHoliday(\DateTime $date)
    {
        $currentDate = null;
        $dateY = (integer)$date->format('Y');
        $dateM = (integer)$date->format('m');
        $dateD = (integer)$date->format('j');

        if( !empty($this->data) &&
            !empty($this->data->$dateY) &&
            !empty($this->data->$dateY->$dateM) &&
            !empty($this->data->$dateY->$dateM->$dateD)){
            $currentDate = $this->data->$dateY->$dateM->$dateD->isWorking;
        }

        if($currentDate) {
            switch ($currentDate) {
                case '0':
                    return 'work';
                    break;
                case '2':
                    return 'holiday';
                    break;
                default:
                    return false;
                    break;
            }
        }
        return false;
    }
}