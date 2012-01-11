<?php

/**
 * File contains helper functions. 
 * 
 *
 * @package    coslib
 */



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
        $destination = _COS_PATH . "/logs/coscms.log";
        error_log($message, 3, $destination);
    }
}