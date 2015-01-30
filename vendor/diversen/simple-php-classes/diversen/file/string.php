<?php

namespace diversen\file;
/**
 * package contains file class for doing common file tasks associated 
 * with strings. 
 * @package file
 * 
 */

/**
 * class contains file class for doing common file tasks associated 
 * with strings. 
 * @package file
 */

class string {
    
    /**
     * return the number of lines in a file
     * @param string $file
     * @return int $num
     */
    public static function getNumLines ($file) {
        $linecount = 0;
        $handle = fopen($file, "r");
        while(!feof($handle)){
            $line = fgets($handle);
            $linecount++;
        }
        
        fclose($handle);
        return $linecount;
    }
    
    /**
     * removes a line from a file and saves it
     * @param string $file
     * @param string $str
     * @return boolean $res true on success and false on failure.
     */
    public static function rmLine ($file, $str) {
        $handle = fopen($file, "r");
        $final = '';
        while(!feof($handle)){
            $line = fgets($handle);
            if (strstr($line, $str)) continue;
            $final.= $line;
        }
        fclose($handle);
        return file_put_contents($file, $final, LOCK_EX);
    }
    
    public static function getLine($file) {
        $handle = @\fopen($file, "r");
        if ($handle) {
            $line = \fgets($handle);
            @\fclose($handle);
            return $line;
        } 
        return false;
    }
}
