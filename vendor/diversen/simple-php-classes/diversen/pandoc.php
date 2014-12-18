<?php

namespace diversen\pandoc;

class pandoc {
    
    /**
     * returns pandoc version e.g. 1.2.1.1 or 0 if pandoc 
     * does not exists
     * @return string $version
     */
    public static function getVersion () {
        if (self::pandocExists()) {
            return '0';
        }
        
        $return = 0;
        $output = array();
        
        // new this to be set
        putenv('HOME=/home');
        exec('pandoc -v 2>&1', $output, $return);
        return str_replace('pandoc ', '', $output[0]);
    }
    
    public static function pandocExists () {
        $return = 0;
        $output = array();
        exec('whereis pandoc', $output, $return);
        return $return;
    }    
}
