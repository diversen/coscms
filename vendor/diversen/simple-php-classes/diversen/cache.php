<?php

namespace diversen;
use diversen\db\q as db_q;
/**
 * File contains a simple class for caching content to database table. 
 * @package cache
 */

/**
 * 
 * class cache. 3 methods set, get, and delete
 * 
 * When setting something with the cache class you will specify a string and
 * a ID. Name is given in order to prevent class of identical ID's only.
 * This can be e.g. the module name if your module uses the cache.  
 * 
 * When the class gets a name and ID it generates a unique string 
 * which is the internal cache key. 
 * 
 * When retrieving information you will only need to specify the 
 * a original (e.g. the module name) and the ID. 
 * 
 * <code>
 * 
 * $data = array ('title' => 'test', 'content' => 'some content');
 * 
 * 
 * cache::set('my_blog' 123, $data);
 * 
 * // getting the entry is just as easy. 
 * // The last argument is max life time in seconds for the entry. 
 * cache::get('my_blog', 123, 3600);
 * 
 * // if the entry is outdated NULL will be returned
 * 
 * </code>
 * 
 * 
 * @package cache
 * 
 */
class cache {


    /**
     * generate a system cache id
     * @param   string    $module
     * @param   int       $id
     * @return  string    $str (md5)
     */
    public static function generateId($module, $id) {
        $str = $module . '_' . $id;
        return md5($str);
    }

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
        return self::getDb($module, $id, $max_life_time);
    }

    /**
     * sets a string in cache
     * @param   string  $module
     * @param   int     $id
     * @param   string  $data
     * @return  string   $str
     */
    public static function set($module, $id, $data) {
        return self::setDb($module, $id, $data);
        

    }

    /**
     * delete a string from cache
     * @param   string  $module
     * @param   int     $id
     * @return  boolean $res db result
     */
    public static function delete($module, $id) {
        return self::deleteDb($module, $id) ;
    }
    
        
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
    private static function getDb($module, $id, $max_life_time = null) {
        $id = self::generateId($module, $id);
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
    private static function setDb($module, $id, $data) {
        self::delete($module, $id);
        $id = self::generateId($module, $id);
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
    private static function deleteDb($module, $id) {
        $id = self::generateId($module, $id);
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
