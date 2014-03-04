<?php

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

    
    


    public static $driver = null;
    /**
     * sets the cache engine
     * @param string $engine
     */
    public static function setDriver($driver) {
        self::$driver = $driver;
    }
    
    /**
     * gets the cache engine
     * @return 
     */
    public static function getDriver () {
        if (!self::$driver) {
            self::$driver = 'db';
        }
        return self::$driver;
    }

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
        $driver = self::getDriver();
        $class = "cache_driver_$driver";
        return $class::get($module, $id, $max_life_time);

    }

    /**
     * sets a string in cache
     * @param   string  $module
     * @param   int     $id
     * @param   string  $data
     * @return  string   $str
     */
    public static function set($module, $id, $data) {
        $driver = self::getDriver();
        $class = "cache_driver_$driver";
        return $class::set($module, $id, $data);
        

    }

    /**
     * delete a string from cache
     * @param   string  $module
     * @param   int     $id
     * @return  boolean $res db result
     */
    public static function delete($module, $id) {
        $driver = self::getDriver();
        $class = "cache_driver_$driver";
        return $class::delete($module, $id) ;
    }

}
