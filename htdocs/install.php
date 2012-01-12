<?php

class config {
    public static $vars = array();
}

config::$vars['coscms_main'] = array();

$path = realpath('..');
if (DIRECTORY_SEPARATOR != '/') {	
	$path = str_replace ('\\', '/', $path); 
}

define('_COS_PATH',  $path);

// global array used as registry for holding debug info
$_COS_DEBUG = array();

// start timer
$_COS_DEBUG['timer']['start'] = microtime();

// set base path in debug info
$_COS_DEBUG['cos_base_path'] = _COS_PATH;

// set include path
$ini_path = ini_get('include_path');
ini_set('include_path',
    _COS_PATH . PATH_SEPARATOR . "." . PATH_SEPARATOR .
    _COS_PATH . '/lib/vendor' . PATH_SEPARATOR .
    _COS_PATH . "/lib");

// parse main config.ini file
$_COS_DEBUG['include_path'] = ini_get('include_path');

include_once "common.php";

// register::$vars['coscms_main'] is used as a register for holding global settings.
config::$vars['coscms_main'] = config::getIniFileArray(_COS_PATH . '/config/config.ini', true);

include_once "db.php";
include_once "moduleInstaller.php";

/**
 *
 * @param <string> $module name of module to be installed.
 */
function install_module($module){
    $install = new moduleInstaller($module);
    $ret = $install->install();
    if (!$ret) {
        print $install->error . "<br />";
    } else {
        print $install->confirm . "<br />";
    }
}

/**
 * class installDb
 * Only method change is connect.
 * We don't try and catch in the e class
 */
class installDb extends db {
    public $sql;
    
    function __construct(){
        // make sure we have a connection
        $this->connect();
    }

    function readSql(){
        $file = _COS_PATH . '/scripts/default.sql';
        $this->sql = file_get_contents($file);
        return $this->sql;
    }
}


$version = "5.2.0";
if (version_compare( $version, phpversion(), ">=")) {
    echo 'We need PHP version 5.2.0 or higher, my version: ' . PHP_VERSION . " ERROR<br>";
} else {
    echo "Version " . phpversion() . " OK. We need $version<br />"; 
}



$ary = get_loaded_extensions();
if (in_array('PDO', $ary)){
    echo "PDO is installed. OK<br>";
}

if (in_array('pdo_mysql', $ary)){
    echo "PDO MySql is installed. OK<br>";
}

if (get_magic_quotes_gpc()){
    echo "magic_quotes_gpc is on. Error";
    die();

} else {
    echo "magic_quotes_gpc is off. OK<br>";
}

// try if we can connect to db given in config.ini
try {
    $db = new installDb();
} catch (PDOException $e) {
    echo "Could not connect to db with the data given in config/config.ini. Error";
    die();
}

// check to see if an install have been made.
// home menu item in table 'menus' will be set if an install has been made.
// we check if there are rows in 'menus'


try {
    $num_rows = $db->getNumRows('menus');
} catch (PDOException $e) {   
    $num_rows = 0;
}

if ($num_rows == 0){
    echo "No tables or data in database. OK<br>";
    // read default sql and execute it.
    $sql = $db->readSql();
    $res = $db->rawQuery($sql);

    // if positive we install base modules.
    if ($res){
        echo install_module('error');
        echo install_module('settings');
        echo install_module('account');
        echo install_module('path_manip');
        echo install_module('content');
    }
} else {
    print "System is already installed! Error<br>";
}
