#!/usr/bin/env php
<?php

define('_COS_CLI', 1);
$base_dir = dirname(__FILE__);
define('_COS_PATH', $base_dir);

include_once "coslib/mainCli.php";

mainCli::init();
$ret = mainCli::run();
exit($ret);