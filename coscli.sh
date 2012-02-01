#!/usr/bin/php -e
<?php

define('_COS_PATH', '.');
define('_COS_CLI', 1);
include_once "coslib/shell.php";

mainCli::init();
mainCli::run();

//php ./coslib/shell.php $*
