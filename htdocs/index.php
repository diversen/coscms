<?php

// index
use diversen\boot;
use diversen\conf;

if (file_exists('vendor')) {
    $path = realpath('.');
    include 'vendor/autoload.php';
} else {
    $path = realpath('..');
    include '../vendor/autoload.php';
}

conf::setMainIni('base_path', $path); 
$boot = new boot();
$boot->run();
