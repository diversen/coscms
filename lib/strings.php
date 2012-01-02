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
        $title = self::rawurlencodeStripSpaces($title);
        $slug = $base . '/' . $title;
        return $slug;        
    }
}