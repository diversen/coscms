<?php

/**
 * first entrance for all http request
 * all it does is to include coslib/head.php
 * which then will bootstrap your application
 */

// define a base path
$path = dirname(__FILE__);

// make it work on both windows and unix
if (DIRECTORY_SEPARATOR != '/') {
    $path = str_replace ('\\', '/', $path);
}

// define _COS_PATH.
define('_COS_PATH',  $path);

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
include 'vendor/autoload.php';

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
    include $filename;
}

/**
 * register the autoload on the stack
 */
spl_autoload_register('coslib_autoloader');

// include bootstrap file. 
include _COS_PATH . "/coslib/head.php";
