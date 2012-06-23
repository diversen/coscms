<?php


mainCli::setCommand('multi', array(
    'description' => 'Commands used on a multi domain server where multiple hosts share same code base',
));

mainCli::setOption('multi_all_up', array(
    'long_name'   => '--all-up',
    'description' => 'Will upgrade all sites found in config/multi',
    'action'      => 'StoreTrue'
));


function multi_all_up ($options = null) {
    $path = _COS_PATH . "/config/multi/*";    
    $dirs = file::getDirsGlob($path, array ('basename' => 1));
    
    foreach ($dirs as $domain) {
        $command = "./coscli.sh --domain=$domain module --all-up";
        passthru($command, $return_var);
        echo "RETURN = $return_var\n";
    }

    
}
/*
mainCli::setArgument(
    'hostname',
    array('description'=> 'Specify the apache hostname to be used for install or uninstall. yoursite will be http://yoursite',
        'optional' => false,
));*/
