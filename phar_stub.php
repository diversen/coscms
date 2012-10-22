<?php

Phar::interceptFileFuncs();

define('_COS_CLI', 1);
$base_dir = dirname(__FILE__);
define('_COS_PATH', $base_dir);
$ini_path = ini_get('include_path');
ini_set('include_path', 
    _COS_PATH . PATH_SEPARATOR . 
        $ini_path . PATH_SEPARATOR);
//ini_set('include_path', $base_dir);
//Phar::mount('./backup/sql', $base_dir . '/backup/sql');

include_once "coslib/head.php";
include_once "coslib/shell.php";
mainCli::init();
mainCli::run();

