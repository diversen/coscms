<?php

include_once "vendor/autoload.php";

use diversen\git;

$public = "https://github.com/jeremykendall/php-domain-parser.git";

$private = "git@github.com:diversen/account.git";

/*
echo git::getPublicFromPrivate($public);
echo PHP_EOL;

echo git::getPublicFromPrivate($private);
echo PHP_EOL;
*/
//die;
echo git::getSshFromHttps($public);
echo PHP_EOL;
