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

$error = null;
if (isset($argv[1])) {
    $dir = $argv[1];
} else {
    $error = 1;
}


if (!file_exists($dir)){
    $error = 1;
}

if ($error) {
    $str.= "Usage: $argv[0] coscms-path";
    $str.= "The CosCMS {$dir} source not found in current directory\n";
    die($str); 
}


$phar = new Phar("$dir.phar", 0, "$dir.phar");
$phar->interceptFileFuncs();
$phar->buildFromDirectory(dirname(__FILE__) . "/$dir");
$stub = $phar->createDefaultStub('index.php');
$phar->setStub($stub);
$phar->stopBuffering();

echo "web phar created. Make it executable and run it with: ./$dir.phar\n";
exit(0);
