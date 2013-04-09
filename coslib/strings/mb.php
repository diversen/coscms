<?php

/**
 * specific mb helper methods
 */
class strings_mb {
    
    /**
     * checks for mb_strtolower and return the string.
     * If mb_strtolower does not exists we use strtolower
     * @param string $str
     * @return string $str
     */
    public static function tolower ($str) {
        if (function_exists('mb_strtolower')) {
            return mb_strtolower($str, 'UTF-8');
        }
        return strtolower($str);
    }
}
