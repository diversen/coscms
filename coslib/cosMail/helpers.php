<?php

/**
 * contains mail helpers
 * @package cosMail
 */

/**
 * class contains mail helpers
 * @package cosMail
 */
class cosMail_helpers {
    
    /**
     * gets domain from email
     * @param string $mail
     * @return string|false $ret domain or false
     */
    public static function getDomain ($mail) {
        $ary = explode('@', $mail);
        if (isset($ary[1])) { 
            return $ary[1];
        }
        return false;
    }  
}
