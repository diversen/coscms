<?php

function cos_cli_phar_create() {
    $dir = getcwd();
    $base = basename($dir);
    //chdir('..');
    //echo $dir = getcwd();
    //die;
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

self::setCommand('phar', array(
    'description' => 'Commands for generating phar archieves out of current source',
));

self::setOption('cos_cli_phar_create', array(
    'long_name'   => '--cli-create',
    'description' => 'Creates cli archieve from current source',
    'action'      => 'StoreTrue'
));

self::setOption('cos_web_phar_create', array(
    'long_name'   => '--web-create',
    'description' => 'Creates web archieve from current source',
    'action'      => 'StoreTrue'
));
