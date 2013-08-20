<?php

class date_helpers {
    
    /**
     * 
     * @return int $month current month as int
     */
    public static function monthCurrentInt () {
        return strftime("%m");
    }
    
    /**
     * get current year as int, e.g. 1972
     * @return int $current current year
     */
    public static function yearCurrentInt () {
        return strftime("%Y");
    }
    
    /**
     * return last 12 months starting with param start
     * @param int $start
     * @return array $ary array with last 12 moinths as ints
     */
    public static function last12Months ($date) {
        if (!$date) {
            $year = self::yearCurrentInt();
            $month = (int)self::monthCurrentInt();
        } else {
            $ary = explode('-', $date);
            $year = $ary[0];
            $month = $ary[1];
        }
        
        
        
        
        $ary = array ();
        $ary[] = array ('year' => $year, 'month' => $month);
        $i = 11;
        
        
        $next = $month;
        while ($i) {
            if ($next == 1) {
                $next = 12;
                $year--;
            } else {
                $next--;
            }
            $ary[] = array ('month' => $next, 'year' => $year);
            $i--;
        }
        
        return $ary;
    }
    
    /**
     * returns a dropdown with months
     * @param string $name name of form element
     * @param int $selected the selected month
     * @return string $html the clean html select element
     */
    public static function monthDropdown ($name ='month', $selected = null) {
        for ($i= 1; $i <= 12; $i++) {
            $months[$i] = array (
                'id' => $i,
                 'value' =>  self::monthName($i)
            );
        }
        
        return html::selectClean(
            $name, $months, 'value', 'id', $selected);
    }
    
    /**
     * returns a dropdown with months. Starting with current month 
     * and then last month ... etc.  
     * @param string $name name of form element
     * @param int $selected the selected month
     * @return string $html the clean html select element
     */
    public static function monthOffsetDropdown ($name ='month', $start = null, $selected = null, $extra = array ()) {
        

        if (!$start) { 
            $start = self::yearCurrentInt () . '-' . self::monthCurrentInt ();
        } else {
            $ary = explode('-', $start);
            $start = $ary[0] . '-' . $ary[1];
        }
        if (!$selected) { 
            $selected = self::yearCurrentInt () . '-' . self::monthCurrentInt ();
        } else {
            $ary = explode('-', $selected);
            $selected = $ary[0] . '-' . $ary[1];
        }
        
        $months = self::last12Months($start);  
        
        foreach ($months as $key => $val) {
            $months[$key] = array (
                'id' => $val['year'] . '-' . $val['month'],
                'value' =>  self::monthName($val['month'])
            );
        }
        
        return html::selectClean(
            $name, $months, 'value', 'id', $selected, $extra);
    }
        
   /**
    * gets a month name from month as int
    * @param int $month_int
    * @param string $format
    * @return string $month_name 
    * 
    */
    public static function monthName($month_int, $format = 'F') {
        $month_int = (int)$month_int;
        $timestamp = mktime(0, 0, 0, $month_int);
        return strftime('%B', $timestamp);
    }
    
    // found on
    // http://snippets.dzone.com/posts/show/1310
    public static function birthday ($birthday) {
        list($year,$month,$day) = explode("-",$birthday);
        $year_diff  = date("Y") - $year;
        $month_diff = date("m") - $month;
        $day_diff   = date("d") - $day;
        if ($month_diff < 0) { 
            $year_diff--;
        } elseif (($month_diff==0) && ($day_diff < 0)) { 
            $year_diff--;
        }
        return $year_diff;
    }
    
    /**
     * add or subtract days from timestamp (mysql like)
     * @param int $days e.g. 10 or -10
     * @param string $from e.g. 2013-10-10. Default to now
     * @return string $stamp e.g. 2013-10-20
     */
    public static function daysToTimestamp ($days, $from = null) {
        
        if (!$from) { 
            $from = self::nowTimestamp ();
        }
        
        $date = strtotime("$from $days days");
        $date = date("Y-m-d", $date );
        return $date;
    }

    /**
     * get currenct date
     * @param array $options if we need hms then set hms => 1
     * @return string $date
     */
    public static function nowTimestamp ($options = array ()) {

        if (isset($options['hms'])) {
            $format = 'Y-m-d G:i:s';
        } else {
            $format = 'Y-m-d';
        }
        $date = date($format );
        return $date;

    }
}