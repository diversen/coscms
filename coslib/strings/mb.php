<?php

/**
 * File contains contains class for converting between charset
 * @package strings
 */

/**
 * Class contains contains class for converting between charset
 * @package strings
 */
class strings_mb {
    
    /**
     * checks for mb_strtolower and return the string.
     * If mb_strtolower does not exists we use strtolower
     * @param string $str
     * @return string $str
     */
    public static function tolower ($str) {
        if (function_exists('mb_strtolower')) {
            return mb_strtolower($str, 'UTF-8');
        }
        return strtolower($str);
    }
    
    /**
     * wrapper to get URF8 string length
     * fallback to normal strlen if mb_strlen does not exists 
     * @param string $str
     * @return int $strlen
     */
    public static function strlen ($str) {
        if (function_exists('mb_strlen')) {
            return mb_strlen($str, 'UTF-8');
        }
        return strlen($str);
    }
    
    /**
     * get all built-in encodings as array
     * @return array $ary
     */
    public static function getEncodingsAry () {
        static $ary = null;
        if (!$ary) {
            $ary = mb_list_encodings();
        }
        return $ary;
    }
    
    /**
     * get encoding of a single string
     * @param string $str
     * @return mixed $res encoding (e.g. ISO-8859-1 or UTF8) or false
     */
    public static function getEncoding ($str) {
        $list = self::getEncodingsAry();
        $res = mb_detect_encoding($str, $list, true);
        return $res;
    }
    
    /**
     * convets a string from UTF-8 to given charset
     * @param string $content UTF-8 string
     * @param string $charset e.g. ISO-8859-1
     * @return string $content the string converted to specified charset
     */
    public static function toCharset($content, $charset) {
        $content = mb_convert_encoding($content, $charset, 'UTF-8');
        return $content;
    } 
    
    /**
     * converts a array of strings to utf8
     * @param array $ary input array
     * @return array  $ary utf8 array
     */
    public static function toUTF8Ary ($ary) {
        $new = array ();
        foreach ($ary as $key => $val) {
            $new[$key] = strings::toUTF8($val);
        }
        return $new;       
    }
}
