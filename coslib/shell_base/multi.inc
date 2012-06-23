<?php

function multi_all_up ($options = null) {
    
} 

mainCli::setCommand('multi', array(
    'description' => 'Commands used on a multi domain server where multiple hosts share same code base',
));

mainCli::setOption('multi_all_up', array(
    'long_name'   => '--all-up',
    'description' => 'Will upgrade all sites found in config/multi',
    'action'      => 'StoreTrue'
));

/*
mainCli::setArgument(
    'hostname',
    array('description'=> 'Specify the apache hostname to be used for install or uninstall. yoursite will be http://yoursite',
        'optional' => false,
));*/
