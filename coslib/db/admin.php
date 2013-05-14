<?php

/**
 * File contains comon methods when working in db adin mode. 
 * @package coslib 
 */

/**
 * dbadmin 
 * @package coslib
 */
class db_admin extends db {
    
    /**
     * changes database we are working on
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
    
    /**
     * dublicate a table 
     * @param string $source source table name
     * @param string $dest destination table name
     * @param boolean $drop should we drop table if destination exists 
     * @return boolean $res result of query
     */
    public static function dublicateTable ($source, $dest, $drop = true) {
        if ($drop) {
            $sql = "DROP TABLE IF EXISTS $dest";
            $res = self::rawQuery($sql);
            if (!$res) {
                return false;
            }
        }
        
        $sql = "CREATE TABLE $dest LIKE $source; INSERT $dest SELECT * FROM $source";
        return self::rawQuery($sql);
    }
    
    /**
     * Alter table to include a full text index
     * @param string $table
     * @param string $columns (e.g. firstname, lastname)
     * @return boolean $res result
     */
    public static function generateIndex($table, $columns) {
        $sql = "ALTER TABLE $table ENGINE = MyISAM";
        $res = self::rawQuery($sql);
        if (!$res) {
            return false;
        }
        
        $cols = implode(',', $columns);
        
        $sql = "ALTER TABLE $table ADD FULLTEXT($cols)";
        return self::rawQuery($sql);
    }
    
    /**
     * check if a table with specified name exists
     * @param string $table
     * @return array $rows
     */
    public static function tableExists($table) {
        $q = "SHOW TABLES LIKE '$table'";
        $rows = db::selectQueryOne($q);
        return $rows;
    }
    
    /**
     * get indexes on the table
     * @param string $table the table name
     * @return array $rows
     */
    public static function getKeys ($table) {
        $q = "SHOW KEYS FROM $table";
        $rows = db::selectQuery($q);
        return $rows;
    }
    
    /**
     * check if a specified key exists
     * 
     */
    public static function keyExists ($table, $key) {
        $q = "SHOW KEYS FROM $table WHERE Key_name='$key'";
        $rows = db::selectQueryOne($q);
        return $rows;
    }
}
