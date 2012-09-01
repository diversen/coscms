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

function multi_exec_command ($options = null) {
    
    $path = _COS_PATH . "/config/multi/*";  
    if (!isset($options['command'])) {
        cos_cli_abort('Specify a command');
        return 1;
    }
    
    $command = $options['command'];
    
    $dirs = file::getDirsGlob($path, array ('basename' => 1));
    foreach ($dirs as $domain) {
        $exec_command = "./coscli.sh --domain=$domain $command";
        cos_cli_print("Executing command: $exec_command");

        passthru($exec_command, $return_var);
    }  
}

mainCli::setCommand('multi-shared', array(
    'description' => 'Commands used on a multi domain server where multiple hosts share same code base',
));

mainCli::setOption('multi_all_up', array(
    'long_name'   => '--all-up',
    'description' => 'Will upgrade all sites found in config/multi',
    'action'      => 'StoreTrue'
));

mainCli::setOption('multi_exec_command', array(
    'long_name'   => '--exec',
    'description' => 'Will execute given command on all multi sites. E.g: ./coscli.sh multi --exec \'module --mod-down meta\'',
    'action'      => 'StoreTrue'
));

mainCli::setArgument(
    'command',
    array('description'=> 'Specify a command to execute on all sites',
        'optional' => true,
)); 
