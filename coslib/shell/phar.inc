<?php

use Symfony\Component\Filesystem\Filesystem;

function cos_phar_cli_create() {

    $dir = getcwd();
    $base = basename($dir);
    
    // build from source
    cos_build_simple();
    $build_dir = "tmp/phar";
    
    $fs = new Filesystem();
    $fs->mkdir($build_dir, 0755);
    
    $output = "$build_dir/$base-cli.phar";
    $phar = new Phar($output);
    $phar->interceptFileFuncs();
    $phar->buildFromDirectory("$dir/build/$base");
    $stub = $phar->createDefaultStub('phar-cli.php');
    $phar->setStub($stub);
    $stub = "#!/usr/bin/env php \n" . $stub;
    $phar->setStub($stub);
    $phar->stopBuffering();
    
    cos_exec("chmod +x $output");
    cos_exec("cp -R config $build_dir");
    
    if (config::getMainIni('phar_sqlite')) {
        db_to_sqlite();
        cos_exec("cp -R sqlite $build_dir");
        cos_exec("chmod -R 777 $build_dir/sqlite");    
    }
    
    echo "CLI phar executable file created from current source ($output)\n";
    exit(0);
}

function cos_phar_web_create() {
    
    $dir = getcwd();
    $base = basename($dir);
    cos_build_simple();
    
    $build_dir = "tmp/phar";
    
    $fs = new Filesystem();
    $fs->mkdir($build_dir, 0755);
    
    $output = "$build_dir/$base-web.phar";
    $phar = new Phar($output);
    $phar->interceptFileFuncs();
    $phar->buildFromDirectory("$dir/build/$base");
    $stub = $phar->createDefaultStub('phar-web.php');
    $phar->setStub($stub);
    $phar->stopBuffering();
    
    cos_exec("cp -R config $build_dir");
    if (config::getMainIni('phar_sqlite')) {
        db_to_sqlite();
        cos_exec("cp -R sqlite $build_dir");
        cos_exec("chmod -R 777 $build_dir/sqlite");    
    }

    echo "Web phar executable file created from current source ($output)\n";
    echo "Server it with built-in server like this:\n";
    echo "php -S localhost:8080 $output\n";
    exit(0);
}

function cos_phar_sqlite ($options) {
    config::setMainIni('phar_sqlite', 1);
}

self::setCommand('phar', array(
    'description' => 'Generate phar archieves from current source',
));

self::setOption('cos_phar_sqlite', array(
    'long_name'   => '--sqlite',
    'description' => 'Will add a sqlite3 database into the build dir.',
    'action'      => 'StoreTrue'
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

/*
self::setArgument('sqlite',
    array('description'=> 'Specicify the module or template for which you will make a translation. If spcified in collect command, then the module is where the collected translation are found',
          'optional' => false));
*/