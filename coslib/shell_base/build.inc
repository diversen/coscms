<?php

/**
 * File containing documentation functions for shell mode
 *
 * @package     shell
 */


mainCli::setCommand('build', array(
    'description' => 'apache2 commands. Install, remove hosts.',
));

mainCli::setOption('build_pear', array(
    'long_name'   => '--pear',
    'description' => 'Will build all packages a splace them in build/pear.tar.gz',
    'action'      => 'StoreTrue'
));

mainCli::setOption('build_cos', array(
    'long_name'   => '--coscms',
    'description' => 'Will build a full package of coscms without the git repos',
    'action'      => 'StoreTrue'
));

function build_pear ()  {
    $command = "tar";
}

mainCli::setArgument(
    'hostname',
    array('description'=> 'Specify the apache hostname to be used for install or uninstall. yoursite will be http://yoursite',
        'optional' => false,
));
