<?php

function db_to_sqlite ($options) {
    die('ok');
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
