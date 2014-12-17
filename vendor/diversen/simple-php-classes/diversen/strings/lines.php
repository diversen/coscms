<?php

namespace diversen\strings;
/**
 * File contains contains with strings methods
 * @package strings
 */

/**
 * Class contains strings methods
 * @package strings
 */

class lines {

    /**
     * get a file as an array
     * @param type $file
     */
    public static function getFileAsArray ($file) {
	$file = fopen($file, "r");
	$ary = array();

	while (!feof($file)) {
   	    $ary[] = fgets($file);
	}
    }
}
