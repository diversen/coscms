<?php

class cache_driver_db  {
    
    public static $table = 'system_cache';
    /**
     * get a cached string from a module and an id
     * the module and the id can be anything, but for the sake of not 
     * cluttering the cache namespace, this is a standard that can be used
     * @param   string    $module
     * @param   int       $id
     * @param   int       $max_life_time in secs
     * @return  mixed     $data unserialized data
     */
    public static function get($module, $id, $max_life_time = null) {
        $id = cache::generateId($module, $id);
        $row = db_q::select(self::$table)->filter('id =', $id)->fetchSingle();

        if (!$row) {
            return null;
        }
        if ($max_life_time) {
            $expire = $row['unix_ts'] + $max_life_time;
            if ($expire < time()) {
                self::delete($module, $id);
                return null;
            } else {
                return unserialize($row['data']);
            }
        } else {
            return unserialize($row['data']);
        }
        return null;
    }

    /**
     * sets a string in cache
     * @param   string  $module
     * @param   int     $id
     * @param   string  $data
     * @return  strin   $str
     */
    public static function set($module, $id, $data) {
        self::delete($module, $id);
        $id = cache::generateId($module, $id);
        $values = array('id' => $id, 'unix_ts' => time());
        $values['data'] = serialize($data);
        return db_q::insert(self::$table)->values($values)->exec();
        
    }

    /**
     * delete a string from cache
     * @param   string  $module
     * @param   int     $id
     * @return  boolean $res db result
     */
    public static function delete($module, $id) {
        $id = cache::generateId($module, $id);
        $row = db_q::select(self::$table)->
                filter('id =', $id)->
                fetchSingle();
        if (!empty($row)) {
            return db_q::delete(self::$table)->
                    filter('id =', $id)->
                    exec();
        }
    }
}
