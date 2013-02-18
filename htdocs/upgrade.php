<?php

set_time_limit(0);
ignore_user_abort(true);
$config = $path = null;

// test if we have placed coslib outside web directory
if (file_exists('./coslib/coslibSetup.php')) {
    $config = "./coslib/coslibSetup.php";
    $path = realpath('.');
    
} else {
    $config = "../coslib/coslibSetup.php";
    $path = realpath('..');
}



// windows
if (DIRECTORY_SEPARATOR != '/') {	
    $path = str_replace ('\\', '/', $path); 
}

define('_COS_PATH',  $path);
define('_COS_HTDOCS',  ".");

// include config.php for reading config files etc.
include_once $config;
config::$vars['coscms_main'] = array();

// global array used as registry for holding debug info
$_COS_DEBUG = array();

// start timer
$_COS_DEBUG['timer']['start'] = microtime();

// set base path in debug info
$_COS_DEBUG['cos_base_path'] = _COS_PATH;

// set include path

// parse main config.ini file
$_COS_DEBUG['include_path'] = ini_get('include_path');
config::$vars['coscms_main'] = config::getIniFileArray(_COS_PATH . '/config/config.ini', true);

include_once "coslib/shell/common.inc";
include_once "coslib/shell/profile.inc";

/**
 * class installDb
 * Only method change is connect.
 * We don't try and catch in the class
 */
class installDb extends db {
    public $sql;
    
    function __construct($options = array ()){
        // make sure we have a connection
        $this->connect($options);
    }

    function readSql(){
        $file = _COS_PATH . '/scripts/default.sql';
        $this->sql = file_get_contents($file);
        return $this->sql;
    }
}

function cos_check_Version () {
    $version = "5.3.0";
    if (version_compare( $version, phpversion(), ">=")) {
        echo "We need PHP version $version or higher, my version: " . PHP_VERSION . " ERROR<br>";
    } else {
        echo "Version " . phpversion() . " OK. We need $version<br />"; 
    }
}



function cos_check_pdo_mysql () {
    $ary = get_loaded_extensions();
    if (in_array('PDO', $ary)){
        echo "PDO is installed. OK<br>";
    } else {
        die("No PDO<br />");
    }
    
    if (in_array('pdo_mysql', $ary)){
        echo "PDO MySql is installed. OK<br>";
    } else {
        die("No PDO MySQL<br />");
    }
}

function cos_check_magic_gpc () {
    if (get_magic_quotes_gpc()){
        echo "magic_quotes_gpc is on. Error";
        die();
    } else {
        echo "magic_quotes_gpc is off. OK<br>";
    }
}

function cos_check_files_dir () {
    clearstatcache();
    $files_dir = _COS_HTDOCS . "/files";
    $domain = config::getMainIni('domain');
    $files_dir.="/$domain";
    
    if (!is_writable($files_dir)) {
        echo "dir: $files_dir is not writeable<br />\n";
        $user = getenv('APACHE_RUN_USER');
        echo "server user is: $user<br />\n";
        echo "On unix solution will be to:<br />\n";
        echo "sudo chown -R www-data:www-data ./files";
        die();
    } else {
        echo "We can write to files dir.OK<br>";
    }
}

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
        install_from_profile(array ('profile' => 'default'));

        // reload language
        $reload = new moduleinstaller();
        $reload->reloadCosLanguages();
        $reload->reloadLanguages();
        
    }
}
