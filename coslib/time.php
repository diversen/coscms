<?php

/**
 * file contains time functions
 * @package time 
 */

/**
 * class with time methods
 * @package time
 */
class time {

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
    
    /**
     * returns a locale date string from mysql timestamp. 
     * @param type $date same format as mysql timestamp
     * @param string $format ini settings format e.g. date_format_long
     * @return string $format in ( ... ) according to set locale 
     */
    public static function getDateString ($date, $format = 'date_format_long'){        
        $unix_stamp = strtotime($date);
        $date_formatted = strftime(config::getMainIni($format), $unix_stamp);
        return $date_formatted;
    }
    
    /**
     * will check a submitted date to see if it is valid
     * 
     * @param string $date format is: yyyy-mm-dd
     * @return boolean $res true on success and false on failure
     */
    public static function checkDate ($date) {
        $date = substr($date, 0, 10);
        $date_ary = explode('-', $date);
        if (!isset($date_ary[0]) || !isset($date_ary[1]) || !isset($date_ary[2])) {
            return false;
        }
        if  ($date_ary['0'] < 1910 ){
            return false;
        }
        return checkdate ($date_ary['1'] , $date_ary['2'] , $date_ary['0']);
    }
}
