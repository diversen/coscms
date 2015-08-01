<?php

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
    } else {
        include "index.php";
    }
}