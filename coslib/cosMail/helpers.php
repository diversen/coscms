<?php

/**
 * contains mail helpers
 * @package cosMail
 */

/**
 * class contains mail helpers
 * @package cosMail
 */
class cosMail_helpers {

    /**
     * gets domain from email
     * @param string $mail
     * @return string|false $ret domain or false
     */
    public static function getDomain($mail) {
        $ary = explode('@', $mail);
        if (isset($ary[1])) {
            return $ary[1];
        }
        return false;
    }

    /**
     * extract all emails from a text
     * @param type $txt
     */
    public static function getEmails($txt) {
        $pattern = "/([\s]*)([_a-zA-Z0-9-]+(\.[_a-zA-Z0-9-]+)*([ ]+|)@([ ]+|)([a-zA-Z0-9-]+\.)+([a-zA-Z]{2,}))([\s]*)/i";

        // preg_match_all returns an associative array
        preg_match_all($pattern, $txt, $matches);

        // all emails caught in $matches[0]
        if (!empty($matches[0])) {
            foreach ($matches[0] as $key => $val) {
                $matches[0][$key] = strtolower(trim($val));
            }
        }
        $matches = array_unique($matches[0]);
        return  $matches;
    }
}
