<?php

/**
 * File contains comon methods when working in db adin mode. 
 * @package coslib 
 */

/**
 * dbadmin 
 * @package
 */
class db_admin extends db {
    
    /**
     *  changes database we are working on
     * @param string $database
     */
    public static function changeDB ($database = null) {
        if (!$database) {
            $db_curr = db_admin::getDbInfo(); 
            $database = $db_curr['name'];  
        }
        $sql = "USE `$database`";
        self::rawQuery($sql);
    }
    
    /**
     * gets database info from cinfuguration
     * @return array $ary array ('name' => 'my_db, 'host' => 'localhost')
     */
    public static function getDbInfo() {
        $url = parse_url(config::$vars['coscms_main']['url']);
        $ary = explode (';', $url['path']);
        $db = explode ("=", $ary[0]);
        $database = array();
        $database['name'] = $db[1];
        $host = explode ("=", $ary[1]);
        $database['host'] = $host[1];
        return $database;
    }
}
