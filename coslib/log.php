<?php

/**
 * File contains methods for logging
 * @package    log
 */

/**
 * class log contains methods for doing 
 * logging
 * @package log
 */

class log {
    
    /**
     * logs an error. Will always be written to log file
     * if using a web server it will be logged to the default
     * error file. If CLI it will be placed in logs/coscms.log
     * @param string $message
     * @param boolean $write_file
     */
    public static function error ($message, $write_file = true) {
        cos_error_log($message, $write_file);
    }
    
    
    /**
     * debug a message
     * @param string $message 
     */
    public static function debug ($message) {
        cos_debug($message);
    }
    
    /**
     * create log file 
     */
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
 * puts a string in logs/error.log
 * You can log objects and arrays. They will be exported to a string
 * @param mixed $message
 * @param boolean $write_file
 */
function cos_error_log ($message, $write_file = 1) {
    
    
    if (!is_string($message)) {
        $message = var_export($message, true);
    }
    
    if (config::getMainIni('debug')) {
        echo $message;
    }
    
    if ($write_file){
        if (config::isCli()) {
            error_log($message . "\n", 3,_COS_PATH . "/logs/coscms.log");
        } else {
            error_log($message);
        }
    }
}

/**
 * simple debug which write to error log if 'debug' is set in main config.ini
 * @param mixed $message
 * @return void 
 */
function cos_debug ($message) {

    if (config::getMainIni('debug')) {
        cos_error_log($message);
        return;
    }  
}
