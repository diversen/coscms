<?php

set_time_limit(0);
ignore_user_abort(true);

$config = $path = null;

// test if we place coslib outside web directory
if (file_exists('../coslib/config.php')) {
    $config = "../coslib/config.php";
    $path = realpath('..');
} else {
    $config = "./coslib/config.php";
    $path = realpath('.');
}
include_once $config;
config::$vars['coscms_main'] = array();


if (DIRECTORY_SEPARATOR != '/') {	
	$path = str_replace ('\\', '/', $path); 
}

define('_COS_PATH',  $path);
define('_COS_HTDOCS',  $path);

// global array used as registry for holding debug info
$_COS_DEBUG = array();

// start timer
$_COS_DEBUG['timer']['start'] = microtime();

// set base path in debug info
$_COS_DEBUG['cos_base_path'] = _COS_PATH;

//echo _COS_PATH;

// set include path
$ini_path = ini_get('include_path');
//$ini_path = ini_get('include_path');
ini_set('include_path', 
    _COS_PATH . PATH_SEPARATOR . 
    _COS_PATH . '/vendor' . PATH_SEPARATOR .
    _COS_PATH . "/coslib" . PATH_SEPARATOR . _COS_PATH . '/modules' . 
        $ini_path . PATH_SEPARATOR);

// parse main config.ini file
$_COS_DEBUG['include_path'] = ini_get('include_path');

//include_once "common.php";

// register::$vars['coscms_main'] is used as a register for holding global settings.
config::$vars['coscms_main'] = config::getIniFileArray(_COS_PATH . '/config/config.ini', true);

include_once "coslib/config.php";
include_once "coslib/file.php";
include_once "coslib/strings.php";
include_once "coslib/db.php";
include_once "coslib/uri.php";
include_once "coslib/moduleloader.php";
include_once "coslib/session.php";
include_once "coslib/html.php";
include_once "coslib/layout.php";
include_once "coslib/template.php";
include_once "coslib/event.php";
include_once "coslib/mail.php";
include_once "coslib/validate.php";
include_once "coslib/http.php";
include_once "coslib/user.php";
include_once "coslib/log.php";
include_once "coslib/lang.php";
include_once "coslib/time.php";
include_once "coslib/urldispatch.php";
include_once "coslib/moduleInstaller.php";
include_once "coslib/shell_base/profile.inc";


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


$version = "5.3.0";
if (version_compare( $version, phpversion(), ">=")) {
    echo "We need PHP version $version or higher, my version: " . PHP_VERSION . " ERROR<br>";
} else {
    echo "Version " . phpversion() . " OK. We need $version<br />"; 
}

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
        install_from_profile(array ('profile' => 'default'));
    }
    echo "Base system installed.<br />";
    echo "Remember to set files/ directory to be writeable <br>";

    
    
} else {
    echo "System is installed! <br>";
    echo "Remember to set files/ directory to be writeable <br>";
    echo "If something is wrong. Try to drop database and retry install<br />";
    
}

    $users = $db->getNumRows('account');
    if ($users == 0) {
        web_install_add_user();
    } else {
        echo "User exists. Install OK<br />\n";
    }


function web_install_add_user () {

        $layout = new layout('zimpleza');

        $errors = array ();
        
        if (isset($_POST['submit'])) {
            $_POST = html::specialEncode($_POST);
            if (empty($_POST['pass1'])) {
                $errors[] = 'Please enter a password';
            }
            
            if ($_POST['pass1'] != $_POST['pass2']) {
                $errors[] = 'Not same passwords';
            }
            if (empty ($_POST['email'])) {
                $errors[] = 'Please enter an email';
            }
            
            if (!empty($errors)){
                view_form_errors($errors);
            } else {
                $db = new db();
                
                $_POST = html::specialDecode($_POST);
                $values = array ();
                
                $values['email'] = $_POST['email'];
                $values['password'] = md5($_POST['pass1']); // MD5
                $values['username'] = $_POST['email'];
                $values['verified'] = 1;
                $values['admin'] = 1;
                $values['super'] = 1;
                $values['type'] = 'email';

                $db->insert('account', $values);
                http::locationHeader("/account/login/index", 
                        'Account created. You may log in');
                //web_install_add_user();
            }
        }

        web_install_user_form ();
}

function web_install_user_form () {
    $form = new html();
    $form->formStart();
    $form->init(null, 'submit');
    $form->legend('Add User');
    $form->label('email', 'Enter email of user:');
    $form->text('email');
    $form->label('pass1', 'Enter password');
    $form->password('pass1');
    $form->label('pass2', 'Retype password');
    $form->password('pass2');
    $form->submit('submit', 'Submit');
    $form->formEnd();
    echo $form->getStr();
}
