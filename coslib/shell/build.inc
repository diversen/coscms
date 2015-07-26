<?php

/**
 * File containing documentation functions for shell mode
 *
 * @package     shell
 */

/**
 * build a distro where all placed is normal layout
 * @param array $options
 */
function cos_build($options = null) {
    $name = cos_readline("Enter name of build - usually name of profile. Command make take some time to execute - be patient\n");

    if (file_exists("../$name")) {
        die("File or dir with name $name exists\n");
    }

    $command = "cp -rf . ../$name";
    cos_exec($command);

    $command = "rm `find ../$name -name '.git'` -rf";
    cos_exec($command);

    $output = array();
    exec('git tag -l', $output);

    $version = array_pop($output);

    $command = "cd  .. && tar -Pczf $name-$version.tar.gz  -v $name ";
    cos_exec($command);
}

/**
 * build source package with more simple form of install. 
 * @param array $options
 */
function cos_build_simple($options = null) {

    $dir = getcwd();
    $name = basename($dir);

    if (file_exists("./build/$name")) {
        cos_exec("rm -rf ./build/$name*");
    }
    cos_exec("mkdir ./build/$name");

    $htdocs = "cp -rf htdocs/* ./build/$name";
    cos_exec($htdocs);

    $domain = conf::getMainIni('domain');
    if (!$domain) {
        $domain = 'default';
    }
    $files_rm = "rm -rf ./build/$name/files/$domain/*";
    cos_exec($files_rm);

    $config = "mkdir ./build/$name/config";
    cos_exec($config);

    $tmp_dir = "mkdir ./build/$name/tmp";
    cos_exec($tmp_dir);
    
    $profiles = "cp -rf profiles ./build/$name";
    cos_exec($profiles);

    $sql_scripts = "cp -rf scripts ./build/$name";
    cos_exec($sql_scripts);

    $cli = "cp -rf coscli.sh ./build/$name";
    cos_exec($cli);
    
    $composer = "cp -rf composer.json ./build/$name";
    cos_exec($composer);

    $misc_scripts = "cp -rf misc ./build/$name";
    cos_exec($misc_scripts);

    // reset database password
    $ary = conf::getIniFileArray("./config/config.ini");
    $profile = new profile();

    $ary = $profile->iniArrayPrepare($ary);

    // clean ini settings for secrets
    $ini_settings = conf::arrayToIniFile($ary);

    // add ini dist file
    file_put_contents("./build/$name/config/config.ini-dist", $ini_settings);

    $coslib = "cp -rf coslib ./build/$name";
    cos_exec($coslib);

    $index = "cp -rf misc/alt-index.php ./build/$name/index.php";
    cos_exec($index);

    $phar_cli = "cp -rf phar-cli.php ./build/$name/";
    cos_exec($phar_cli);
    
    $phar_web = "cp -rf phar-web.php ./build/$name/";
    cos_exec($phar_web);

    $module_dir = _COS_MOD_DIR;
    $modules = "cp -rf $module_dir ./build/$name";
    cos_exec($modules);

    $vendor = "cp -rf vendor ./build/$name";
    cos_exec($vendor);

    $rm_git = "rm `find ./build/$name -name '.git'` -rf";
    cos_exec($rm_git);

    $rm_ignore = "rm `find ./build/$name -name '.gitignore'` -rf";
    cos_exec($rm_ignore);

    $rm_doc = "rm -rf ./build/vendor/doc";
    cos_exec($rm_doc);

    $output = array();

    exec('git tag -l', $output);
    $version = array_pop($output);

    $command = "cd  ./build && tar -Pczf $name-$version.tar.gz $name ";
    cos_exec($command);
}

self::setCommand('build', array(
    'description' => 'Build distros',
));

self::setOption('cos_build', array(
    'long_name' => '--build',
    'description' => 'Will build a distribution from current source where coslib is placed outside htdocs',
    'action' => 'StoreTrue'
));

self::setOption('cos_build_simple', array(
    'long_name' => '--build-simple',
    'description' => 'Will build a distribution from current source where all files can placed in a single web directory (e.g. htdocs)',
    'action' => 'StoreTrue'
));
