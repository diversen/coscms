<?php

use diversen\conf;
use diversen\http;
use diversen\file;
use diversen\boot;
use diversen\file\path;

if (file_exists('vendor')) {
    $path = '.';
    include 'vendor/autoload.php';
} else {
    $path = "..";
    include '../vendor/autoload.php';
}

$real = path::truepath($path);
conf::setMainIni('base_path', $real); 

// Path to current request
$info = parse_url($_SERVER['REQUEST_URI']);

if (preg_match('/\.(?:css|js)$/', $_SERVER["REQUEST_URI"])) {
    return false;    // serve the requested resource as-is.
}

// Does htdocs dir exists
if (file_exists($real . "/htdocs")) {
    $real.= "/htdocs" . "/$info[path]";   
}

// Get full requst path
if (file_exists($real) && $info['path'] != '/') {

    $mime = file::getMime($real);
    if ($mime) {
        if ($mime == 'text/x-php') {
            return false;
        }
        http::cacheHeaders();
        header("Content-Type: $mime");
        readfile($real);
    }
    die();
    //return false;
} else {

    $boot = new boot();
    $boot->run();
    return true;
}
return false;
