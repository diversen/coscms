<?php

/**
 * router for self-contained phar-archive
 * used with php built-in router. 
 * TODO: Should work with sqlite
 */
// Phar::interceptFileFuncs();
try {
    Phar::mount('config/config.ini', 'config/config.ini');
    Phar::mount('sqlite/database.sql', 'sqlite/database.sql');
} catch (Exception $e) {
    echo $e->getMessage();
    die();
} 

if (php_sapi_name() == 'cli-server') {
    $info = parse_url($_SERVER['REQUEST_URI']);
    if (file_exists( "./$info[path]") && $info['path'] != '/') {
        return false;
    } else {
        include_once "index.php";
        return true;
    }
}

__HALT_COMPILER();
