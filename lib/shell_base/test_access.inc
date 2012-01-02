<?php

/**
 * File containing documentation functions for shell mode
 *
 * @package     shell
 */

/**
 * @ignore
 */
include_once "lib/mycurl.php";
include_once "lib/moduleloader.php";

/**
 * function for checking if your are denying people 
 * from e.g. admin areas of your module. 
 */
function test_access($options = null){
    
   $module_dir = _COS_PATH . "/modules";
   $files = get_file_list_recursive($module_dir, "*.php");
   
   $base_url = "http://" . get_main_ini('server_name');
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
// }}}


mainCli::setCommand('test-access', array(
    'description' => 'Dev command for checking if allow people where they are not allowed. ',
));

mainCli::setOption('test_access', array(
    'long_name'   => '--check',
    'description' => 'Will check all web access points and give return code, e.g. 200 or 403',
    'action'      => 'StoreTrue'
));


