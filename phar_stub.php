<?php

/**
 * phar stub used when creating a phar file
 * of all coscms file
 * see: /bin/phar_create.php
 */
Phar::interceptFileFuncs();

define('_COS_CLI', 1);
$base_dir = dirname(__FILE__);
define('_COS_PATH', $base_dir);

$ini_path = ini_get('include_path');
ini_set('include_path', 
    _COS_PATH . PATH_SEPARATOR . 
        $ini_path . PATH_SEPARATOR);

include_once "coslib/mainCli.php";

mainCli::init();
mainCli::run();

