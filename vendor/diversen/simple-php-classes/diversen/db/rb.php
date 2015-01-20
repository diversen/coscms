<?php

namespace diversen\db;
use \diversen\conf as config;
/**
 * File contains RB (Redbeans) helpers for easy connecting to CosCMS 
 * DB using Redbeans
 * @package db
 */

/**
 * @ignore
 */
include_once "rb.php";
use RedBean_Facade as R;

/**
 * override default RB model formatter
 * @ignore
 */
class MyModelFormatter implements \RedBean_IModelFormatter {

    public function formatModel($model) {
        //return $model.'_Object';
    }

}

/**
 * class db_rb contains some helpers methods for RB. 
 * Methods for connecting, converting array to beans
 * 
 * example: 
 * db_rb::connect();
 * $bean = db_rb::getBean('test');
 * $bean = db_rb::arrayToBean($bean, $_POST);
 * r::store($bean);
 * @package db
 * 
 */
class rb {
        
    /**
     * setup a Redbean instance from CosCMS
     */
    public static function connect () {
        static $connected = null;
        
        if (!$connected){           
            
            $url = config::getMainIni('url');
            $username = config::getMainIni('username');
            $password = config::getMainIni('password');
            R::setStrictTyping(false); 
            $formatter = new MyModelFormatter;
            \RedBean_ModelHelper::setModelFormatter($formatter);
            
            R::setup($url, $username,$password); //mysql
            $freeze = config::getMainIni('rb_freeze');
            if ($freeze == 1) {
                R::freeze(true);
            }
            $connected = true;
        } 
    }
    
    /**
     * method for transforming an array into a bean
     * @param object $bean
     * @param array $ary
     * @param boolean $skip_null if true we skip values that is not set (e.g. null)
     *                           if false we don't skip null - but add them to bean
     * @return object $bean 
     */
    public static function arrayToBean ($bean, $ary, $skip_null = true) {
        foreach ($ary as $key => $val) {
            if (!isset($val) && $skip_null)  { 
                continue;
            }
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
            $bean = R::dispense( $table );
        } else {
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
        } catch(\Exception $e) {
            R::rollback();
        }
    }

    /**
     * commit a bean with transactions
     * @param object $bean
     * @return $res false or last insert id 
     */
    public static function commitBean ($bean) {
        R::begin();
        try{
            $res = R::store($bean);
            R::commit();
        } catch(\Exception $e) {
            R::rollback();
            $res = false;
        }
        return $res;
    }
}