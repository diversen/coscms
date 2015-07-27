<?php

/**
 * router for self-contained phar-archive
 * used with php built-in router. 
 */

phar::interceptFileFuncs();
include_once 'vendor/autoload.php';
echo $path = "phar://" . realpath('.') . "/modules"; echo "<br />";
set_include_path( $path . PATH_SEPARATOR . get_include_path());
echo get_include_path();

use diversen\file;
use diversen\http;

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

if (php_sapi_name() == 'cli-server') {
    $info = parse_url($_SERVER['REQUEST_URI']);
    $file = $info['path'];
    if (file_exists( "./$info[path]") && $info['path'] != '/') {
        
        $full = __DIR__ . "$file"; 
        if (!file_exists($full) OR is_dir($full) ) {
            echo "Is dir. Or does not exists";
            return false;
        }
        
        $mime = file::getMime($full);
        
        if ($mime) {
            if ($mime == 'text/x-php') {
                return false;
            }
            http::cacheHeaders();
            header("Content-Type: $mime");
            readfile($full);
        }
        //return false;
    } else {
        include_once "index.php";
        //return true;
    }
}

__HALT_COMPILER();
