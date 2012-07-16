<?php

/**
 * File containing database functions for shell mode
 *
 * shell database commands are used when using the shell command
 * <code>$ coscli.sh db</code>
 * Use -h for help about implemented commands.
 *
 * @package     shell
 */

include_once "coslib/shell_base/common.inc";
/*
 *
 * @return array $database containing array('name' => database, 'host' => 'host')
 */
function get_db_info(){
    $url = parse_url(config::$vars['coscms_main']['url']);
    $ary = explode (';', $url['path']);
    $db = explode ("=", $ary[0]);
    $database = array();
    $database['name'] = $db[1];
    $host = explode ("=", $ary[1]);
    $database['host'] = $host[1];
    return $database;
}

// {{{ create_db() 
/**
 * function for creating a database for creds in config.ini
 *
 * @return  int     the executed commands shell status 0 on success.
 */
function create_db($options = array()){

    $db = get_db_info();
    $command = 
        "mysqladmin -u" . config::$vars['coscms_main']['username'] .
        " -p" . config::$vars['coscms_main']['password'] . " -h$db[host] ";
    
    $command.= "--default-character-set=utf8 ";
    $command.= "CREATE $db[name]";
    return $ret = cos_exec($command, $options);
}

// }}}
// {{{ drop_db

/**
 * function for dropping database specified in config.ini
 *
 * @return  int     the executed commands shell status 0 on success. 
 */
function drop_db_default($options = array()){
    define ('NO_DB', 1);    
    $db = get_db_info();
    $command = 
        "mysqladmin -f -u" . config::$vars['coscms_main']['username'] .
        " -p" . config::$vars['coscms_main']['password'] . " -h$db[host] ";
    $command.= "DROP $db[name]";
    return $ret = cos_exec($command, $options);
}

// }}}
// {{{ load_db
/**
 * function for loading db with install sql found in scripts/default.sql
 *
 * @return  int     the executed commands shell status 0 on success. 
 */
function load_db_default(){
    // silence when dropping db. 
    drop_db_default($options = array ('silence' => 1));
    create_db();
    $db = get_db_info();
    $command = 
        "mysql -u" . config::$vars['coscms_main']['username'] .
        " -p" . config::$vars['coscms_main']['password'] ." $db[name] < scripts/default.sql";
    $ret = cos_exec($command);
}
// }}} 
// {{{ connect_db();

/**
 * function for opening a connection to the database specified in config.ini
 *
 * opens up the mysql command line tool
 *
 * @return  int     the executed commands shell status 0 on success.
 */
function connect_db(){
    $db = get_db_info();
    $command = 
        "mysql -u" . config::$vars['coscms_main']['username'] .
        " -p" . config::$vars['coscms_main']['password'] . " $db[name]";

    $ret = array();
    proc_close(proc_open($command, array(0 => STDIN, 1 => STDOUT, 2 => STDERR), $pipes));
}

// }}}
// {{{ dump_db()
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
        echo $dump_name = $options['File'];
        
     }
    
    $db = get_db_info();
    $command = 
        "mysqldump --opt -u" . config::$vars['coscms_main']['username'] .
        " -p" . config::$vars['coscms_main']['password'];
    $command.= " $db[name] > $dump_name";
    cos_exec($command);
}

// }}}
// {{{ load_db_file ($options)
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
        $latest = "backup/sql/" . $latest . ".sql";
        $file = $latest;
    } else {
        $file = $options['File'];
        if (!file_exists($file)) {
            cos_cli_abort("No such file: $file");
        }
    }
    $db = get_db_info();
    $command = 
        "mysql -u" . config::$vars['coscms_main']['username'] .
        " -p" . config::$vars['coscms_main']['password'] . " $db[name] < $file";
    return $ret = cos_exec($command);

}

// }}}
// {{{ _get_latest
/**
 * function for getting latest timestamp for dumps found in backup/sql
 *
 * @return  int     the executed commands shell status 0 on success.
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

// }}}

if (cos_is_cli()){

    mainCli::setCommand('db', array(
        'description' => 'Will manipulate database in different ways: Backup, restore datbase, open connection etc.',
    ));

    mainCli::setOption('drop_db_default', array(
        'long_name'   => '--drop-db-default',
        'description' => 'Drops database from settings in config/config.ini (if user is allowed to)',
        'action'      => 'StoreTrue'
    ));

    mainCli::setOption('create_db', array(
        'long_name'   => '--create-db',
        'description' => 'Creates database from settings in config/config.ini (if user is allowed to) ',
        'action'      => 'StoreTrue'
    ));


    mainCli::setOption('load_db_default', array(
        'long_name'   => '--load-db-default',
        'description' => 'Loads database in config/config.ini with default sql (scripts/default.sql)',
        'action'      => 'StoreTrue'
    ));

    mainCli::setOption('load_db_file', array(
        'long_name'   => '--load-db-file',
        'description' => 'Loads specified file or latest into db',
        'action'      => 'StoreTrue'
    ));

    mainCli::setOption('connect_db', array(
        'long_name'   => '--connect',
        'description' => 'Will try to connect to database specified in config/config.ini',
        'action'      => 'StoreTrue'
    ));

    mainCli::setOption ('dump_db_file', array(
        'long_name'   => '--dump-db-file',
        'description' => 'Will try to dump database specified in config/config.ini',
        'action'      => 'StoreTrue'
    ));

    mainCli::setArgument('File',
        array('description'=> 'Specify the file to load into db',
              'optional' => true));
}