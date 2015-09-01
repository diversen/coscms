<?php

// index
use diversen\boot;
use diversen\conf;
use diversen\file\path;

if (file_exists('vendor')) {
    $path = '.';
    include 'vendor/autoload.php';
} else {
    $path = "..";
    include '../vendor/autoload.php';
}

$path = path::truepath($path);
conf::setMainIni('base_path', $path); 

$boot = new boot();
$boot->run();
