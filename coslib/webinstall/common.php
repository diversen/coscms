<?php

/**
 * common files used for web install
 * see: 
 * 
 * htdocs/install.php
 * htdocs/upgrade.php
 * 
 */

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