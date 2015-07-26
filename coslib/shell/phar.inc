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
    
    if (conf::getMainIni('phar_sqlite')) {
        db_to_sqlite();
        cos_exec("cp -R sqlite $build_dir");
        cos_exec("chmod -R 777 $build_dir/sqlite");    
    }
    
    echo "CLI phar executable file created from current source ($output)\n";
    exit(0);
}

function cos_phar_web_create() {
    
    // move sources to build dir. 
    // e.g. build/coscms
    cos_build_simple();
    
    // some base values
    $dir = getcwd();
    $base = basename($dir);
    
    // dir we build phar from
    $build_from_dir = "$dir/build/$base";
    
    // when creating a web phar we add 
    // config.ini
    // this is done so that on first exectution of the
    // phar archive we will create this file and a .sqlite database. 
    
    
    $build_phar_dir = "build/phar";
    if (!file_exists($build_phar_dir)) {
        cos_exec("mkdir $build_phar_dir");
    }

    // hidden .config file
    // cos_exec("cp -f config/config.ini $build_from_dir/tmp/.config.ini");
    // reset database password
    $ary = conf::getIniFileArray("./config/config.ini");
    $profile = new profile();

    // rm secrets
    $ary = $profile->iniArrayPrepare($ary);
    
    // add sqlite database to build phar dir
    if (conf::getMainIni('phar_sqlite')) {
        db_to_sqlite();
    
        // mv sqlite database into hidden file
        cos_exec("cp -R sqlite/database.sql $build_from_dir/tmp/.database.sql");
        cos_exec("chmod 777 $build_from_dir/tmp/.database.sql");
        cos_exec("mkdir $build_from_dir/sqlite");
        
        unset($ary['db_init']);
        $ary['url'] = 'sqlite:.database.sql';
        
    }
    // template_assets::$cacheDir;
    
    // no caching of assets. Place css and js in file
    $ary['cached_assets'] = 0;
    $ary['cashed_assets_reload'] = 0;
    $ary['cached_assets_minify'] = 0;
    $ary['cached_assets_compress'] = 0;
    $ary['cached_assets_inline'] = 1;
    
    if (conf::getMainIni('phar_files')) {
        //cos_needs_root();
        cos_exec("cp -rf htdocs/files $build_from_dir");
        cos_exec("sudo chown -R 777 $build_from_dir/files");
    }
    
    $ini_settings = conf::arrayToIniFile($ary);
    file_put_contents("$build_from_dir/tmp/.config.ini", $ini_settings);
    
    $output = "$build_phar_dir/$base-web.phar";
    $phar = new Phar($output);
    $phar->buildFromDirectory($build_from_dir);
    $stub = $phar->createDefaultStub('phar-web.php', 'phar-web.php');
    $phar->setStub($stub);
    $phar->stopBuffering();

    echo "Web phar executable file created from current source ($output)\n";
    echo "Server it with built-in server like this:\n";
    echo "cd $build_phar_dir\n";
    echo "php -S localhost:8080 $base-web.phar\n";
    exit(0);
}

/**
 * notification flag to other functions
 * @param type $options
 */
function cos_phar_sqlite ($options = array ()) {
    conf::setMainIni('phar_sqlite', 1);
}

/**
 * notification flag to other functions
 * @param type $options
 */
function cos_phar_files ($options = array ()) {
    conf::setMainIni('phar_files', 1);
}

self::setCommand('phar', array(
    'description' => 'Generate phar archieves from current source',
));

self::setOption('cos_phar_sqlite', array(
    'long_name'   => '--sqlite',
    'description' => 'Will add a sqlite3 database into the build dir.',
    'action'      => 'StoreTrue'
));

self::setOption('cos_phar_files', array(
    'long_name'   => '--files',
    'description' => 'Will add a public fils (files/default).',
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

