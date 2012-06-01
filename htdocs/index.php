<?php

/**
 * first entrance for all http request
 * all it does is to include coslib/head.php
 * which then will bootstrap your application
 */

// define a base path
$path = dirname(__FILE__);
$path = realpath($path . "/../");

// make it work on both windows and unix
if (DIRECTORY_SEPARATOR != '/') {	
    $path = str_replace ('\\', '/', $path);
}

// define _COS_PATH. Only major constant
define('_COS_PATH',  $path);

// include bootstrap file. 
include _COS_PATH . "/coslib/head.php";
