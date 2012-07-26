<?php

/**
 * File contains RB (Redbeans) helpers for easy connecting to CosCMS 
 * DB using Redbeans
 * @package rb helpers 
 */

/**
 * class contains class RB with a connect method, which creates a Redbean
 * connection from a CosCMS string. Also a a couple of methods used for
 * helping creating beans from arrays  
 * @package rb helpers
 */

class RB {
    
    /**
     * setup a Redbean instance from CosCMS
     */
    public static function connect () {
        static $connected = null;
        
        if (!$connected){
            include_once "rb.php";
            $url = config::getMainIni('url');
            $username = config::getMainIni('username');
            $password = config::getMainIni('password');
            R::setup($url, $username,$password); //mysql
            $connected = true;
        } 
    }
    
    /**
     * method for transforming an array into a bean
     * @param object $bean
     * @param array $ary
     * @return object $bean 
     */
    public static function arrayToBean ($bean, $ary) {
        foreach ($ary as $key => $val) {
            $bean->{$key} = trim($val);
        }
        return $bean;
    }
    
    /**
     * helper function for getting a bean. It searches for an existing bean
     * if not found it create a new bean
     * @param string $table
     * @param string $field
     * @param mixed $search
     * @return object $bean 
     */
    public static function getBean ($table, $field = null, $search = null) {
        if (isset($field) && isset($search)) {
            $needle = R::findOne($table," 1 AND $field  = ?", array( $search ));
        } else {
            $needle = null;
        }
        
        if (empty($needle)) {
            //log::file ("NOTICE: DISPENSE: table {$table} field {$field} search {$search}");
            $bean = R::dispense( $table );
        } else {
            //log::file ("NOTICE: LOADING: table {$table} field {$field} search {$search}");
            $bean = R::load($table, $needle->id);
        }
        return $bean;
    }

    /**
     * deletes beans with transactions
     * @param object $beans 
     */
    public static function deleteBeans ($beans) {
        R::begin();
        try{
            R::trashAll($beans);   
            R::commit();
        } catch(Exception $e) {
            R::rollback();
        }
    }

    /**
     * commit a bean with transactions
     * @param object $bean 
     */
    public static function commitBean ($bean) {

        R::begin();
        try{
            R::store($bean);
            R::commit();
        } catch(Exception $e) {
            R::rollback();
        }
    }
}