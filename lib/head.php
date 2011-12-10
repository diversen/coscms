<?php


/**
 * @package    coslib
 */

/**
 * @ignore
 */
register::$vars['coscms_main'] = array();
register::$vars['coscms_main']['module'] = array();
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

// Rest is only for web mode. 
if (!defined('_COS_CLI')){
    include_once "lib/common.php";
    load_config_file ();
    
    // set a unified server_name if not set in config file. 
    if (empty(register::$vars['coscms_main']['server_name'])){
        register::$vars['coscms_main']['server_name'] = $_SERVER['SERVER_NAME'];
    }
       
    ob_start();

    // redirect to uniform domain name is set in config.ini
    $server_redirect = get_main_ini('server_redirect');
    if (isset($server_redirect)) {
        server_redirect($server_redirect);
    }
    
    // redirect to https is set in config.ini
    $server_force_ssl = get_main_ini('server_force_ssl');
    if (isset($server_force_ssl)) {
        server_force_ssl();
    }
    
    // include all common classes
    include "db.php";
    include "uri.php";
    include "moduleloader.php";
    include "session.php";
    include "html.php";
    include "layout.php";
    include "template.php";
    include "event.php";
    
    // start session
    session::initSession();
    
    $preloaded_module = get_main_ini('preloaded_modules');

    // init module loader
    $db = new db();
    $moduleLoader = new moduleLoader();
    $moduleLoader->runLevel(1);

    // select all db settings and merge them with ini file settings
    $db_settings = $db->selectOne('settings', 'id', 1);

    register::$vars['coscms_main'] =
        array_merge(register::$vars['coscms_main'] , $db_settings);
    
    $moduleLoader->runLevel(2);

    if (isset(register::$vars['coscms_main']['locale'])){
        $locale = register::$vars['coscms_main']['locale'];
    } else {
        $locale = register::$vars['coscms_main']['language'].'.UTF8';
    }

    // set locale for time and monetary
    setlocale(LC_TIME, $locale);
    setlocale(LC_MONETARY, $locale);
    
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
    // only from here we should use template class. 
    $layout = new layout();

    // we first load menus here so we can se what happened when we
    // inited module. In case of a 404 not found error we don't want
    // to load module menus
    $layout->loadMenus();
    
    // init blocks
    $layout->initBlocks();

    // load page module
    // catch the included controller file with ob functions
    // and return the parsed page as html
    $str = $moduleLoader->loadModule();
    
    // collect final out put
    ob_start();    
    mainTemplate::printHeader();
    echo $str;
    
    $moduleLoader->runLevel(6);
    mainTemplate::printFooter();   
    register::$vars['final_output'] = ob_get_contents();
    ob_end_clean();
    
    // Last divine intervention
    // tidy / e.g. Dom

    $moduleLoader->runLevel(7); 
    echo register::$vars['final_output'];
}
