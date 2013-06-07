<?php

/**
 * File contains contains with few extension of the base string class
 * @package strings
 */

/**
 * Class contains contains with few extension of the base string class
 * @package strings
 */

class strings_ext {
    
    /** 
     * Substring without losing word meaning and
     * tiny words (length 3 by default) are included on the result.
     *  "..." is added if result do not reach original string length
     * Found on php.net
     *
     * @param   string  $str string to operate on
     * @param   int     $length the maxsize of the string to return
     * @param   int     $minword minimum size of word to cut from
     * @param   boolean $use_dots
     * @return  string  $str the substringed string
     * 
     */
    public static function substr2_min($str, $length, $minword = 3, $use_dots = true)
    {
        $sub = $ret = '';
        $len = 0;

        foreach (explode(' ', $str) as $word)
        {
            $part = (($sub != '') ? ' ' : '') . $word;
            $sub .= $part;
            $len += strings::strlen($part);

            if (strings::strlen($word) > $minword && strings::strlen($sub) >= $length)
            {
                break;
            }
            $ret.= $part;
        }

        if ($use_dots) {
            return $ret . (($len < strings::strlen($str)) ? '...' : '');
        }
        return $ret;
    }
    
    public static function removeNewlines ($str) {
        return preg_replace('/\s+/', ' ', trim($str));
    }
}