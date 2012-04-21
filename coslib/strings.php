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
        $title = self::utf8SlugString($title);
        $slug = $base . '/' . $title;
        return $slug;        
    }
    
    public static function utf8SlugString ($title) {
        $title = self::sanitizeUrlRigid($title);
        $title = self::rawurlencodeStripSpaces($title);
        return $title;
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
     * Found on: http://www.php.net/manual/en/function.utf8-encode.php#102382
     * convert a string to UTF8
     * @param string $content
     * @return string $str
     */
    public static function toUTF8($content) {
        
        if(!mb_check_encoding($content, 'UTF-8')
            OR !($content === mb_convert_encoding(mb_convert_encoding($content, 'UTF-32', 'UTF-8' ), 'UTF-8', 'UTF-32'))) {

            $content = mb_convert_encoding($content, 'UTF-8');

            if (mb_check_encoding($content, 'UTF-8')) {
                print('Converted to UTF-8');
            } else {
                print('Could not converted to UTF-8');
                die;
            }
        } else {
            echo "is utf8";
        }
        return $content;
    } 
    
    /**
     * simple sanitize function where only thing removed is /
     * in order to not confuse the url
     * @param string $string string to sanitize
     * @return string $string sanitized string
     */
    public static function sanitizeUrlSimple ($string) {
        $strip = array("/", "?", "#");
        return $clean = trim(str_replace($strip, "", htmlspecialchars(strip_tags($string))));
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
    public static function substr2($str, $length, $minword = 3, $use_dots = true)
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
    public static function removeExtraWs ($str) {
        $str = preg_replace('/\s\s+/', ' ', $str);
        return $str;
    }
    
    /** 
     * slightly modified from: 
     * http://stackoverflow.com/a/816102/464549
     * removes all newlines except one
     * @param string $str
     * @return string $srr 
     */
    public static function removeExtraNL ($str) {
        return preg_replace('/(?:(?:\r\n|\r|\n)\s*){2}/s', PHP_EOL, $str);
    }
    
    public static function trimArray () {
        
    }
}
