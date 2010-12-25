<?php

/**
 * @package    coslib
 */
register::$vars['coscms_main'] = array();
register::$vars['coscms_debug'] = array();
register::$vars['coscms_lang'] = array();
register::$vars['coscms_debug']['timer']['start'] = microtime(true);
register::$vars['coscms_debug']['coscms_base']  = register::$vars['coscms_base'];

// set include path
$ini_path = ini_get('include_path');
ini_set('include_path', $ini_path . PATH_SEPARATOR .
    _COS_PATH . PATH_SEPARATOR . "." . PATH_SEPARATOR .
    _COS_PATH . '/vendor' . PATH_SEPARATOR .
    _COS_PATH . "/lib" . PATH_SEPARATOR . _COS_PATH . '/modules');

// parse main config.ini file
register::$vars['coscms_debug']['include_path'] = ini_get('include_path');

// load ini settings from file
$config_file = register::$vars['coscms_base'] . '/config/config.ini';
if (!file_exists($config_file)){
    define ("NO_CONFIG_FILE", true);
} else {
    register::$vars['coscms_main'] = parse_ini_file($config_file, true);
    if (isset(register::$vars['coscms_main']['development'])){
        if (
            (register::$vars['coscms_main']['development']['server_name'] ==
                @$_SERVER['SERVER_NAME'])
                OR defined('_COS_CLI') )
            {
            // we are on development, merge and overwrite normal settings with
            // development settings.
            register::$vars['coscms_main'] =
            array_merge(
                register::$vars['coscms_main'],
                register::$vars['coscms_main']['development']
            );
        }
    }
}


// set a unified server_name
if (empty(register::$vars['coscms_main']['server_name'])){
    register::$vars['coscms_main']['server_name'] = @$_SERVER['SERVER_NAME'];
}


if (!defined('_COS_CLI')){
    ob_start();
    
    // include common functions
    include "common.php";
    include "db.php";
    include "uri.php";
    include "moduleloader.php";
    include "session.php";
    include "layout.php";
    include "template.php";

    session::initSession();

    // init module loader
    $db = new db();
    $moduleLoader = new moduleLoader();
    $moduleLoader->runLevel(1);

    // select all db settings and merge them with ini file settings
    $db_settings = $db->selectOne('settings', 'id', 1);




    register::$vars['coscms_main'] =
        array_merge(register::$vars['coscms_main'] , $db_settings);
    $moduleLoader->runLevel(2);

    $locale = register::$vars['coscms_main']['language'].'.UTF8';
    setlocale(LC_ALL, $locale);
    // set default timezone
    date_default_timezone_set(register::$vars['coscms_main']['date_default_timezone']);
    $moduleLoader->runLevel(4);

    // include translation class and language specified in config.ini
    include "lang.php";

    // load languages.
    lang::init();    
    $moduleLoader->runLevel(5);

    // set files to load and init module.
    $moduleLoader->setModuleFiles();
    $moduleLoader->initModule();

    // include template class found in htdocs/templates
    $layout = new layout();

    // we first load menus here so we can se what happened when we
    // inited module. In case of a 404 not found error we don't want
    // to load module menus
    $layout->loadMenus();

    // load module
    // means: catch the included controller file with ob functions
    // and return the parsed page as html
    $str = $moduleLoader->loadModule();

    mainTemplate::printHeader();
    print $str;
    

    $moduleLoader->runLevel(6);
    mainTemplate::printFooter();   

    //last runlevel. After anything has been written to screen.
    $moduleLoader->runLevel(7);                
}
