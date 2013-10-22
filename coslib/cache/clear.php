<?php

class cache_clear {

    public static function db ($options = null) {
        $res = db_q::setDelete('system_cache')->filter('1 =', 1)->exec();
        if ($res) {
            return 0;
        }
        return 1;
    }

    public static function assets ($options = null) {
        if (config::isCli()) {
            cos_needs_root();
        }
        $path = _COS_PATH . "/htdocs/files/default/cached_assets";
        return file::rrmdir($path);
    }

    public static function all ($options = null) {
        if (config::isCli()) {
            cos_needs_root();
        }
        self::assets();
        self::db();
    }
}