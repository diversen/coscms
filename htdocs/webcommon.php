<?php

// webcommon.php
// used when installing or upgrading from web directory
// with a browser

// prevent user abort
set_time_limit(0);
ignore_user_abort(true);
$setup = $path = null;

// test if we have placed coslib outside web directory
if (file_exists('vendor')) {
    $path = realpath('.');
} else {
    $path = realpath('..');
}

// If windows set windows include path
if (DIRECTORY_SEPARATOR != '/') {	
    $path = str_replace ('\\', '/', $path); 
}

// define _COS_PATH and include autoloader
define('_COS_PATH',  $path);

// composer autoload
include _COS_PATH . '/vendor/autoload.php';
use diversen\conf;
use diversen\alias;

alias::set();
conf::defineCommon();
conf::loadMain();
conf::setIncludePath();



$vendor = 'vendor/diversen/simple-php-classes/src';
include_once $vendor ."/shell/common.php";
include_once $vendor. "/shell/profile.php";
include_once $vendor . "/install/common.php";


// check if system is sane
if (!isset($_GET['ignore'])) {
    cos_check_version();
    cos_check_pdo_mysql();
    cos_check_magic_gpc();
    cos_check_files_dir();
}

// try if we can connect to db given in config.ini
try {
    $db = new installDb();
} catch (PDOException $e) {
    echo "Could not connect to db with the data given in config/config.ini. Error";
    die();
}

