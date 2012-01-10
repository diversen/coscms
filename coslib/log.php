<?php

/**
 * @package coslib
 */

/**
 * @package coslib
 */
class log {

    /**
     *
     * @param   string   $message to append to log file
     * @param   int      $level how suvere is the message.
     *
     *                   0 SYSTEM
     *                   1 NOTICE
     *                   2 WARNING
     *                   3 DEBUG
     *                   4 ERROR
     *                   5 DIE
     */
    public static function write($message, $level = 0){
        $file = _COS_PATH . "/logs/coscms.log";
        $message = $level .": " . $message . "\n";
        $ret = file_put_contents($file, $message, FILE_APPEND);
        if (!$ret){
            die ("Can not append to file $file");
        }
    }
}
