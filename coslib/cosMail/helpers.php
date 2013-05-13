<?php

class cosMail_helpers {
    
    public static function getDomain ($mail) {
        $ary = explode('@', $mail);
        if (isset($ary[1])) { 
            return $ary[1];
        }
        return false;
    }  
}
