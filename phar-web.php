<?php

/**
 * router for self-contained phar-archive
 * used with php built-in router. 
 * TODO: Should work with sqlite
 */
Phar::interceptFileFuncs();


try {
    Phar::mount('config/config.ini', '.config.ini');
    Phar::mount('sqlite/database.sql', '.database.sql');
} catch (Exception $e) {
    //echo $e->getMessage();
    $str = file_get_contents('tmp/.config.ini');
    file_put_contents('.config.ini', $str);
    $str = file_get_contents('tmp/.database.sql');
    file_put_contents('.database.sql', $str);
    chmod('.database.sql', 0777);    
    Phar::mount('config/config.ini', '.config.ini');
    Phar::mount('sqlite/database.sql', '.database.sql');

    //die('created .config and .database.sql');
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

