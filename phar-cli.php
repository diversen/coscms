<?php

/**
 * phar stub used when creating a phar CLI file
 * to create a phar file of current tree, use:
 * ./coscli.sh phar -h
 */

try {

    // mount config and database.sq outside phar archive
    // first arg is internal file (which must not exist)
    // second arg is external file
    Phar::mount('config/config.ini', '.config.ini');
    Phar::mount('sqlite/database.sql', '.database.sql');
} catch (Exception $e) {

    // Exception is that .config.ini and .database.sql does not exists
    // we make them
    $str = file_get_contents('tmp/.config.ini');
    file_put_contents('.config.ini', $str);
    $str = file_get_contents('tmp/.database.sql');
    file_put_contents('.database.sql', $str);
    chmod('.database.sql', 0777);  

    // And mount again  
    Phar::mount('config/config.ini', '.config.ini');
    Phar::mount('sqlite/database.sql', '.database.sql');
} 

include_once "vendor/autoload.php";
use diversen\conf;
use diversen\cli;

define('_COS_CLI', 1);
$path = dirname(__FILE__);
conf::setMainIni('base_path', $path); 

// som paths are set in coscli.sh
class mainCli extends cli{}

// init and run
mainCli::init();
$ret = mainCli::run();
exit($ret);
