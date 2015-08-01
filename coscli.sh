#!/usr/bin/env php
<?php

include_once "vendor/autoload.php";
use diversen\conf;
use diversen\cli;

define('_COS_CLI', 1);
$path = dirname(__FILE__);
conf::setMainIni('base_path', $path); 

// som paths are set in coscli.sh
class mainCli extends cli{}

// init and run
mainCli::init();
$ret = mainCli::run();
exit($ret);
