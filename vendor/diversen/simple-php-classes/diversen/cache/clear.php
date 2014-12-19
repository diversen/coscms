<?php

namespace diversen\cache;
use diversen\db\q as db_q;
use diversen\file;
/**
 * File contains methods for clearing all or some of the cached assets
 */

/**
 * Class contains a simple class for clearing caches
 * @package cache_clear
 */

class clear {

    /**
     * clears system_cache table
     * @return int  
     */
    public static function db () {
        $res = db_q::delete('system_cache')->filter('1 =', 1)->exec();
        return $res;
    }

    public static function assets ($options = null) {

        $path = _COS_PATH . "/htdocs/files/default/cached_assets";
        if (file_exists($path)) {
            file::rrmdir($path);
        }
        return 1;
    }

    public static function all ($options = null) {
        self::assets();
        self::db();
        return 1;
    }
}