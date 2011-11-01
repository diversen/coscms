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
 * @package    coslib
 */

class event {
    
    
    /**
     *
     * @param array $methods e.g. array ('fb::post', 'twitter::post');
     * @param mixed $args any variable can be used. E.g. array, object or void
     * @return mixed anything can be returned object, array. 
     */
    public static function triggerEvent ($methods, $args = null) {     
        foreach ($methods as $key => $val) {
            $ary = explode('::', $val);
            $module = $class = $ary[0];
            $method = $ary[1];
            include_module($module);
            return $class::$method($args);
        }
    }
}
