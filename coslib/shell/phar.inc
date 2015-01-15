<?php

function cos_phar_cli_create() {
    
    $dir = getcwd();
    $base = basename($dir);
    cos_build_simple();
    
    $dir = getcwd();
    $base = basename($dir);
    $output = "../$base-cli.phar";
    $phar = new Phar($output);
    $phar->interceptFileFuncs();
    //$phar->buildFromDirectory($dir);
    $phar->buildFromDirectory("$dir/build/$base");
    $stub = $phar->createDefaultStub('phar-cli.php');
    $phar->setStub($stub);
    $stub = "#!/usr/bin/env php \n" . $stub;
    $phar->setStub($stub);
    $phar->stopBuffering();
    cos_exec("chmod +x $output");
    echo "CLI phar executable file created from current source ($output)\n";
    exit(0);
}

function cos_phar_web_create() {
    
    $dir = getcwd();
    $base = basename($dir);
    cos_build_simple();
    
    $output = "../$base-web.phar";
    $phar = new Phar($output);
    $phar->interceptFileFuncs();
    $phar->buildFromDirectory("$dir/build/$base");
    $stub = $phar->createDefaultStub('misc/alt-router.php');
    $phar->setStub($stub);
    $phar->stopBuffering();

    echo "Web phar executable file created from current source ($output)\n";
    echo "Server it with built-in server like this:\n";
    echo "php -S localhost:8080 $output\n";
    exit(0);
}

self::setCommand('phar', array(
    'description' => 'Commands for generating phar archieves out of current source',
));

self::setOption('cos_phar_cli_create', array(
    'long_name'   => '--cli-create',
    'description' => 'Creates cli archieve from current source',
    'action'      => 'StoreTrue'
));

self::setOption('cos_phar_web_create', array(
    'long_name'   => '--web-create',
    'description' => 'Creates web archieve from current source',
    'action'      => 'StoreTrue'
));
