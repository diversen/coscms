<?php

class timeAndDate {

    /**
     * return number of days, hours, seconds as array from seconds
     * 
     * @param int $secs number of seconds
     * @return array $res array e.g. array ('days' => 2, 
     *                                      'hours' => 4,
     *                                      'minuts => 10); 
     */
    public static function getSecsDivided ($secs) {
        $day = 24 * 60 * 60;
        $hour = 60 * 60;
        $minute = 60;
        
        $res = array ();
        
        // get days
        if ($secs >= $day) {
            $remains_from_days = $secs % $day;
            $res['days'] = ($secs - $remains_from_days) / $day;
        } else {
            $remains_from_days = $secs;
            $res['days'] = 0;
        }
        
        if ($remains_from_days >= $hour) {
            $remains_from_hours = $remains_from_days % $hour;
            $res['hours'] = ($remains_from_days - $remains_from_hours) / $hour;
        } else {
            $remains_from_hours = $remains_from_days;
            $res['hours'] = 0;
        }
        
        if ($remains_from_hours >= $minute) {
            $remains_from_minutes = $remains_from_hours % $minute;
            $res['minutes'] = ($remains_from_hours - $remains_from_minutes) / $minute;
        } else {
            $remains_from_minutes = $remains_from_hours;
            $res['minutes'] = 0;
        }
        
        $res['seconds'] = $remains_from_minutes;
        return $res;
    }
}
