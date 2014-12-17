<?php

namespace diversen\strings;
/**
 * file contains strings_normalize
 * @package strings
 */

/**
 * class contains a method to normalize newlines
 * @package strings
 */
class normalize {
    // found on: http://darklaunch.com/2009/05/06/php-normalize-newlines-line-endings-crlf-cr-lf-unix-windows-mac
    public static function newlinesToUnix($s) {
        $s = str_replace("\r\n", "\n", $s);
        $s = str_replace("\r", "\n", $s);
        //$s = preg_replace("/\n{2,}/", "\n\n", $s);
        return $s;
    }
}
