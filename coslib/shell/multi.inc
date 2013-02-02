<?php

function multi_exec_command ($options = null) {
    
    $path = _COS_PATH . "/../";  
    if (!isset($options['command'])) {
        cos_cli_abort('Specify a command');
        return 1;
    }
    
    $command = $options['command'];
    
    $dirs = file::getDirsGlob($path, array ('basename' => 1));
    foreach ($dirs as $domain) {
        if (!file_exists("../$domain/config/config.ini")) {
            continue;
        } 
        $exec_command = "cd ../$domain && ./coscli.sh $command";
        cos_cli_print("Executing command: $exec_command");
        passthru($exec_command, $return_var);
    }  
}

mainCli::setCommand('multi', array(
    'description' => 'Commands used on a multiple domains found in same path',
));

mainCli::setOption('multi_exec_command', array(
    'long_name'   => '--exec',
    'description' => 'Will execute given command on all sites found in path. E.g: ./coscli.sh multi --exec \'git --all-up --master\'',
    'action'      => 'StoreTrue'
));

mainCli::setArgument(
    'command',
    array('description'=> 'Specify a command to execute on all sites',
        'optional' => true,
)); 
