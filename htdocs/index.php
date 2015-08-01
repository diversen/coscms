<?php

/**
 * first entrance for all http request
 * all it does is to include coslib/head.php
 * which then will bootstrap your application
 */


use diversen\boot;
use diversen\conf;

// test if we have placed coslib outside web directory
if (file_exists('vendor')) {
    $path = realpath('.');
} else {
    $path = realpath('..');
}

// make it work on both windows and unix
if (DIRECTORY_SEPARATOR != '/') {
    $path = str_replace ('\\', '/', $path);
}

include $path . '/vendor/autoload.php';
conf::setMainIni('base_path', $path); 

$boot = new boot();
$boot->run();
