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

    /**
     * generate a system cache id
     * @param   string    $module
     * @param   int       $id
     * @return  string    $str (md5)
     */
    public static function generateId ($module, $id){
        $str = $module . '_' . $id;
        return md5($str);

    }

    /**
     * get a cached string
     * @param   string    $module
     * @param   int       $id
     * @param   int       $max_life_time in secs
     * @return  string    $str
     */
    public static function get ($module, $id, $max_life_time = null) {
        $id = self::generateId($module, $id);
        
        $row = dbQ::setSelect('system_cache')->filter('id =', $id)->fetchSingle();

        if (!$row) {
            return null;
        }
        if ($max_life_time){
            $expire = $row['unix_ts'] + $max_life_time;
            if ($expire < time()){
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
    public static function set ($module, $id, $data) {
        self::delete($module, $id);

        //db::$dbh->tansaction
        $id = self::generateId($module, $id);
        $db = new db();
        
        $values = array ('id' => $id, 'unix_ts' => time());
        $values['data'] = serialize($data);
        return $db->insert('system_cache', $values);
    }

    /**
     * delete a string from cache
     * @param   string  $module
     * @param   int     $id
     * @return  string  $str
     */
    public static function delete ($module, $id) {
        $id = self::generateId($module, $id);

        $db = new db();
        $search = array ('id' => $id);
        return $db->delete('system_cache', null, $search);
    }
}