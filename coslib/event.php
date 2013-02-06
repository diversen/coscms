<?php

/**
 * File contains a very simple of an implementation of an idea which we 
 * call event. 
 *
 * @package    event
 */

/**
 * The class event can be used for making some sort of events or triggers to
 * execute other loaded class methods when we needs to do so. E.g. we can make
 * a event on a user registration which will send an email on successfull 
 * registration. 
 * 
 * e.g. In module account/create we could add the following call in the method
 * accountCreate::verify
 * 
 * event::triggerEvent(array('accountverify::verify'), $res);
 * 
 * were $res is the boolean operation telling if verification is correct or not. 
 * 
 * Then in the module and class accountverify the method verify will be called
 * This method could then send a notification email to site owner about
 * a successfull registration. 
 * 
 * Normal params to use should be like: 
 * 
 * When using commen action for insert update, delete the params should be
 * something like the following. This will insure that modules easy can 
 * work with other modules. 
 * 
 * You will need an action, an reference, and a parent_id, e.g: 
 * array ('action' => 'update, 'reference' => 'blog, '59'); 
 * 
 * 
 * @package    event
 */

class event {
    
    /**
     * gets results from triggered event as an array, where each
     * methods results are placed in the next key => value pair
     * @param array $methods e.g. array ('fb::post', 'twitter::post');
     * @param mixed $args any variable can be used. E.g. array, object or void
     * @return array $ary array with every triggered events result. 
     */
    public static function getTriggerEvent ($methods, $args = null) {
        
        if (!is_array($methods)) return array ();
        $methods = self::prepareMethods($methods);
        
        $ret = array();
        foreach ($methods as $val) {
            $ary = explode('::', $val);
            $module = $class = $ary[0];
            $method = $ary[1];
            if (moduleloader::isInstalledModule($module)) {
                moduleloader::includeModule($module);
                $ret_val = $class::$method($args);
                if ($ret_val) {
                    $ret[] = $ret_val; 
                }
            } else {
                log::debug("No such static method: $class::$method");
            }
        }
        return $ret;
    }

    /**
     * triggerEvent
     * @param array $methods e.g. array ('fb::post', 'twitter::post');
     * @param mixed $args any variable can be used. E.g. array, object or void. 
     *              if args['return'] => array an array will be returned. Else
     *              a string with all return values concatenated will be returned
     * @return string|array anything can be returned object, array. 
     */
    public static function triggerEvent ($methods, $args = null) {
        if (!is_array($methods)) return;

        $methods = self::prepareMethods($methods);       
        $str = '';        
        $ary_ret = array ();
        
        foreach ($methods as $val) {
            $ary = explode('::', $val);
            $module = $class = $ary[0];
            $method = $ary[1];
            if (moduleloader::isInstalledModule($module)) {
                moduleloader::includeModule($module);
                $ret = $class::$method($args);
                if (!empty($ret)) {
                    $ary_ret[] =  $ret;
                    $str.= $ret;
                }
            }
        }      

        if (isset($args['return']) && $args['return'] == 'array') {   
            return $ary_ret;
        }
        return $str;
    }
    
    /**
     * prepare methods if a method is empty it will be removed
     * @param array $methods
     * @return array $methods
     */
    public static function prepareMethods ($methods) {
        foreach ($methods as $key => $val) {
            if (empty($val)) {
                unset($methods[$key]);
            }
        }
        return $methods;
    }
}
