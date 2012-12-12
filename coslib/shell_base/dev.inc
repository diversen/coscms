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

function dev_clear_cache ($options = null) {
    $res = dbQ::setDelete('system_cache')->filter('1 =', 1)->exec();
    if ($res) return 0;
    return 1;
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

function dev_build_pear ($options = null) {
    $name = cos_readline("Enter name of pear lib - default to /usr/share/php [usr/share/php]\n");
    if (empty($name)) $name = '/usr/share/php';
    if (!file_exists('../deps')) mkdir('../deps');
    
    $command = "cp -rf $name/* ../deps/";
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

mainCli::setOption('dev_clear_cache', array(
    'long_name'   => '--clear_db_cache',
    'description' => 'Will clear db cache',
    'action'      => 'StoreTrue'
));

mainCli::setOption('dev_build', array(
    'long_name'   => '--build',
    'description' => 'Will build a distribution from current source',
    'action'      => 'StoreTrue'
));

mainCli::setOption('dev_build_pear', array(
    'long_name'   => '--build-deps',
    'description' => 'Will build a package of dependencies found in /usr/share/php',
    'action'      => 'StoreTrue'
));
