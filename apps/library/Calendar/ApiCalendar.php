<?php
namespace Multiple\Library\Calendar;

interface ApiCalendar {
    public function checkHoliday(\DateTime $date);
}