<?php

/**
 * Some methods for manipulating strings
 * @package coslib
 */

/**
 * class for manipulating strings
 * @package coslib
 */
class strings {
    
    /**
     * From: http://cubiq.org/the-perfect-php-clean-url-generator
     * @param type $str
     * @return type 
     */
    public static function toAscii($str) {
	$clean = preg_replace("/[^a-zA-Z0-9\/_|+ -]/", '', $str);
	$clean = strtolower(trim($clean, '-'));
	$clean = preg_replace("/[\/_|+ -]+/", '-', $clean);
	return $clean;
    }
    
    /**
     * From: http://cubiq.org/the-perfect-php-clean-url-generator
     * @param type $str
     * @return type 
     */
    public static function toAsciiWithTranslit($str) {
	$clean = iconv('UTF-8', 'ASCII//TRANSLIT', $str);
	$clean = preg_replace("/[^a-zA-Z0-9\/_| -]/", '', $clean);
	$clean = strtolower(trim($clean, '-'));
	$clean = preg_replace("/[\/_| -]+/", '-', $clean);

	return $clean;
    }
    
    /**
     * method for creating a seo title by seperating spaces with e.g. '-'
     * @param string $title the title to change
     * @param string $sep the seperator to use. Deffaults to '-'
     * @return string $title seo title
     */
    function seoTitle($title, $sep = '-'){
        $title = explode(' ', ($title));
        $title = strtolower(implode($title, $sep));
        return $title;
    }
    
        
    /**
     * this is used for rawurlencode a string and substitue spaces ' ' with '-'
     * @param string $str the string to work on
     * @return string $str the manipulated string
     */
    public static function rawurlencodeStripSpaces ($str) {
        $str = str_replace(' ', '-', $str);
        return rawurlencode($str);
    }
    
    /**
     * creates a utf8 based slug where ' ' are substituted with '-'
     * @param string $base the basde of the url, e.g. /content/article/view/1
     * @param string $title the title of the url e.g. 华语 華語
     * @return string $str the manipulated string, e.g. 
     *                     /content/article/view/1/华语-華語
     */
    public static function utf8Slug ($base, $title = null) {
        if (!$title) {
            return $base;
        }
        $title = self::sanitizeUrlRigid($title);
        $title = self::rawurlencodeStripSpaces($title);
        $slug = $base . '/' . $title;
        return $slug;        
    }
    
    /**
     * function for sanitizing a URL
     * from http://chyrp.net/
     * 
     * @param string $string
     * @param boolean $force_lowercase 
     * @param boolean $remove_special
     * @return string $str the sanitized string 
     */
    
    public static function sanitizeUrlRigid ($string, $force_lowercase = true, $remove_special = false) {
        $strip = array("~", "`", "!", "@", "#", "$", "%", "^", "&", "*", "(", ")", "_", "=", "+", "[", "{", "]",
                   "}", "\\", "|", ";", ":", "\"", "'", "&#8216;", "&#8217;", "&#8220;", "&#8221;", "&#8211;", "&#8212;",
                   "â€”", "â€“", "<", ">", "/", "?");
        return $clean = trim(str_replace($strip, "", strip_tags($string)));
    }
    
    /**
     * simple sanitize function where only thing removed is /
     * in order to not confuse the url
     * @param string $string string to sanitize
     * @return string $string sanitized string
     */
    public static function sanitizeUrlSimple () {
        $strip = array("/", "?", "#");
        return $clean = trim(str_replace($strip, "", htmlspecialchars(strip_tags($string))));
    }   
}

/** 
 * Substring without losing word meaning and
 * tiny words (length 3 by default) are included on the result.
 *  "..." is added if result do not reach original string length
 * Found on php.net
 *
 * @param   string  $str string to operate on
 * @param   int     $length the maxsize of the string to return
 * @param   int     $minword minimum size of word to cut from
 * @return  string  $str the substringed string
 */
function substr2($str, $length, $minword = 3, $use_dots = true)
{
    $sub = '';
    $len = 0;

    foreach (explode(' ', $str) as $word)
    {
        $part = (($sub != '') ? ' ' : '') . $word;
        $sub .= $part;
        $len += strlen($part);

        if (strlen($word) > $minword && strlen($sub) >= $length)
        {
            break;
        }
    }

    if ($use_dots) {
        return $sub . (($len < strlen($str)) ? ' ... ' : '');
    }
    return $sub;
}

/**
 * function for removing extra white space, and only have 'one space' left
 * @param string $str the string to operate on
 * @return string $str the transformed string 
 */
function cos_remove_extra_ws ($str) {
    $str = preg_replace('/\s\s+/', ' ', $str);
    return $str;
}

/**
 * function for urlencoding a utf8 encoding a string
 * @param   string  $string the utf8 string to encode
 * @return  string  $string the utf8 encoded string
 */
function cos_url_encode($string){
    return urlencode(utf8_encode($string));
}

/**
 * function for urldecoding a utf8 decodeding a string
 * @param   string  $string the string to decode
 * @return  string  $string the urldecoded and utf8 decoded string
 */
function cos_url_decode($string){
    return utf8_decode(urldecode($string));
}
// }}}

/**
 * trims a string
 * @param string $value 
 */
function trim_value(&$value){ 
    $value = trim($value); 
}

/**
 * trims an array of strings
 * @param array $ary the array to be trimmed
 * @return array $ary the trimmed array 
 */
function trim_array ($ary) {
    array_walk($ary, 'trim_value');
    return $ary;
}