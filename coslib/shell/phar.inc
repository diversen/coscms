<?php

function cos_phar_cli_create() {
    $dir = getcwd();
    $base = basename($dir);
    $phar = new Phar("../$base.phar");
    $phar->interceptFileFuncs();
    $phar->buildFromDirectory($dir);
    $stub = $phar->createDefaultStub('phar_cli.php');
    $phar->setStub($stub);
    $stub = "#!/usr/bin/env php \n" . $stub;
    $phar->setStub($stub);
    $phar->stopBuffering();
    echo "cli phar created. Make it executable and run it with: ./$dir.phar\n";
    exit(0);
}

function cos_phar_web_create() {
    
    
    $dir = getcwd();
    $base = basename($dir);
    $phar = new Phar("../$base.phar");
    $phar->interceptFileFuncs();
    $phar->buildFromDirectory(dirname(__FILE__) . "/$dir");
    $stub = $phar->createDefaultStub('router.php');
    $phar->setStub($stub);
    $phar->stopBuffering();

    echo "Web phar created. Make it executable and run it with: ./$dir.phar\n";
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
