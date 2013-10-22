<?php

class cache_clear {

    /**
     * clears system_cache table
     * @return int  
     */
    public static function db () {
        $res = db_q::delete('system_cache')->filter('1 =', 1)->exec();
        return $res;
    }

    public static function assets ($options = null) {
        if (config::isCli()) {
            cos_needs_root();
        }
        $path = _COS_PATH . "/htdocs/files/default/cached_assets";
        if (file_exists($path)) {
            file::rrmdir($path);
        }
        return 1;
    }

    public static function all ($options = null) {
        if (config::isCli()) {
            cos_needs_root();
        }
        self::assets();
        self::db();
        return 1;
    }
}