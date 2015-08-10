<?php

// index
use diversen\boot;
use diversen\conf;

if (file_exists('vendor')) {
    $path = dirname('.');
    include 'vendor/autoload.php';
} else {
    //$path = dirname(__FILE__) . '/..';
    $path = "../";
    include '../vendor/autoload.php';
}

conf::setMainIni('base_path', $path); 
$boot = new boot();
$boot->run();
