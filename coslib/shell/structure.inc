<?php


/**
 * dumps entire structure
 */
function cos_structure_dump () {
    $ary = db_admin::getDbInfo();
    $user = config::getMainIni('username');
    $password = config::getMainIni('password');
    $command = "mysqldump -d -h $ary[host] -u $user -p$password $ary[name]";
    cos_exec($command);
}

/**
 * dump single table structure
 * @param array $options
 */
function cos_structure_dump_table ($options) {
    $ary = db_admin::getDbInfo();
    $user = config::getMainIni('username');
    $password = config::getMainIni('password');
    
    $command = "mysqldump -d -h $ary[host] -u $user -p$password $ary[name] $options[table]";
    cos_exec($command);
}

if (cos_is_cli()){

    mainCli::setCommand('structure', array(
        'description' => 'Dump structure of a db table',
    ));
    
    mainCli::setOption('cos_structure_dump', array(
        'long_name'   => '--db',
        'description' => 'Outputs table structure for complete database',
        'action'      => 'StoreTrue'
    ));

    mainCli::setOption('cos_structure_dump_table', array(
        'long_name'   => '--table',
        'description' => 'Outputs table structure for a single table',
        'action'      => 'StoreTrue'
    ));

    mainCli::setArgument('table',
        array('description'=> 'Specify table to dump structure of',
              'optional' => true));
}
