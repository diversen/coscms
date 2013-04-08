<?php

// webcommon.php
// used when installing or upgrading from web directory
// with a browser

// prevent user abort
set_time_limit(0);
ignore_user_abort(true);
$setup = $path = null;

// test if we have placed coslib outside web directory
if (file_exists('./coslib/coslibSetup.php')) {
    $setup = "./coslib/coslibSetup.php";
    $path = realpath('.');
} else {
    $setup = "../coslib/coslibSetup.php";
    $path = realpath('..');
}

// If windows set windows include path
if (DIRECTORY_SEPARATOR != '/') {	
    $path = str_replace ('\\', '/', $path); 
}

// define _COS_PATH and include autoloader
define('_COS_PATH',  $path);
include_once $setup;

// load main ini
// define constants
config::loadMain();
config::defineCommon();

include_once "coslib/shell/common.inc";
include_once "coslib/shell/profile.inc";
include_once "coslib/webinstall/common.php";

// check if system is sane
cos_check_version();
cos_check_pdo_mysql();
cos_check_magic_gpc();
cos_check_files_dir();

// try if we can connect to db given in config.ini
try {
    $db = new installDb();
} catch (PDOException $e) {
    echo "Could not connect to db with the data given in config/config.ini. Error";
    die();
}