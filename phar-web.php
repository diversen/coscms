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

    function getMime($path) {
        $result = false;
        if (is_file($path) === true) {
            if (function_exists('finfo_open') === true) {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                if (is_resource($finfo) === true) {
                    $result = finfo_file($finfo, $path);
                }
                finfo_close($finfo);
            } else if (function_exists('mime_content_type') === true) {
                $result = preg_replace('~^(.+);.*$~', '$1', mime_content_type($path));
            } else if (function_exists('exif_imagetype') === true) {
                $result = image_type_to_mime_type(exif_imagetype($path));
            }
        }
        return $result;
    }

// If cli-server
// This is only meant for PHP built-in server
//Phar::mungServer(array('REQUEST_URI'));
//Phar::webPhar(null, 'index.php');

if (php_sapi_name() == 'cli-server') {
    $info = parse_url($_SERVER['REQUEST_URI']);
    $file = $info['path'];
    if (file_exists( "./$info[path]") && $info['path'] != '/') {
        
        $full = __DIR__ . "$file"; 
        if (!file_exists($full) OR is_dir($full) ) {
            echo "Is dir. Or does not exists";
            return false;
        }
        
        
        $mime = getMime($full);
        
        if ($mime) {
            if ($mime == 'text/x-php') {
                return false;
            }
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

