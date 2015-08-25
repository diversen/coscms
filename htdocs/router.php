<?php

use diversen\conf;
use diversen\http;
use diversen\file;
use diversen\boot;

if (file_exists('vendor')) {
    $path = '.';
    include 'vendor/autoload.php';
} else {
    $path = "..";
    include '../vendor/autoload.php';
}

conf::setMainIni('base_path', $path); 

// Set real path
$real = realpath($path);

// PAth to current request
$info = parse_url($_SERVER['REQUEST_URI']);

// Does htdocs dir exists
if (file_exists($real . "/htdocs")) {
    $real.= "/htdocs" . "/$info[path]";   
}

// Get full requst path
if (file_exists($real) && $info['path'] != '/') {

	//echo $real; die;
    $mime = file::getMime($real);
    //die;
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
