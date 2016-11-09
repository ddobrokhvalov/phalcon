<?php
namespace Multiple\Library\Calendar;

class Calendar
{
    private $api;
    private $date;
    private $nowDate;
    private $interval;
    private $countDiffDays;

    private  function __construct(ApiCalendar $api)
    {
        $this->api = $api;
        $this->countDiffDays = 0;
        $this->nowDate = new \DateTime('now');
        $this->interval = new \DateInterval('P1D');
    }

    public function checkDateAddComplaint($date, $days = 10)
    {
        $this->date = new \DateTime($date);
        $countHolidays = 0;
        $countDays = 0;
        $currrent = $this->date;
        if($this->nowDate > $this->date) {
            while ($currrent < $this->nowDate && $countDays < 20) {
                $isHoliday = $this->api->checkHoliday($currrent);
                if ($isHoliday == 'holiday') $countHolidays++;
                elseif ($isHoliday != 'work' && $currrent->format('N') > 5) $countHolidays++;
                $currrent->add($this->interval);
                $countDays++;
            }
            $countDays--;
            if(($countDays - $countHolidays) >= $days ) return 1;
        }
        return 0;
    }

    public function checkDateAbortComplaint($regDate, $days = 5){
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
                    } else if( $currrent->format('N') == 7 ){
                        $currrent->add(new \DateInterval('P2D'));
                    }
                } elseif($currrent->format('N') > 5){
                    $countHolidays++;
                }
                $countDays++;
            }
            if(($countDays - $countHolidays) >= $days ) return 1;
        }
        return 0;
    }
}





