<?php

/**
 * file contains strings_version
 * @package strings
 */

/**
 * class contains a method to get versions from strings
 * @package strings
 */
class strings_version {
    /**
     * parses a semantic version string into a array consisting of 
     * major, minor, minimal 2.4.6 will return: 
     * 
     * @param string $str e.g. 2.4.6
     * @return array $ary array ('major' => 2, 'minor' => 4, 'minimal' => 6)
     */
    public static function getSemanticAry ($str) {
        $ary = explode(".", $str);
        $ret['major'] = $ary[0];
        $ret['minor'] = $ary[1];
        $ret['minimal'] = $ary[2];
        return $ret;
    }
}
