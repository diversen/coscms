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
     * return last 12 months starting with param start
     * @param int $start
     * @return array $ary array with last 12 moinths as ints
     */
    public static function last12Months ($start) {
        
        
        $ary = array ();
        $ary[] = (int)$start;
        $i = 11;
        
        $next = (int)$start;
        while ($i) {
            if ($next == 1) {
                $next = 12;
            } else {
                $next--;
            }
            $ary[] = $next;
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
        if (!$start) $start = self::monthCurrentInt ();
        if (!$selected) $selected = self::monthCurrentInt ();
        
        $months = self::last12Months($start);       
        foreach ($months as $key => $val) {
            $months[$key] = array (
                'id' => $val,
                 'value' =>  self::monthName($val)
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
}