#!/usr/bin/env php
<?php

define('_COS_CLI', 1);
$base_dir = dirname(__FILE__);
define('_COS_PATH', $base_dir);

include_once "vendor/autoload.php";

use diversen\cli;

// som paths are set in coscli.sh
class mainCli extends cli{}

// init and run
mainCli::init();
$ret = mainCli::run();
exit($ret);
