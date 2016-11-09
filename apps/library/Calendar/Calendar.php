<?php
namespace Multiple\Library\Calendar;

class Calendar
{
    private $api;
    private $endDate;
    private $nowDate;
    private $interval;
    private $countDiffDays;
    private static $instance = null;

    private  function __construct(ApiCalendar $api)
    {
        $this->api = $api;
        $this->countDiffDays = 0;
        $this->nowDate = new \DateTime('now');
        $this->interval = new \DateInterval('P1D');
    }

    public function checkDate($endDate, $days = 10)
    {
        $this->endDate = new \DateTime($endDate);
        $countHolidays = 0;
        $countDays = 0;
        $currrent = $this->endDate;
        if($this->nowDate > $this->endDate) {
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

    public static function getInstance(ApiCalendar $api){
        if(is_null(self::$instance)){
            return new Calendar($api);
        }
        return self::$instance;
    }
}





