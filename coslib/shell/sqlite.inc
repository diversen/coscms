<?php

use diversen\db\admin;
use Symfony\Component\Filesystem\Filesystem;

function db_to_sqlite ($options = array ()) {
    
    $check = "which sequel";
    if (cos_exec($check)) {
        cos_cli_print('You need sequel. Install it like this, e.g.:');
        cos_cli_print('sudo aptitude install ruby-sequel libsqlite3-ruby libmysql-ruby');
        cos_cli_abort();
    } else {
        cos_cli_print_status('OK' , 'g','Sequel is installed' );
    }
    
    $ok = false;
    $info = admin::getDbInfo();
    if ($info['scheme'] == 'mysql') {
        $ok = true; 
    } 
    if ($info['scheme'] == 'mysqli') {
        $ok = true;
    }
    
    if (!$ok) {
        cos_cli_print_status('ERROR', 'r', 'Driver needs to be mysql or mysqli');
    }
    
    $fs = new Filesystem();
    $fs->remove('sqlite/database.sql');
    
    $username = conf::getMainIni('username');
    $password = conf::getMainIni('password');
    $command = "sequel ";
    $command.= "$info[scheme]://$username:$password@$info[host]/$info[dbname] ";
    $command.= "-C ";
    $command.= "sqlite://sqlite/database.sql";

    $ret = cos_system($command);
    
    if (!$ret) {
        $fs->chmod('sqlite/database.sql', 0777, 0000, true);
        cos_cli_print('Sqlite database created. Edit config.ini and add:'); 
        cos_cli_print("sqlite://sqlite/database.sql");
    }    
}

if (conf::isCli()){

    self::setCommand('sqlite', array(
        'description' => 'Sqlite database commands.',
    ));
    
    self::setOption('db_to_sqlite', array(
        'long_name'   => '--mysql-to-sqlite',
        'description' => 'Create a sqlite3 database from current MySQL database. Will be placed in sqlite/database.sql',
        'action'      => 'StoreTrue'
    ));
}
