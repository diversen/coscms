<?php

class strings_lines {

    public static function getFileAsArray ($file) {
	$file = fopen($file, "r");
	$ary = array();

	while (!feof($file)) {
   	    $ary[] = fgets($file);
	}
    }
}

