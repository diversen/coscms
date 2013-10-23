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

        echo $path = _COS_PATH . "/htdocs/files/default/cached_assets";
        if (file_exists($path)) {
            file::rrmdir($path);
        }
        return 1;
    }

    public static function all ($options = null) {
        echo "ok";
        self::assets();
        self::db();
        return 1;
    }
}