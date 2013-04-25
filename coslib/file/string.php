<?php

class file_string {
    
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
}