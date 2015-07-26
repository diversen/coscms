<?php

use diversen\db\admin as db_admin;
/**
 * dumps entire structure
 */
function cos_structure_dump () {
    $ary = db_admin::getDbInfo();
    $user = conf::getMainIni('username');
    $password = conf::getMainIni('password');
    $command = "mysqldump -d -h $ary[host] -u $user -p$password $ary[dbname]";
    cos_exec($command);
}

/**
 * dump single table structure
 * @param array $options
 */
function cos_structure_dump_table ($options) {
    $ary = db_admin::getDbInfo();
    $user = conf::getMainIni('username');
    $password = conf::getMainIni('password');
    
    $dump_dir = "backup/sql/$options[table]";
    if (!file_exists($dump_dir)) {
        mkdir($dump_dir);
    }
    
    $dump_name = "backup/sql/$options[table]/" . time() . ".sql";
    
    $command = "mysqldump -d -h $ary[host] -u $user -p$password $ary[dbname] $options[table] > $dump_name";
    cos_exec($command);
}

/**
 * function for dumping a database specfied in config.ini to a file
 *
 * @param   array   Optional. If you leave empty, then the function will try
 *                  and find most recent sql dump and load it into database.
 *                  Set <code>$options = array('File' => '/backup/sql/latest.sql')</code>
 *                  for setting a name for the dump.
 * @return  int     the executed commands shell status 0 on success.
 */
function cos_db_dump_table ($options = null){

    if (!isset($options['table'])) {
        cos_cli_abort('Specify a table to backup');
    }
    
    $dump_dir = "backup/sql/$options[table]";
    if (!file_exists($dump_dir)) {
        mkdir($dump_dir);
    }
    
    $dump_name = "backup/sql/$options[table]/" . time() . ".sql";
    
    $db = db_admin::getDbInfo();
    $command = 
        "mysqldump --opt -u" . conf::$vars['coscms_main']['username'] .
        " -p" . conf::$vars['coscms_main']['password'];
    $command.= " $db[dbname] $options[table] > $dump_name";
    cos_exec($command);
}

/**
 * function for loading a database file into db specified in config.ini
 *
 * @param   array   options. You can specifiy a file to load in options.
 *                  e.g. <code>$options = array('File' => 'backup/sql/latest.sql')</code>
 * @return  int     the executed commands shell status 0 on success.
 */
function cos_db_load_table($options){
    
    
    if (!isset($options['table'])) {
        cos_cli_abort('Specify a table to load with a backup');
    }
    
    $dump_dir = "backup/sql/$options[table]";
    if (!file_exists($dump_dir)) {
        cos_cli_abort('Yet no backups');
    }
    
    $search = _COS_PATH . "/backup/sql/$options[table]";
    $latest = get_latest_db_dump($search);
    if ($latest == 0) {
        cos_cli_abort('Yet no database dumps');
    }
        
    $latest = "backup/sql/$options[table]/" . $latest . ".sql";
    
    $db = db_admin::getDbInfo();
    $command = 
        "mysql --default-character-set=utf8  -u" . conf::$vars['coscms_main']['username'] .
        " -p" . conf::$vars['coscms_main']['password'] . " $db[dbname] < $latest";
    return $ret = cos_exec($command);
}

if (conf::isCli()){

    self::setCommand('structure', array(
        'description' => 'Dump structure of a db table',
    ));
    
    self::setOption('cos_structure_dump', array(
        'long_name'   => '--db',
        'description' => 'Outputs table structure for complete database',
        'action'      => 'StoreTrue'
    ));

    self::setOption('cos_structure_dump_table', array(
        'long_name'   => '--table',
        'description' => 'Outputs table structure for a single table',
        'action'      => 'StoreTrue'
    ));
    
    self::setOption('cos_db_dump_table', array(
        'long_name'   => '--backup-table',
        'description' => 'Backup single DB table',
        'action'      => 'StoreTrue'
    ));
    
    self::setOption('cos_db_load_table', array(
        'long_name'   => '--load-table',
        'description' => 'Create single table from latest backup',
        'action'      => 'StoreTrue'
    ));

    self::setArgument('table',
        array('description'=> 'Specify table to dump structure of',
              'optional' => true));
}
