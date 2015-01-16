<?php

use diversen\db\admin;

function db_to_sqlite ($options) {
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
    
    print_r($info);
    $username = config::getMainIni('username');
    $password = config::getMainIni('password');
    $command = "sequel ";
    $command.= "$info[scheme]://$username:$password@$info[host]/$info[dbname] ";
    $command.= "-C ";
    
    ob_implicit_flush();
    $command.= "sqlite://sqlite/database.sql";
    //ob_clean();
    $ret = 1;
    $ret = cos_system($command);
    
    if (!$ret) {
        cos_system("chmod -R 777 sqlite");
        cos_cli_print('Sqlite database created. Edit config.ini and add:'); 
        cos_cli_print("sqlite://sqlite/database.sql");
    }
    //
    // sequel mysql://root:password@localhost/database -C sqlite://sqlite/database.sql
    
}


if (config::isCli()){

    self::setCommand('sqlite', array(
        'description' => 'Sqlite database commands.',
    ));
    
    self::setOption('db_to_sqlite', array(
        'long_name'   => '--mysql-to-sqlite',
        'description' => 'Create a sqlite3 database from current MySQL database. Will be placed in sqlite/database.sql',
        'action'      => 'StoreTrue'
    ));

}
