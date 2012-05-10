<?php

/**
 * File contains a very simple of an implementation of an idea which we 
 * call event. 
 *
 * @package    coslib
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
 * @package    coslib
 */

class event {
    
    //public static $ret = array();
    
    /**
     *
     * @param array $methods e.g. array ('fb::post', 'twitter::post');
     * @param mixed $args any variable can be used. E.g. array, object or void
     * @return mixed anything can be returned object, array. 
     */
    public static function getTriggerEvent ($methods, $args = null) {
        if (!is_array($methods)) return;
        //if (isset(event::$ret)) unset(event::$ret);
        $methods = self::prepareMethods($methods);
        $ret = array();
        foreach ($methods as $key => $val) {
           
            $ary = explode('::', $val);
            $module = $class = $ary[0];
            $method = $ary[1];
            moduleLoader::includeModule($module);
            $ret[] = $class::$method($args);
            //$ret[] = $ret;
        }
        return $ret;
    }
    /**
     *
     * @param array $methods e.g. array ('fb::post', 'twitter::post');
     * @param mixed $args any variable can be used. E.g. array, object or void
     * @return mixed anything can be returned object, array. 
     */
    public static function triggerEvent ($methods, $args = null) {
        if (!is_array($methods)) return;

        
        $methods = self::prepareMethods($methods);
       
        $str = '';
        $i = count($methods);
        
        $ary_ret = array ();
        
        foreach ($methods as $key => $val) {
            $ary = explode('::', $val);
            $module = $class = $ary[0];
            $method = $ary[1];
            moduleLoader::includeModule($module);
            $ret = $class::$method($args);
            if (!empty($ret)) {
                $ary_ret[] =  $ret;
                $str.= $ret;
            }
        }      

        if (isset($args['return']) && $args['return'] == 'array') {   
            return $ary_ret;
        }
        return $str;
    }
    
    public static function prepareMethods ($methods) {
        foreach ($methods as $key => $val) {
            if (empty($val)) {
                unset($methods[$key]);
            }
        }
        return $methods;
    }
}
