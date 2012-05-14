<?php

function dateInRange ($start_date, $end_date, $date_from_user) {
  $start_ts = strtotime($start_date);
  $end_ts = strtotime($end_date);
  $user_ts = strtotime($date_from_user);
  return (($user_ts >= $start_ts) && ($user_ts <= $end_ts));
}

function dateTimestampToLocaleDay ($stamp) {
    $time = strtotime($stamp);
    $datearray = getdate($time);
    return $datearray["weekday"];
}

function dateGetDatesTimeDiff ($from, $to) {
    $from_t = strtotime($from);
    $to_t = strtotime($to);
    $diff = $to_t - $from_t;
    return floor($diff/(60*60*24));
}

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

function dateGetDateNow ($options = array ()) {
    if (isset($options['hms'])) {
        $format = 'Y-m-d G:i:s';
    } else {
        $format = 'Y-m-d';
    }
    $date = date($format );
    return $date;
}

function dateAddDaysToTimestamp ($from, $days) {
    $date = strtotime("$from +$days days");
    $date = date("Y-m-d", $date );
    return $date;
}

function dateSubstractDaysFromTimestamp ($from, $days) {
    $date = strtotime("$from -$days days");
    $date = date("Y-m-d", $date );
    return $date;
}

function dateIsValid ($date) {
    if (!is_string($date)) return false;
    $ary = explode('-', $date);
    if (count($ary) == 3) {
        return checkdate (  (int)$ary[1] , (int)$ary[2] , (int)$ary[0]);
    }
    return false;
}

function dateGetWeekNumber ($date) {
    $week = date('W', strtotime($date));
    return $week;
}

function dateGetDayNumber ($date) {
    $weekday = date('N', strtotime($date)); // 1-7
    return $weekday;    
}

function dateGetDayStr($date) {
    $weekday = strtoupper(date('D', strtotime($date))); // 1-7
    return $weekday;    
}

function dateGetWeekInRange ($from, $to) {
    $diff = dateGetDatesTimeDiff($from, $to);
    $weeks = array (); 
    $i = 0;
    while($diff) {
        $from = dateAddDaysToTimestamp($from, 1) ;
        $week = dateGetWeekNumber($from); 
        $weeks[$week] = $week;
        $diff--;
    }
    return $weeks;
}


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

function dateGetWeek($date) {
        $start = strtotime($date) - strftime('%w', $date) * 24 * 60 * 60;
        $end = $start + 6 * 24 * 60 * 60;
        return array('start' => strftime('%Y-%m-%d', $start),
                     'end' => strftime('%Y-%m-%d', $end));
}

