<?php


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
     * @return  string    $str
     */
    public static function get ($module, $id) {
        $id = self::generateId($module, $id);

        $db = new db();
        $search = array ('id' => $id);
        $row = $db->selectOne('system_cache', null, $search);
        if ($row){
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
        $id = self::generateId($module, $id);
        $db = new db();
        $values = array ('id' => $id);
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
