<?php

/**
 * File contains helper functions. 
 * 
 *
 * @package    coslib
 */

class log {
    public static function error ($message, $write_file = true) {
        cos_error_log($message, $write_file);
    }
    
    public static function message ($message, $write_file = true) {
        cos_error_log($message, $write_file);
    }
    
    public static function debug ($message) {
        cos_debug($message);
    }
    
    public static function createLog () {
        if (!defined('_COS_PATH')) {
            die('No _COS_PATH defined');
        }
        
        $file = _COS_PATH . "/logs/error.log";
        if (!file_exists($file)) {
            $res = @file_put_contents($file, '');
            if ($res === false) {
                die("Can not create log file: $file");
            }
        }
    }
}

/**
 * puts a string in logs/coscms.log
 * @param string $message
 */
function cos_error_log ($message, $write_file = true) {
    if (!is_string($message)) {
        $message = var_export($message, true);
    }
    
    $message = strftime('%c', time()) . ": " . $message;
    $message.="\n";
    
    if ($write_file) {
        //$destination = _COS_PATH . "/logs/error.log";
        error_log($message);
        //error_log($message, 3, $destination);
    }
}

function cos_debug ($message) {
    static $debug = null;
    if (!$debug) $debug = config::getMainIni('debug');
    if ($debug) cos_error_log($message);
}
