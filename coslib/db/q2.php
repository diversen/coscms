<?php

/**
 * File contains contains extension of db_q providing
 * shorthand options of db_q
 * e.g. select = setSelect 
 *
 * @package    db
 */

/**
 * Class contains contains extension of db_q providing
 * shorthand options of db_q
 * e.g. select = setSelect 
 * 
 * @package    db
 */

class db_q2 extends db_q {
    /**
     * short hand of setSelect
     * @param string $table
     * @param string $fields
     * @return object $db_q
     */
    public static function select ($table, $fields = null){
        return self::setSelect($table, $fields);
        
    }
    
    /**
     * short hand of setSelectNumRows
     * @param string $table
     * @return object $db_q
     */
    public static function numRows ($table){
        return self::setSelectNumRows($table);
    }
    
    /**
     * prepare for a delete query
     * @param string $table the table to delete from
     */
    public static function delete ($table){
        return self::setDelete($table);
    }
    
        /**
     * prepare for an update query statement
     * @param type $table 
     */
    
    public static function update ($table) {
        return self::setUpdate($table);
    }
    
        /**
     * prepare for insert
     * @param type $table the table to insert values into
     */
    public static function insert ($table) {
        return self::setInsert($table);
    }
    
        /**
     * set values for insert or update. 
     * @param array $values the values to insert
     * @param array $bind array with types to bind values to
     */
    public static function values ($values, $bind = array()) {
        return self::setInsertValues($values, $bind);
    }
    

}
