#!/usr/bin/env php
<?php

include_once "vendor/autoload.php";
use diversen\conf;
use diversen\cli;

$path = dirname(__FILE__);
conf::setMainIni('base_path', $path); 

// commands are called with mainCli. 
class mainCli extends cli{}

// init and run
mainCli::init();
$ret = mainCli::run();
exit($ret);
