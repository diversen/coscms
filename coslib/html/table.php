<?php

/**
 * File containing class for building tables
 * @package html 
 */

/**
 * Class for building tables
 * @package html
 */
class html_table {
    
    public static $str;
    public static function td ($val) {
        self::$str.= "<td>$val</td>";
        return new html_table;
    }
    
    public static function trBegin () {
        self::$str.= "<tr>\n";
        return new html_table;
    }
    
    public static function trEnd () {
        self::$str.= "<tr>\n";
        return new html_table;
    }
    
    public static function tableBegin ($options) {
        $extra = html::parseExtra($options);
        self::$str.= "<table $extra>\n";
        return new html_table;
    }
    
    public static function tableEnd () {
        self::$str.= "<table>\n";
        return new html_table;
    }
    
    public static function get () {
        $str =  self::$str;
        self::$str = '';
        return $str;
    }
}