<?php
namespace Multiple\Library\Calendar;

class Calendar
{
    private $url = 'http://basicdata.ru/api/json/calend/';
    private $startDate;
    private $nowDate;
    private $holidays;

    private function getHolidays()
    {
        $site = curl_init();
        curl_setopt($site, CURLOPT_URL, $this->url );
        curl_setopt($site, CURLOPT_RETURNTRANSFER,true);
        $temp = curl_exec($site);
        if(!empty($temp)){
            return (array)json_decode($temp);
        }
    }

    public  function __construct($startDate)
    {
        $this->startDate = strtotime($startDate);
        $this->startDate = date("Y-m-d H:i:s", $this->startDate);
        $this->nowDate =  date("Y-m-d H:i:s");
        $this->holidays = $this->getHolidays();
    }
    public function checkDate()
    {
        $countHolidays = 0;
        $currrent = date('Y-m-d H:i:s', strtotime($this->startDate) + 86400);
        while($currrent <= $this->nowDate){
            $nDay = date("N", strtotime($currrent));
            if($nDay > 5) $countHolidays++;
            $currrent = date('Y-m-d H:i:s', strtotime($currrent) + 86400);
        }

    }
}