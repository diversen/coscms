<?php

/**
 * File containing documentation functions for shell mode
 *
 * @package     shell
 */


// {{{ create_docs()
/**
 * wrapper function for creating documention with phpdoc
 * hi! I'am created with this function :)
 *
 * @return int  value from cos_system command
 */
function create_docs(){
    // check if command exists
    $command = "whereis phpdoc";
    $ret = cos_exec($command);
    if ($ret){
        cos_cli_print("Could not find command phpdoc on your system");
        cos_cli_print("If the command phpdoc is not on your system we will not be able to create documentation.");
        cos_cli_print("One way to do this is to: pear install PhpDocumentor");
        exit(127);
    }

    $command = "phpdoc run ";
    $command.= "-d coslib ";
    $command.= "--template old-ocean -t " . _COS_PATH . "/htdocs/phpdocs ";
    cos_system($command);
}

// }}}

mainCli::setCommand('doc', array(
    'description' => 'Command for creating documentation',
));

mainCli::setOption('create_docs', array(
    'long_name'   => '--create-docs',
    'description' => 'Will make phpdoc documentation. Will be found in htdocs/phpdoc',
    'action'      => 'StoreTrue'
));

