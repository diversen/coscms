<?php

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
}