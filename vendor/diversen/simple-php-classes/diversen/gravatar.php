<?php

namespace diversen;
/**
 * contians simple methods and functions for getting a fravatar image from
 * gravatar.com 
 * @package gravatar
 */

/**
 * contians simple methods and functions for getting a fravatar image from
 * gravatar.com 
 * @package gravatar
 */
class gravatar {

    public static function getGravatar($email, $s = 80, $d = 'identicon', $r = 'g', $img = false, $atts = array()) {
        return self::get_gravatar($email, $s, $d, $r, $img, $atts);
    }

    public static function getGravatarImg($email, $s = 80, $d = 'identicon', $r = 'g', $img = true, $atts = array()) {
        return self::get_gravatar_img($email, $s, $d, $r, $img, $atts);
    }

    /**
     * Get either a Gravatar URL or complete image tag for a specified email address.
     * @link http://gravatar.com/site/implement/images/php/
     * @param string $email The email address
     * @param int $s Size in pixels, defaults to 80px [ 1 - 512 ]
     * @param string $d Default imageset to use [ 404 | mm | identicon | monsterid | wavatar ]
     * @param string $r Maximum rating (inclusive) [ g | pg | r | x ]
     * @param boolean $img True to return a complete IMG tag False for just the URL
     * @param array $atts Optional, additional key/value attributes to include in the IMG tag
     * @return string $str containing either just a URL or a complete image tag
     */
    public static function get_gravatar($email, $s = 80, $d = 'identicon', $r = 'g', $img = false, $atts = array()) {
        $url = 'http://www.gravatar.com/avatar/';
        $url .= md5(strtolower(trim($email)));
        $url .= "?s=$s&amp;d=$d&amp;r=$r";
        if ($img) {
            $url = '<img src="' . $url . '"';
            foreach ($atts as $key => $val) {
                $url .= ' ' . $key . '="' . $val . '"';
            }
            $url .= ' />';
        }
        return $url;
    }

    /**
     * @ignore
     */
    public static function get_gravatar_img($email, $s = 80, $d = 'identicon', $r = 'g', $img = true, $atts = array()) {
        return self::get_gravatar($email, $s, $d, $r, $img, $atts);
    }
}
