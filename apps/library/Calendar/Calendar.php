<?php
namespace Multiple\Library\Calendar;

class Calendar
{
    private $api;
    private $date;
    private $nowDate;
    private $interval;
    private $countDiffDays;

    public  function __construct(ApiCalendar $api, $days)
    {
        $this->api = $api;
        $this->countDiffDays = 0;
        $this->days = $days;
        $this->nowDate = new \DateTime('now');
        $this->interval = new \DateInterval('P1D');
    }

    public function checkDateAddComplaint($date)
    {
        $this->date = new \DateTime($date);
        $countHolidays = 0;
        $countDays = 0;
        $currrent = $this->date;
        if($this->nowDate > $this->date) {
            if($currrent->format('N') == 6){
                $currrent->add(new \DateInterval('P2D'));
            } else if($currrent->format('N') == 7){
                $currrent->add(new \DateInterval('P1D'));
            }

            while($this->api->checkHoliday($currrent) == 'holiday'){
                $currrent->add($this->interval);
            }

            while ($currrent < $this->nowDate && $countDays <= 10) {
                //$isHoliday = $this->api->checkHoliday($currrent);
                //if ($isHoliday == 'holiday') $countHolidays++;
                //elseif ($isHoliday != 'work' && $currrent->format('N') > 5) $countHolidays++;
                $currrent->add($this->interval);
                $countDays++;
            }

            $diffDays = $countDays - $countHolidays;
            if($diffDays > $this->days ) return 1;
            elseif($diffDays == $this->days){
                if($this->nowDate->format('H:i') > $this->date->format('H:i')){
                    return 1;
                }
            }
        }
        return 0;
    }

    public function checkDateAbortComplaint($regDate){
        $this->date = new \DateTime($regDate);
        $countHolidays = 0;
        $countDays = 0;
        $currrent = $this->date;
        if($this->nowDate > $this->date) {
            while ($currrent < $this->nowDate && $countDays < 20) {
                $isHoliday = $this->api->checkHoliday($currrent);
                if ($isHoliday == 'holiday'){
                    $countHolidays++;
                } elseif($countDays == 0 && $isHoliday != 'work'){
                    if($currrent->format('N') == 6){
                        $currrent->add(new \DateInterval('P3D'));
                        continue;
                    } else if( $currrent->format('N') == 7 ){
                        $currrent->add(new \DateInterval('P2D'));
                        continue;
                    }
                } elseif($currrent->format('N') > 5){
                    $countHolidays++;
                }
                $currrent->add($this->interval);
                $countDays++;
            }
            $diffDays = $countDays - $countHolidays;
            if($diffDays > $this->days) return 1;
            elseif($diffDays == $this->days){
                if($this->nowDate->format('H:i') > $this->date->format('H:i')){
                    return 1;
                }
            }
        }
        return 0;
    }

}





