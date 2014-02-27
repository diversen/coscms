<?php

/**
 * File contains comon methods when working in db adin mode. 
 * @package db 
 */

/**
 * dbadmin 
 * @package db
 */
class db_admin extends db {
    
    /**
     * changes database we are working on
     * @param string $database
     */
    public static function changeDB ($database = null) {
        if (!$database) {
            $db_curr = db_admin::getDbInfo(); 
            $database = $db_curr['dbname'];  
        }
        $sql = "USE `$database`";
        self::rawQuery($sql);
    }
    
    /**
     * gets database info from cinfuguration
     * @return array $ary array ('name' => 'my_db, 'host' => 'localhost')
     */
    public static function getDbInfo($url = null) {
        if (!$url) {
            $url = config::$vars['coscms_main']['url']; ;
        }
        
        $url = parse_url($url);
        //print_r($url);
        $ary = explode (';', $url['path']);
        foreach ($ary as $val) {
            //print_r($val);
            $a = explode ("=", $val);
            //print_r($a);
            if (isset($a[0], $a[1])) {
                $url[$a[0]] = $a[1];
            }
        }
        return $url;

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
    
    public static function cloneDB($database, $newDatabase){
        $db = new db();
        $rows = $db->selectQuery('show tables');
        $tables = array();
        foreach ($rows as $table) {
            $tables[] = array_pop($table);
        }

        $db->rawQuery("CREATE DATABASE `$newDatabase` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci");
        foreach($tables as $cTable){
            self::changeDB ( $newDatabase );
            $create     =   $db->rawQuery("CREATE TABLE $cTable LIKE ".$database.".".$cTable);
            if(!$create) {
                $error  =   true;
            }
            $db->rawQuery("INSERT INTO $cTable SELECT * FROM ".$database.".".$cTable);
        }
        return !isset($error) ? true : false;
    }
}
