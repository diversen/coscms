#!/usr/bin/env php
<?php

include_once "vendor/autoload.php";
use diversen\conf;
use diversen\cli;
use diversen\cli\main as mainCli;

$path = dirname(__FILE__);
conf::setMainIni('base_path', $path); 

mainCli::init();
$ret = mainCli::run();
exit($ret);
