<?php

/**
 * specific mb helper methods
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
    
    public static function getEncodingsAry () {
        static $list = null;
        if (!$list) {
            $list = mb_list_encodings();
        }
        return $list;
    }
    
    /**
     * get encoding of a string
     * @param string $str
     * @return encoding $res e.g. ISO-8859-1 or UTF8
     */
    public static function getEncoding ($str) {
        $list = self::getEncodingsAry();
        $res = mb_detect_encoding($str, $list, true);
        return $res;
    }
    
    /**
     * convets from UTF-8 to given charset
     * @param string $content UTF-8 string
     * @param string $charset e.g. ISO-8859-1
     * @return type
     */
    public static function toCharset($content, $charset) {
        $content = mb_convert_encoding($content, $charset, 'UTF-8');
        return $content;
    } 
    
    /**
     * 
     * @param array $ary input array
     * @return array  $ary utf8 arrau
     */
    public static function toUTF8Ary ($ary) {
        $new = array ();
        foreach ($ary as $key => $val) {
            $new[$key] = strings::toUTF8($val);
        }
        return $new;
        
    }
}
