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
function cos_build ($options = null) {
    $name = cos_readline("Enter name of build - usually name of profile. Command make take some time to execute - be patient\n");
    
    if (file_exists("../$name")) {
        die("File or dir with name $name exists\n");
    }
    
    $command = "cp -rf . ../$name";
    cos_exec($command);
    
    $command = "rm `find ../$name -name '.git'` -rf";
    cos_exec($command);
    
    $output = array ();
    exec('git tag -l', $output);
    
    $version = array_pop($output);
    
    $command = "cd  .. && tar -Pczf $name-$version.tar.gz  -v $name ";
    cos_exec($command);
}

/**
 * build source package with more simple form of install. 
 * @param array $options
 */
function cos_build_simple ($options = null) {

    $name = cos_readline("Enter name of build - usually name of profile. Command make take some time to execute - be patient\n");
    $mk_build_dir = "mkdir ./build/$name";
    if (file_exists("./build/$name")) {
        $res = cos_confirm_readline("Delete ./build/$name ");
        if ($res) {
            $rm_build_dir = "rm -rf ./build/$name*";
            cos_exec($rm_build_dir);
            cos_exec($mk_build_dir);
        } else {
            cos_cli_abort('Aborted!');
        }
    } else {
        cos_exec($mk_build_dir);
    }
    
    $htdocs = "cp -rf htdocs/* ./build/$name";
    cos_exec($htdocs);
    
    $domain = config::getMainIni('domain');
    $files_rm = "rm -rf ./build/$name/files/$domain/*";
    cos_exec($files_rm);
    
    $config = "mkdir ./build/$name/config";
    cos_exec($config);
    
    $profiles = "cp -rf profiles ./build/$name";
    cos_exec($profiles);
    
    $sql_scripts = "cp -rf scripts ./build/$name";
    cos_exec($sql_scripts);
    
    $cli = "cp -rf coscli.sh ./build/$name";
    cos_exec($cli);
    
    $misc_scripts = "cp -rf misc ./build/$name";
    cos_exec($misc_scripts);
    
    // reset database password
    $ary = config::getIniFileArray("./config/config.ini");
    $profile = new profile();
    
    $ary = $profile->removeIniSecretsFromArray($ary);
    
    if (isset($ary['password'])) {
        $ary['password'] = '';
    }
    
    // in same dir we place all. 
    $ary['htdocs_path'] = '_COS_PATH';
    
    $ini_settings = config::arrayToIniFile($ary);
    file_put_contents("./build/$name/config/config.ini-dist", $ini_settings);
    
    $coslib = "cp -rf coslib ./build/$name";
    cos_exec($coslib);
    
    $index = "cp -rf misc/alt-index.php ./build/$name/index.php";
    cos_exec($index);
    
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
    
    $output = array ();
    
    exec('git tag -l', $output);
    $version = array_pop($output);
    
    $command = "cd  ./build && tar -Pczf $name-$version.tar.gz  -v $name ";
    cos_exec($command);
}

/**
 * deprecated since all pear packages is included
 * @deprecated since version 1.9xx
 * @param array $options
 */

function cos_build_pear ($options = null) {
    $name = cos_readline("Enter name of pear lib - default to /usr/share/php [usr/share/php]\n");
    if (empty($name)) $name = '/usr/share/php';
    if (!file_exists('../deps')) mkdir('../deps');
    
    $command = "cp -rf $name/* ../deps/";
    cos_exec($command);
    
    $command = "rm ../deps/doc";
    cos_exec($command);
    
    //$command = "rm `find ../$name -name '.git'` -rf";
    //cos_exec($command);
    
    //$output = array ();
    exec('git tag -l', $output);
    
    $version = array_pop($output);
    
    $command = "cd  .. && tar -Pczf deps-$version.tar.gz  -v deps ";
    cos_exec($command);
}

mainCli::setCommand('build', array(
    'description' => 'Command for building a disto based on a single archive . ',
));



mainCli::setOption('cos_build', array(
    'long_name'   => '--build',
    'description' => 'Will build a distribution from current source where coslib is placed outside htdocs',
    'action'      => 'StoreTrue'
));


mainCli::setOption('cos_build_simple', array(
    'long_name'   => '--build-simple',
    'description' => 'Will build a distribution from current source where all files can placed in a single web directory (e.g. htdocs)',
    'action'      => 'StoreTrue'
));

/*
mainCli::setOption('dev_build_pear', array(
    'long_name'   => '--build-deps',
    'description' => 'Will build a package of dependencies found in /usr/share/php',
    'action'      => 'StoreTrue'
));
*/