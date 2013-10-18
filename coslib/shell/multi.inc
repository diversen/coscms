<?php

/**
 * executes a command on all coscms systems install in parent dir (../) from
 * current dir
 * @param array $options array ('command' => $command);
 * @return int $res
 */
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
        multi_passthru($exec_command);
    }  
}


/**
 * passthru wrapper which print command being passthru'ed
 * @param string $command
 * @return int $res result from passthrou
 */
function multi_passthru ($command) {
    cos_cli_print_status('NOTICE', 'g', "Executing command: $command");
    passthru($command, $ret);
    return $ret;
}

/**
 * executes a shell command on all coscms systems install in parent dir (../) from
 * current dir
 * @param string $command
 * @return int $ret
 */
function multi_exec_shell_command ($options = array ()) {
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
        $exec_command = "cd ../$domain && $command";
        multi_passthru($exec_command);
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

mainCli::setOption('multi_exec_shell_command', array(
    'long_name'   => '--exec-shell',
    'description' => 'Will execute given command on all sites found in path. E.g: ./coscli.sh multi --exec \'git --all-up --master\'',
    'action'      => 'StoreTrue'
));

mainCli::setArgument(
    'command',
    array('description'=> 'Specify a command to execute on all sites',
        'optional' => true,
)); 
