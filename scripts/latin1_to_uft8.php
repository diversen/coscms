<?php

define('_COS_PATH', realpath('.'));
include_once "coslib/coslibSetup.php";

config::loadMainCli();

$db = new db();
$db->connect();

function get_tables_db () {
    $db = new db();
    $rows = $db->selectQuery('show tables');
    $tables = array();
    foreach ($rows as $table) {
        $tables[] = array_pop($table);
    }
    return $tables;
}

function get_table_create ($table) {
    $db = new db();
    $sql = "DESCRIBE $table";
    return $db->selectQuery($sql);
}



function column_has_text ($ary) {
    $ary['Type'] = trim($ary['Type']);
    if (preg_match("#^varchar#", $ary['Type'])) {
        return true;
    }
    
    if (preg_match("#^text#", $ary['Type']) ) {
        return true;
    }
    
    if (preg_match("#^mediumtext#", $ary['Type']) ) {
        return true;
    }
    
    if (preg_match("#^longtext#", $ary['Type']) ) {
        return true;
    }
    if (preg_match("#^tinytext#", $ary['Type']) ) {
        return true;
        
    }
    return false;
}

$tables = get_tables_db();
foreach ($tables as $table ) {
    $create = get_table_create($table);

    foreach ($create as $column) {
        if (column_has_text($column)) {
            //print_r($column);
            
            echo "Fixing $table:  column $column[Field]\n";
            $query = "ALTER TABLE $table MODIFY $column[Field] $column[Type] character set latin1;";
            $query.= "ALTER TABLE $table MODIFY $column[Field] $column[Type] blob;";
            $query.= "ALTER TABLE $table MODIFY $column[Field] $column[Type] character set utf8;";
            //die;
            $db->rawQuery($query);
            
            //print($column['Type']) . "\n";               
            
        }
    }

    //}
    
}
