#!/usr/bin/env php
<?php

// is cli
define('_COS_CLI', 1);

// deinfe _COS_PATH, base path
$base_dir = dirname(__FILE__);
define('_COS_PATH', $base_dir);

// setup based on _COS_PATH
include_once "coslib/setup.php";
setup::common();

use diversen\cli;

// som paths are set in coscli.sh
class mainCli extends cli{}

// include
//include_once "coslib/mainCli.php";

// init and run
mainCli::init();
$ret = mainCli::run();
exit($ret);
