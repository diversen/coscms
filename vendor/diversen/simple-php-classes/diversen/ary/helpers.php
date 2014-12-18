<?php

namespace diversen\ary;

/**
 * file contains array helpers
 * @package array
 */

/**
 * class contains array helpers
 * @package array
 */
class helpers {
    /**
     * prepares an array for db post where we specify keys to use 
     * @param array $keys keys to use from POST request
     * @return array $ary array with post array we will use 
     */
    public static function preparePOST ($keys, $null_values = true) {
        $ary = array ();
        foreach ($keys as $val) {
            if (isset($_POST[$val])) {
                $ary[$val] = $_POST[$val];
            } else {
                if ($null_values) {
                    $ary[$val] = NULL;
                }
            }
        }
        return $ary;
    }
    
    /**
     * prepares an array for db post where we specify keys to use 
     * @param array $keys keys to use from GET request
     * @return array $ary array with post array we will use 
     */
    public static function prepareGET ($keys, $null_values = true) {
        $ary = array ();
        foreach ($keys as $val) {
            if (isset($_GET[$val])) {
                $ary[$val] = $_GET[$val];
            } else {
                if ($null_values) {
                    $ary[$val] = NULL;
                }
            }
        }
        return $ary;
    }
}
