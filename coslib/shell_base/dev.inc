<?php

/**
 * File containing documentation functions for shell mode
 *
 * @package     shell
 */

/**
 * @ignore
 */
include_once "coslib/mycurl.php";

/**
 * function for checking if your are denying people 
 * from e.g. admin areas of your module. 
 */
function dev_test_access($options = null){
    
    $module_dir = _COS_PATH . "/modules";
    $files = file::getFileListRecursive($module_dir, "*.php");
   
    $base_url = "http://" . config::getMainIni('server_name');
    foreach ($files as $val) {
        $url = str_replace($module_dir, '', $val);
        $url = substr($url, 0, -4);
       
        $url = $base_url . $url;
        $curl = new mycurl($url);
        $curl->createCurl();
       
        echo $curl->getHttpStatus();
        echo cos_cli_print(" Status code recieved on: $url");       
   }
}


function dev_test ($options = null) {
    //include_module('account');
    //echo config::getModuleIni('account_allow_create');
}


function dev_build ($options = null) {
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
function dev_build_simple ($options = null) {

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
    
    $config = "cp -rf config ./build/$name";
    cos_exec($config);
    
    $profiles = "cp -rf profiles ./build/$name";
    cos_exec($profiles);
    
    $sql_scripts = "cp -rf scripts ./build/$name";
    cos_exec($sql_scripts);
    
    // reset database password
    $ary = config::getIniFileArray("./config/config.ini");
    if (isset($ary['password'])) {
        $ary['password'] = '';
    }
    
    // in same dir we place all. 
    $ary['htdocs_path'] = '_COS_PATH';
    
    $ini_settings = config::arrayToIniFile($ary);
    file_put_contents("./build/$name/config/config.ini", $ini_settings);
    
    $coslib = "cp -rf coslib ./build/$name";
    cos_exec($coslib);
    
    $index = "cp -rf misc/alt-index.php ./build/$name/index.php";
    cos_exec($index);
    
    $modules = "cp -rf modules ./build/$name";
    cos_exec($modules);
    
    $vendor = "cp -rf vendor ./build/$name";
    cos_exec($vendor);
    
    
    $rm_git = "rm `find ./build/$name -name '.git'` -rf";
    cos_exec($rm_git);
    
    $rm_ignore = "rm `find ./build/$name -name '.gitignore'` -rf";
    cos_exec($rm_ignore);
    
    $pear = cos_readline("Enter name of pear lib - default to /usr/share/php [/usr/share/php]\n");
    if (empty($pear)) $pear = '/usr/share/php';

    
    $cp_pear = "cp -rf $pear/* ./build/$name/vendor";
    cos_exec($cp_pear);
    
    $rm_doc = "rm -rf ./build/vendor/doc";
    cos_exec($rm_doc);
    
    $output = array ();
    
    // get latest tag.
    exec('git tag -l', $output);
    $version = array_pop($output);
    
    $command = "cd  ./build && tar -Pczf $name-$version.tar.gz  -v $name ";
    cos_exec($command);
}

function dev_build_pear ($options = null) {
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

mainCli::setCommand('dev', array(
    'description' => 'Dev command. ',
));

mainCli::setOption('dev_test_access', array(
    'long_name'   => '--http-return-codes',
    'description' => 'Will check all web access points and give return code, e.g. 200 or 403 or 404',
    'action'      => 'StoreTrue'
));


mainCli::setOption('dev_build', array(
    'long_name'   => '--build',
    'description' => 'Will build a distribution from current source',
    'action'      => 'StoreTrue'
));


mainCli::setOption('dev_build_simple', array(
    'long_name'   => '--build-simple',
    'description' => 'Will build a distribution from current source where all files are placed in a single directory',
    'action'      => 'StoreTrue'
));

mainCli::setOption('dev_build_pear', array(
    'long_name'   => '--build-deps',
    'description' => 'Will build a package of dependencies found in /usr/share/php',
    'action'      => 'StoreTrue'
));
