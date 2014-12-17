<?php

namespace diversen\uri;
/**
 * file contains uri_direct
 * @package uri
 */

/**
 * class contains methods to work directly on the uri string
 * @package uri
 */
class direct {
    
    /**
     * flag to prevent to many url parts
     * @var int $max 
     */
    public static $max = 7;

    /**
     * get fragment from URL 
     * @param int $part part of url to get
     * @param string|null $url specify url or _SERVER['REQUEST_URI'] will be used
     * @return string|null $fragment part of url string
     */
    public static function fragment($part, $url = null) {

        if (!$url) {
            $url = $_SERVER['REQUEST_URI'];
        }
        
        $parsed = @parse_url($url);
        $fragments = explode('/', $parsed['path']);

        $i = 0;
        foreach ($fragments as $key => $value) {
            if ($i > self::$max) {
                return null;
            }
            if (strlen($value) == 0) {
                unset($fragments[$key]);
            }
            $i++;
        }

        $fragments = array_values($fragments);
        if (isset($fragments[$part])) {
            return $fragments[$part];
        } else {
            return null;
        }
    }

    /**
     * get base path of url, e.g. from /test/me?test=10 you will get /test/me'
     * using parse_url to get 'path'
     * @param string|null $url a url. If empty $_SERVER[*REQUEST_URI'] will
     *                    be used 
     * @return string $parsed the base 'path'
     */
    public static function path ($url = null) {
        if (!$url) { 
            $url = $_SERVER['REQUEST_URI'];
        }
        $parsed = @parse_url($url);
        return $parsed['path'];
    }
    
    /**
     * get a query part ?test=1&test=2
     * @param string $part
     * @param string $url
     * @return string|null $query the query if set or null
     */
    public static function query($part, $url = null) {
        
        if (!$url) {
            $url = $_SERVER['REQUEST_URI'];
        }
        $parsed = @parse_url($url);
        if (!isset($parsed['query'])) {
            return null;
        }
        
        $ary = explode('&', $parsed['query']);
        if (empty($ary)) {
            return null;
        }
        
        $query_parts = array();
        $i = 0;
        foreach ($ary as $val) {
            if ($i > self::$max) {
                return null;
            }

            $q = explode('=', $val);
            $query_parts[$q[0]] = $q[1];
            $i++;
        }

        if (isset($query_parts[$part])) {
            return $query_parts[$part];
        } else {
            return null;
        }
    }
}
