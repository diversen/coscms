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

// include bootstrap file. 
include _COS_PATH . "/coslib/head.php";
