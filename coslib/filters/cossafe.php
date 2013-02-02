<?php

/**
 *
 * Filter for cresating safe html with HTML_Safe
 *
 * @package    filters
 */

/**
 * class for highlighting php with native php functions.
 *
 * @package    filters
 */
class cossafe {

    /**
     *
     * @param array     array of elements to filter.
     * @return <type>
     */
    public static function filter($text){
        require_once 'HTML/Safe.php';
        //$safehtml =& new safehtml();
        $safe = new HTML_Safe;
        $text = $safe->parse($text);

        $text = strip_tags($text, '<p><a><ul><li>');
        
        return $text;
    }
}
