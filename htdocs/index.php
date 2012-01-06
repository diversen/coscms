<?php

/*
class register {
    public static $vars = array();
}*/

// define a base path
$path = dirname(__FILE__);
$path = realpath($path . "/../");

// make it work on both windows and unix
if (DIRECTORY_SEPARATOR != '/') {	
    $path = str_replace ('\\', '/', $path);
}

//register::$vars['coscms_base'] = $path;
define('_COS_PATH',  $path);

// include head
include _COS_PATH . "/lib/head.php";