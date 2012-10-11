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

mainCli::setCommand('dev', array(
    'description' => 'Dev command for checking if allow people where they are not allowed. ',
));

mainCli::setOption('dev_test_access', array(
    'long_name'   => '--check-access',
    'description' => 'Will check all web access points and give return code, e.g. 200 or 403',
    'action'      => 'StoreTrue'
));

mainCli::setOption('dev_test', array(
    'long_name'   => '--test',
    'description' => 'test',
    'action'      => 'StoreTrue'
));

mainCli::setOption('dev_clear_cache', array(
    'long_name'   => '--clear_db_cache',
    'description' => 'Will clear db cache',
    'action'      => 'StoreTrue'
));


