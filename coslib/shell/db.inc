<?php

/**
 * File containing database functions for shell mode
 *
 * shell database commands are used when using the shell command
 * <code>./coscli.sh db</code>
 * Use -h for help about implemented commands.
 *
 * @package     shell_db
 */

/**
 * shell callback
 * print_r db info
 * @param type $options
 */
function db_show_con_info ($options) {
    $info = db_admin::getDbInfo();
    print_r($info);
}

/**
 * function for creating a database for creds in config.ini
 * @return int $res  the executed commands shell status 0 on success.
 */
function create_db($options = array()){

    $db = db_admin::getDbInfo();
    $command = 
        "mysqladmin -u" . config::$vars['coscms_main']['username'] .
        " -p" . config::$vars['coscms_main']['password'] . " -h$db[host] ";
    
    $command.= "--default-character-set=utf8 ";
    $command.= "CREATE $db[dbname]";
    return $ret = cos_exec($command, $options);
}

/**
 * function for dropping database specified in config.ini
 * @return int $res the executed commands shell status 0 on success. 
 */
function drop_db_default($options = array()){
    define ('NO_DB', 1);    
    $db = db_admin::getDbInfo();
    $command = 
        "mysqladmin -f -u" . config::$vars['coscms_main']['username'] .
        " -p" . config::$vars['coscms_main']['password'] . " -h$db[host] ";
    $command.= "DROP $db[dbname]";
    return $ret = cos_exec($command, $options);
}


/**
 * function for loading db with install sql found in scripts/default.sql
 * this function will also drop database if it exists
 * @return int $res the executed commands shell status 0 on success. 
 */
function load_db_default(){

    $db = db_admin::getDbInfo();
    $command = 
        "mysql -u" . config::$vars['coscms_main']['username'] . ' ' .
        "-p" . config::$vars['coscms_main']['password'] . ' ' .
        "-h$db[host] " . ' ' .
        "$db[dbname] < scripts/default.sql";

    return cos_exec($command);
}


/**
 * function for opening a connection to the database specified in config.ini
 * opens up the mysql command line tool
 * @return  int     the executed commands shell status 0 on success.
 */
function connect_db(){
    $db = db_admin::getDbInfo();

    $command = 
        "mysql --default-character-set=utf8 -u" . config::$vars['coscms_main']['username'] .
        " -p" . config::$vars['coscms_main']['password'] .
        " -h" . $db['host'] . 
        " $db[dbname]";

    $ret = array();
    proc_close(proc_open($command, array(0 => STDIN, 1 => STDOUT, 2 => STDERR), $pipes));
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
function dump_db_file ($options = null){
    if (!isset($options['File'])){
        cos_cli_print('You did not specify file to dump. We create one from current timestamp!');
        $dump_name = "backup/sql/" . time() . ".sql";
    } else {
        $dump_name = $options['File'];
    }
    
    $db = db_admin::getDbInfo();
    $command = 
        "mysqldump --opt -u" . config::$vars['coscms_main']['username'] .
        " -p" . config::$vars['coscms_main']['password'] . 
        " -h" . $db['host'];
    $command.= " $db[dbname] > $dump_name";
    cos_exec($command);
}

/**
 * function for loading a database file into db specified in config.ini
 *
 * @param   array   options. You can specifiy a file to load in options.
 *                  e.g. <code>$options = array('File' => 'backup/sql/latest.sql')</code>
 * @return  int     the executed commands shell status 0 on success.
 */
function load_db_file($options){
    if (!isset($options['File'])){
        cos_cli_print('You did not specify file to load. We use latest!');
        $latest = get_latest_db_dump();
        if ($latest == 0) {
            cos_cli_abort('Yet no database dumps');
        }
        
        $latest = "backup/sql/" . $latest . ".sql";
        $file = $latest;
    } else {
        $file = $options['File'];
        if (!file_exists($file)) {
            cos_cli_abort("No such file: $file");
        }
    }
    $db = db_admin::getDbInfo();
    $command = 
        "mysql --default-character-set=utf8  -u" . config::$vars['coscms_main']['username'] .
        " -p" . config::$vars['coscms_main']['password'] . " $db[dbname] < $file";
    return $ret = cos_exec($command);
}

/**
 * function for getting latest timestamp for dumps
 * 
 * default to backup/sql but you can specify a dir. 
 *
 * @param   string  $dir
 * @return  int     $timestamp
 */
function get_latest_db_dump($dir = null, $num_files = null){
    if (!$dir){
        $dir = _COS_PATH . "/backup/sql";
    }
    $list = file::getFileList($dir);
    $time_stamp = 0;
    foreach ($list as $val){
        $file = explode('.', $val);
        if (is_numeric($file[0])){
            if ($file[0] > $time_stamp){
                $time_stamp = $file[0];
            }
        }
    }
    return $time_stamp;
}

function clone_db ($options = array ()) {
    if (!isset($options['File'])){
        cos_cli_abort('Specify new database name');
    }
    $db = db_admin::getDbInfo();
    $old = $db['dbname'];
    $new_name = $options['File'];
    db_admin::cloneDB($old, $new_name);
}


if (cos_is_cli()){

    mainCli::setCommand('db', array(
        'description' => 'Will manipulate MySQL database in different ways: Backup, restore datbase, open connection etc.',
    ));
    
    mainCli::setOption('db_show_con_info', array(
        'long_name'   => '--show-con-info',
        'description' => 'Show DB connection info',
        'action'      => 'StoreTrue'
    ));

    mainCli::setOption('drop_db_default', array(
        'long_name'   => '--drop-db-default',
        'description' => 'Drops database from settings in config/config.ini (if user is allowed to)',
        'action'      => 'StoreTrue'
    ));

    mainCli::setOption('create_db', array(
        'long_name'   => '--create-db',
        'description' => 'Creates database from settings in config/config.ini (if user is allowed to). Warning: Will drop database if it exists, and create database again.',
        'action'      => 'StoreTrue'
    ));


    mainCli::setOption('load_db_default', array(
        'long_name'   => '--load-db-default',
        'description' => 'Loads database in config/config.ini with default sql (scripts/default.sql). Only the very basic SQL is loaded into database.',
        'action'      => 'StoreTrue'
    ));

    mainCli::setOption('load_db_file', array(
        'long_name'   => '--load-db-file',
        'description' => 'Loads specified file or latest found in backup/sql into db',
        'action'      => 'StoreTrue'
    ));

    mainCli::setOption('connect_db', array(
        'long_name'   => '--connect',
        'description' => 'Connects to database specified in config/config.ini using the mysql shell',
        'action'      => 'StoreTrue'
    ));
    
    mainCli::setOption('clone_db', array(
        'long_name'   => '--clone',
        'description' => 'Clone database. Specify name',
        'action'      => 'StoreTrue'
    ));

    mainCli::setOption ('dump_db_file', array(
        'long_name'   => '--dump-db-file',
        'description' => 'Will try to dump database specified in config/config.ini. If no file is specified then dump will be generated with a unix timestamp.',
        'action'      => 'StoreTrue'
    ));

    mainCli::setArgument('File',
        array('description'=> 'Optional: Specify a path to a file to load into db or DB',
              'optional' => true));
}
