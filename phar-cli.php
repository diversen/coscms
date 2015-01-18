<?php

/**
 * phar stub used when creating a phar CLI file
 * to create a phar file of current tree, use:
 * ./coscli.sh phar -h
 */
//Phar::interceptFileFuncs();
try {
    //Phar::mount('config/config.ini', '../config/config.ini');
    Phar::mount('config/config.ini', 'config/config.ini');
    Phar::mount('sqlite/database.sql', 'sqlite/database.sql');
} catch (Exception $e) {
    echo $e->getMessage();
    die();
} 

define('_COS_CLI', 1);
$base_dir = dirname(__FILE__);
define('_COS_PATH', $base_dir);

$ini_path = ini_get('include_path');
ini_set('include_path', 
    _COS_PATH . PATH_SEPARATOR . 
        $ini_path . PATH_SEPARATOR);


// setup based on _COS_PATH
include_once "coslib/setup.php";
setup::common();
include_once "coslib/mainCli.php";

mainCli::init();
mainCli::run();
