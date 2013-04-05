<?php

set_time_limit(0);
ignore_user_abort(true);
$setup = $path = null;

// test if we have placed coslib outside web directory
if (file_exists('./coslib/coslibSetup.php')) {
    $setup = "./coslib/coslibSetup.php";
    $path = realpath('.');
    
} else {
    $setup = "../coslib/coslibSetup.php";
    $path = realpath('..');
}



// windows
if (DIRECTORY_SEPARATOR != '/') {	
    $path = str_replace ('\\', '/', $path); 
}

define('_COS_PATH',  $path);

include_once $setup;

config::loadMain();
config::defineCommon();

include_once "coslib/shell/common.inc";
include_once "coslib/shell/profile.inc";
include_once "coslib/webinstall/common.php";

cos_check_Version();
cos_check_pdo_mysql();
cos_check_magic_gpc();
cos_check_files_dir();

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
        install_from_profile(array ('profile' => 'default'));
        
        
    }
    echo "Base system installed.<br />";
    
} else {
    echo "System is installed! <br>";
}

$users = $db->getNumRows('account');
if ($users == 0) {
    web_install_add_user();
} else {
    echo "User exists. Install OK<br />\n";
}


