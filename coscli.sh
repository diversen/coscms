#!/usr/bin/env php
<?php

// is cli
define('_COS_CLI', 1);

// define _COS_PATH, base path
$base_dir = dirname(__FILE__);
define('_COS_PATH', $base_dir);

include_once "vendor/autoload.php";

//setup::common();
use diversen\alias;
use diversen\autoloader\modules;
use diversen\conf;

$m = new modules();
$m->autoloadRegister();


alias::set();

// define all constant - based on _COS_PATH and config.ini
conf::defineCommon();

// load config file 
conf::load();

// set include path - based on config.ini
conf::setIncludePath();

// set log level - based on config.ini
log::setLogLevel();
        
// set locales
intl::setLocale();
        
// set default timezone
intl::setTimezone();


use diversen\cli;

// som paths are set in coscli.sh
class mainCli extends cli{}

// init and run
mainCli::init();
$ret = mainCli::run();
exit($ret);
