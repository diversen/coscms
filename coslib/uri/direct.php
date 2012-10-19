<?php

class URI_direct {
    
    /**
     * flag to prevent to many url parts
     * @var int $max 
     */
    public static $max = 7;

    /**
     * get fragment from URL 
     * @staticvar null $fragments
     * @param int $part part of url to get
     * @param string|null $url specify url or _SERVER['REQUEST_URI'] will be used
     * @return string|null $fragment part of url string
     */
    public static function fragment ($part, $url = null) {
        static $fragments = null;
        
        if (!$fragments) {
            if (!$url) $url = $_SERVER['REQUEST_URI'];
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
        }
        
        if (isset($fragments[$part])) { 
            return $fragments[$part];
        } else {
            return null;
        }
    }
    
    /**
     * get base path of url, e.g. from /test/me?test=10 you will get /test/me
     * @param string|null $url
     * @return string
     */
    public static function path ($url = null) {
        if (!$url) $url = $_SERVER['REQUEST_URI'];
        $parsed = @parse_url($url);
        return $parsed['path'];
    }
    
    /**
     * get a query part ?test=1&test=2
     * @staticvar null $query_parts
     * @param string $part
     * @param string $url
     * @return string|null $query the query if set or null
     */
    public static function query ($part, $url = null) {
        static $query_parts = null;
        
        if (!$query_parts) {
            if (!$url) $url = $_SERVER['REQUEST_URI'];
            $parsed = @parse_url($url);
            if (!isset($parsed['query'])) return null;
        
            $ary = explode('&', $parsed['query']);
            if (empty($ary)) return null;
            $query_parts = array ();
            foreach ($ary as $key => $val) {
                $q = explode('=', $val);
                $query_parts[$q[0]] = $q[1];
            }
        }

        if (isset($query_parts[$part])) { 
            return $query_parts[$part];
        } else {
            return null;
        }
    }
    
    
}
