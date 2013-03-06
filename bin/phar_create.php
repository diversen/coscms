<?php

/**
 * copy this file to e.g. ../your-install-dir
 * then run 
 *
 * php ./phar-create.php your-install-dir
 * 
 * Then you should can an executable named:
 * your-install-dir.phar
 * 
 * You can now run the phar archive by using: 
 *
 * php your-install-dir.phar
 *
 */
if (isset($argv[1])) {
    $dir = $argv[1];
} else {
    $dir = 'coscms';
}

if (!file_exists($dir)){
    $str = "The CosCMS {$dir} source not found in current directory\n";
    $str.= "You can set source folder by doing a phar_create.php folder\n";
    die($str); 
}

$phar = new Phar("$dir.phar", 0, "$dir.phar");
$phar->interceptFileFuncs();
$phar->buildFromDirectory(dirname(__FILE__) . "/$dir");
$phar->setStub($phar->createDefaultStub('phar_stub.php'));

