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
}