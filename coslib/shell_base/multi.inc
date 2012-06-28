<?php

function multi_all_up ($options = null) {
    $path = _COS_PATH . "/config/multi/*";    
    $dirs = file::getDirsGlob($path, array ('basename' => 1));
    //print_r($options); die;
    
    include_module('siteclone');
    foreach ($dirs as $domain) {
        
        $command = "./coscli.sh --domain=$domain module --all-up";
        cos_cli_print("Updating: $domain");
        cos_cli_print($command);
        passthru($command, $return_var);
        
        $command = "./coscli.sh --domain=$domain install --lang";
        
        cos_cli_print("Updating system language: $domain");
        cos_cli_print($command);
        passthru($command, $return_var);
        
    }
}

function multi_mod_in ($options = null) {
    
    $path = _COS_PATH . "/config/multi/*";  
    if (!isset($options['module'])) {
        cos_cli_abort('Specify a module');
        return 1;
    }
    
    $module = $options['module'];
    
    $dirs = file::getDirsGlob($path, array ('basename' => 1));
    foreach ($dirs as $domain) {
        $command = "./coscli.sh --domain=$domain module --mod-in $module";
        cos_cli_print("Installing module: $module");
        cos_cli_print($command);
        passthru($command, $return_var);
    }
    
}

mainCli::setCommand('multi', array(
    'description' => 'Commands used on a multi domain server where multiple hosts share same code base',
));

mainCli::setOption('multi_all_up', array(
    'long_name'   => '--all-up',
    'description' => 'Will upgrade all sites found in config/multi',
    'action'      => 'StoreTrue'
));

mainCli::setOption('multi_mod_in', array(
    'long_name'   => '--mod-in',
    'description' => 'Will install module for all multi sites',
    'action'      => 'StoreTrue'
));

mainCli::setArgument(
    'module',
    array('description'=> 'Specify the module to install',
        'optional' => true,
)); 
