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


$password = config::getMainIni('upgrade_password');
if (!$password || !isset($_GET['password'])) {
    //$server = config::getSchemeWithServerName();
    $url = "http://" . $_SERVER['SERVER_NAME'] . "/upgrade.php?password=password";
    die("Set ini_setting 'upgrade_password'='password' password in config/config.ini, and visit $url");
} else {
    if ($password == $_GET['password']) {
        upgrade_from_profile_web(array ('profile' => 'default'));

        // reload language
        $reload = new moduleinstaller();
        $reload->reloadCosLanguages();
        $reload->reloadLanguages();
        
    }
}
