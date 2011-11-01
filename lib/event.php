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
 * Let's use this example to illustrate: 
 * 
 * We have the class called createAccount found in module account/create. 
 * In this class there is a method called verifyAccount()
 * 
 * In order to implement an event in this class we just add 
 * the following event call in accountCreate::verifyAccount() after user
 * account has been verified:
 * 
 * event::triggerEvent('accountCreate', 'verifyAccount', $row) or
 * 
 * 1. param is the class name where the event will be triggered.
 *   or just  __CLASS__ the class were we are
 * 2. param is the method of the class were the event should be triggered
 *    or just the method were we are __METHOD__ were we call triggerEvent
 * 3. last argument is a row (array) which the triggered event method will use
 *    as argument.  
 * 
 * The we can create a module called e.g. accountnotify were we will add
 * the following method in e.g. the top of the model file. A better way would
 * be to implement the call in in a runLevel method or maybe even better 
 * let the module accountnotify load only when the accountCreate module is used.
 * In all cases the accountnotify module will need to be loaded before the module
 * where triggerEvent is called 
 * 
 * Whatever: We will need to notify the event class
 * 
 * event::setEvent('accountCreate', 'verifyAccount', 'accountnotify', 'notify');
 * 
 * And then in the accountnotify class in the method notify we use the following 
 * method call: 
 * 
 * public static function notify ($row) {
 *     // ... code ...
 *     mail_utf8($row['email'], $subject, $message, $from)
 *     // ... code ...
 *     return $ret
 * }
 * 
 * @package    coslib
 */

class event {
    /**
     * 
     * @var array $register register for holding events 
     */
    public static $register = array();
    
    /**
     * init register in order to avoid warnings when no evetns are used
     */
    public static function init() {
        self::$register['events'] = array();
    }
    
    /**
     *
     * method for setting an event
     * @param type $class the clss to trigger on
     * @param type $method the method to trigger on 
     * @param type $trigger_class the trigger class
     * @param type $trigger_event the trigger method
     */
    public static function setEvent ($class, $method, $trigger_class, $trigger_method) {
        static $i = 0;
        self::$register['events'][$i] = 
                array (
                    'class' => $class, 
                    'method' => $method,
                    'trigger_class' => $trigger_class,
                    'trigger_method' => $trigger_method
                    );
        $i++;
    }
    
    /**
     * method for triggering an event
     * @param type $class the class to trigger an event on
     * @param type $method the method to trigger the event for
     * @param type $args the argument to the triggered method
     * @return type $the return value of the triggered method. 
     */
    public static function triggerEvent ($class, $method, $args ) {
        // check if a intercept class exists
        foreach (self::$register['events'] as $key => $val) {
            if ($val['class'] == $class && $val['method'] == $method) {
                $class_to_call = $val['trigger_class'];
                $method_to_call = $val['trigger_method'];
                return $class_to_call::$method_to_call($args);                
            }
        }
    }
}