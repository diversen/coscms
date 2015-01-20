<?php

/**
 * router for self-contained phar-archive
 * used with php built-in router. 
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

// If cli-server
// This is only meant for PHP built-in server
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

