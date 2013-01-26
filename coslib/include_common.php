<?php


/**
 * initialize base system if you just want to use classes
 * Runs both web system and commandline system.
 * 
 * 
 * @package  head
 */

/**
 * set include path
 * @ignore
 */
$ini_path = ini_get('include_path');
ini_set('include_path', 
    _COS_PATH . PATH_SEPARATOR . 
    _COS_PATH . '/vendor' . PATH_SEPARATOR .
    _COS_PATH . "/coslib" . PATH_SEPARATOR . _COS_PATH . '/modules' . 
        $ini_path . PATH_SEPARATOR);



/**
 * specific composer autoload
 */
include_once 'vendor/autoload.php';

/**
 * coslib autoloader
 * @param type $classname
 */
function coslib_autoloader($classname) {
    $classname = ltrim($classname, '\\');
    $filename  = '';
    $namespace = '';
    if ($lastnspos = strripos($classname, '\\')) {
        $namespace = substr($classname, 0, $lastnspos);
        $classname = substr($classname, $lastnspos + 1);
        $filename  = str_replace('\\', '/', $namespace) . '/';
    }
    $filename = str_replace('_', '/', $classname) . '.php';
    include_once $filename;
}

/**
 * register the autoload on the stack
 */
spl_autoload_register('coslib_autoloader');

/**
 * we will now be able to use all coslib classes and all vendor classes
 */