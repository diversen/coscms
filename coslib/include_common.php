<?php


/**
 * initialize base system if you just want to use classes
 * Runs both web system and commandline system.
 * 
 * 
 * @package  head
 */

/**
// example 
// path to ../coslib
$path = dirname('../../../');
$path = realpath($path);
define('_COS_PATH', $path);
// include this file
include_once "../../coslib/include_common.php";
*/
/**
 * set include path
 * @ignore
 */
$ini_path = ini_get('include_path');
ini_set('include_path', 
    _COS_PATH . PATH_SEPARATOR . 
    _COS_PATH . '/vendor' . PATH_SEPARATOR .
    _COS_PATH . "/coslib" . PATH_SEPARATOR . _COS_PATH . '/modules' . 
        $ini_path . PATH_SEPARATOR);




/**
 * include base classes and functions
 * the names specifify what the classes or function collections do. 
 * @ignore
 */

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
include_once "coslib/model.php";
