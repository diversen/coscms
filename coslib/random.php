<?php

/**
 * file contains class for creating random strings
 * @package coslib 
 */

/**
 *class random contains methods for getting random strings
 * @package coslib 
 */

class random {
    
    /**
     * gets a random string from length
     * @param int $length
     * @return string $random
     */
    public static function string( $length ) {
	$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";	
        $str = '';
	$size = strlen( $chars );
	for( $i = 0; $i < $length; $i++ ) {
		$str .= $chars[ rand( 0, $size - 1 ) ];
	}

	return $str;
    }
    
    /**
     * gets a random number from specified length
     * @param int $length
     * @return string $random
     */
    public static function number( $length ) {
	$chars = "0123456789";	
        $str = '';
	$size = strlen( $chars );
	for( $i = 0; $i < $length; $i++ ) {
		$str .= $chars[ rand( 0, $size - 1 ) ];
	}

	return $str;
    }
}
