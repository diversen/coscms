<?php

// webcommon.php
// used when installing or upgrading from web directory using a browser

// Accept no time limit and ignore user abort
set_time_limit(0);
ignore_user_abort(true);

// set a base path and include autoloader
// include autoload
if (file_exists('vendor')) {
    $path = dirname(__FILE__);
    include 'vendor/autoload.php';
} else {
    $path = dirname(__FILE__) . '/..';
    include '../vendor/autoload.php';
}

use diversen\conf;
use diversen\alias;

// set base_path in conf
conf::setMainIni('base_path', $path); 

// set alias. common defines. Load config. Set include paths
alias::set();
conf::defineCommon();
conf::loadMain();
conf::setIncludePath();

// include som install helpers
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
