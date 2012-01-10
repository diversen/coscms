<?php

/**
 * File contains a simple class for caching content to database table. 
 * @package coslib
 */

/**
 * 
 * class cache. 
 * 3 methods set, get, and delete
 * @package coslib
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
     * @param   int       $lifetime in secs
     * @return  string    $str
     */
    public static function get ($module, $id, $max_life_time = null) {
        $id = self::generateId($module, $id);

        QBuilder::setSelect('system_cache');
        QBuilder::filter('id =', $id);
        $row = QBuilder::fetchSingle();

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