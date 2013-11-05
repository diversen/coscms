<?php

/**
 * File containing documentation functions for shell mode
 *
 * @package     shell_dev
 */

/**
 * function for checking if your are denying people 
 * from e.g. admin areas of your module. 
 */
function dev_test_access($options = null){
    
    $files = file::getFileListRecursive(_COS_MOD_PATH, "*.php");
   
    $base_url = "http://" . config::getMainIni('server_name');
    foreach ($files as $val) {
        $url = str_replace(_COS_MOD_PATH, '', $val);
        $url = substr($url, 0, -4);
       
        $url = $base_url . $url;
        $curl = new mycurl($url);
        $curl->createCurl();
       
        echo $curl->getHttpStatus();
        cos_cli_print(" Status code recieved on: $url");       
   }
}

function dev_env($options = null){
    echo config::getEnv() . "\n";
}



mainCli::setCommand('dev', array(
    'description' => 'Dev commands for testing and checking.',
));

mainCli::setOption('dev_test_access', array(
    'long_name'   => '--http-return-codes',
    'description' => 'Will check all web access points and give return code, e.g. 200 or 403 or 404',
    'action'      => 'StoreTrue'
));

mainCli::setOption('dev_env', array(
    'long_name'   => '--env',
    'description' => 'Displays which env you are in (development, stage or production)',
    'action'      => 'StoreTrue'
));