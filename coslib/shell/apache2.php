<?php

class shell_apache2 {
    
    /**
     * 
     * @return int $ret 0 if exists 1 if not exits
     */
    public static function isInstalled() {    
        exec("which apache2", $ary, $ret);
        if ($ret == 0) {
            return true;
        }
        return false;
    }

    
    public static function getVersion() {
        exec('apache2 -v', $ary, $ret);
        $line = $ary[0];
        $ary = explode(':', $line);
        $ary[1];
        $ary = explode('/', $ary[1]);
        preg_match("/\d+(\.\d+)+/", $ary[1], $matches);
        return $matches[0];  
    }
}