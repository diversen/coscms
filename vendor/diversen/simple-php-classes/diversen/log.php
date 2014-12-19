<?php

namespace diversen;
use diversen\conf as config;
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
    public static function error ($message, $write_file = true, $echo = true) {
              
        if (!is_string($message)) {
            $message = var_export($message, true);
        }

        if (config::getMainIni('debug') && $echo == true) {
            if (config::isCli()) {
                echo $message . PHP_EOL;
            } else {
                echo $message;
            }
        }

        if ($write_file){
            $message = strftime(config::getMainIni('date_format_long')) . ": " . $message;
            if (config::isCli()) {
                $path = _COS_PATH . "/logs/coscms.log";
                error_log($message . PHP_EOL, 3, $path);
            } else {
                error_log($message);
            }
        }
    }
    
    
    /**
     * debug a message. Writes to stdout and to log file 
     * if debug = 1 is set in config
     * @param string $message 
     */
    public static function debug ($message) {       
        if (config::getMainIni('debug')) {
            self::error($message);
            return;
        } 
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

