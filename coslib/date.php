<?php

/**
 * File contains common function for doing date math
 * @package date   
 */

/**
 * checks if a date is in a range between start and end date
 * @param string $start_date
 * @param string $end_date
 * @param string $date_from_user
 * @return boolean $res 
 */

function dateInRange ($start_date, $end_date, $date_from_user) {
  $start_ts = strtotime($start_date);
  $end_ts = strtotime($end_date);
  $user_ts = strtotime($date_from_user);
  return (($user_ts >= $start_ts) && ($user_ts <= $end_ts));
}

/**
 * gets locale day as 'weekday' from timestamp
 * @param string $stamp stamp to be used with strtotime
 * @return string $weekday
 */
function dateTimestampToLocaleDay ($stamp) {
    $time = strtotime($stamp);
    $datearray = getdate($time);
    return $datearray["weekday"];
}

/**
 * gets diff between two dates as an int
 * @param string $from date to be parsed with strtotime
 * @param string $to date
 * @return int $days
 */
function dateGetDatesTimeDiff ($from, $to) {
    $from_t = strtotime($from);
    $to_t = strtotime($to);
    $diff = $to_t - $from_t;
    return floor($diff/(60*60*24));
}


/**
 * gets a month name from month as int
 * @param int $month_int
 * @param string $format
 * @return string $month_name 
 * 
 */
function dateGetMonthName($month_int, $format = 'F') {
    $month_int = (int)$month_int;
    $timestamp = mktime(0, 0, 0, $month_int);
    return strftime('%B', $timestamp);
}

/**
 * get current year
 */
function dateGetCurrentYear () {
    return strftime("%Y");
}



/**
 * get dates as array 
 * @param string $from date
 * @param string $to date
 * @return array $dates
 */
function dateGetDatesAry ($from, $to) {
    $ary = array();    
    $i = dateGetDatesTimeDiff($from, $to);
    $c = $i - 1;
    while ($c) {
        $date = strtotime("$from +$c days");
        $date = date("Y-m-d", $date );
        $ary[] = $date;
        $i--;
        $c--;
    }
    
    $ary[] = $from;
    return array_reverse($ary);
}

/**
 * get currenct date
 * @param array $options if we need hms then set hms => 1
 * @return string $date
 */
function dateGetDateNow ($options = array ()) {
    if (isset($options['hms'])) {
        $format = 'Y-m-d G:i:s';
    } else {
        $format = 'Y-m-d';
    }
    
    if (isset($options['timestamp'])) {
        $ts = $options['timestamp'];
    } else {
        $ts = null;
    }
    $date = date($format, $ts );
    return $date;
}

function dateGetDateNowLocale ($format) {
    if (!$format) {
        $format = '%Y-%m-%d';
    }
        
    return strftime($format);
}

/**
 * add days to a timestamp
 * @param string $from start date
 * @param int $days days to add
 * @return string $date 
 */
function dateAddDaysToTimestamp ($from, $days) {
    $date = strtotime("$from +$days days");
    $date = date("Y-m-d", $date );
    return $date;
}

/**
 * subtract days from date
 * @param string $from date
 * @param int $days to subtract
 * @return string date 
 */
function dateSubstractDaysFromTimestamp ($from, $days) {
    $date = strtotime("$from -$days days");
    $date = date("Y-m-d", $date );
    return $date;
}

/**
 * checks if a mysql date is valid
 * @param string $date
 * @return boolean $res
 */
function dateIsValid ($date) {
    if (!is_string($date)) return false;
    $ary = explode('-', $date);
    if (count($ary) == 3) {
        return checkdate (  (int)$ary[1] , (int)$ary[2] , (int)$ary[0]);
    }
    return false;
}

/**
 * gets a week as number from a date
 * @param string $date
 * @return string $week 'W' 
 */
function dateGetWeekNumber ($date) {
    $week = date('W', strtotime($date));
    return $week;
}

/**
 * get a date as a day number 'N'
 * @param string $date
 * @return string $weekday 'N'
 */
function dateGetDayNumber ($date) {
    $weekday = date('N', strtotime($date)); // 1-7
    return $weekday;    
}

/**
 * gets day from date as string 'D'
 * @param string $date
 * @return string $weekday 'D'
 */
function dateGetDayStr($date) {
    $weekday = strtoupper(date('D', strtotime($date))); // 1-7
    return $weekday;    
}

/**
 * gets range of weeks between two dates
 * @param string $from date
 * @param string $to date
 * @return array $weeks array of weeks
 */
function dateGetWeekInRange ($from, $to) {
    $diff = dateGetDatesTimeDiff($from, $to);
    $weeks = array (); 
    while($diff) {
        $from = dateAddDaysToTimestamp($from, 1) ;
        $week = dateGetWeekNumber($from); 
        $weeks[$week] = $week;
        $diff--;
    }
    return $weeks;
}

/**
 * @ignore 
 * @param type $from
 * @param type $to
 * @return type 
 */
function dateGetWeeksInRange ($from, $to) {
    $from = dateAddDaysToTimestamp($from, 2);
    $to = dateAddDaysToTimestamp($to, 1);
    
    $diff = dateGetDatesTimeDiff($from, $to);

    $weeks = array (); 
  
    $week = dateGetWeekNumber($from);
    $from;
    $weeks[$week] = $week;
    
    $diff = floor($diff / 7);
    
    while($diff) {
        $from = dateAddDaysToTimestamp($from, 7) ;
        $week = dateGetWeekNumber($from); 
        $weeks[$week] = $week;
        $diff--;
    }
    return $weeks;
}

/**
 * gets week from date as array with startdate and enddate
 * @param string $date 
 * @return array $ary ('startdate' => 'date', 'enddate' => 'enddate');
 */
function dateGetWeek($date) {
        $start = strtotime($date) - strftime('%w', $date) * 24 * 60 * 60;
        $end = $start + 6 * 24 * 60 * 60;
        return array('start' => strftime('%Y-%m-%d', $start),
                     'end' => strftime('%Y-%m-%d', $end));
}

/**
 * return date as an array
 * @param string $mysql timestamp
 * @return array $ary ('year' => 1972, 'month' => 02, 'day' => 1972);
 */
function dateGetAry ($mysql) {
    $ary = explode ("-" , $mysql);

    $ary['year'] = $ary[0];
    $ary['month'] = $ary[1];
    $ary['day'] = $ary[2];
    return $ary;
}

// found on
// http://snippets.dzone.com/posts/show/1310
/**
 * gets years old from birthday
 * @param string $birthday
 * @return int $years 
 */
function dateYearsOld ($birthday) {
    list($year,$month,$day) = explode("-",$birthday);
    $year_diff  = date("Y") - $year;
    $month_diff = date("m") - $month;
    $day_diff   = date("d") - $day;
    if ($month_diff < 0) $year_diff--;
    elseif (($month_diff==0) && ($day_diff < 0)) $year_diff--;
    return $year_diff;
}
