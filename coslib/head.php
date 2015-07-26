<?php

use diversen\lang;
use diversen\moduleloader;
use diversen\intl;
use diversen\http;
use diversen\db;
use diversen\uri\dispatch;
use diversen\alias;
use diversen\conf;

alias::set();

/**
 * initialize base system. 
 * Runs both web system and commandline system.
 * @package  head
 */

/**
 * include base classes and functions
 * the names specifify what the classes or function collections do. 
 * @ignore
 */


// define all constant - based on _COS_PATH and config.ini
conf::defineCommon();
conf::load();

// set include path
$ini_path = ini_get('include_path');
ini_set('include_path', 
    _COS_PATH . PATH_SEPARATOR . 
    _COS_MOD_PATH . PATH_SEPARATOR . 
        $ini_path . PATH_SEPARATOR);


log::debug('test');
log::setLogLevel();
// Important!
// 
// No runLevels are run in Cli mode
// So if you have e.g. the configdb module installed
// this will not affect settings in any way
// If you run commands, e.g. cron jobs, that depends on 'e.g.' language
// you will need to set language in config/config.ini

// This is only if commandline mode is not specified  
if (!conf::isCli()){
    
    ini_set('default_charset', 'UTF-8');
    
    // load config/config.ini
    
    
    // check if there exists a shared ini file
    // shared ini is used if we want to enable settings between hosts
    // which share same code base. 
    // e.g. when updating all sites, it is a good idea to set the following flag
    // site_update = 1
    // this flag will send correct 503 headers, when we are updating our site. 


    // if site is being updaing we send temporarily headers
    // and display an error message
    if (conf::getMainIni('site_update')) {
        http::temporarilyUnavailable();
    }

    
    // set a unified server_name if not set in config file. 
    $server_name = conf::getMainIni('server_name');
    if (!$server_name){
        conf::setMainIni('server_name', $_SERVER['SERVER_NAME']);
    }

    // redirect to uniform server name is set in config.ini
    // e.g. www.testsite.com => testsite.com
    $server_redirect = conf::getMainIni('server_redirect');
    if (isset($server_redirect)) {
        http::redirectHeaders($server_redirect);
    }
    
    // redirect to https is set in config.in
    // force anything into ssl mode
    $server_force_ssl = conf::getMainIni('server_force_ssl');
    if (isset($server_force_ssl)) {
        http::sslHeaders();
    }

           
    // catch all output
    ob_start();
   
    // init module loader. 
    // after this point we can check if module exists and fire events connected to
    // installed modules
    $db = new db();
    $ml = new moduleloader();
    
    
    // runlevel 1: merge db config
    $ml->runLevel(1);

    // select all db settings and merge them with ini file settings
    $db_settings = $db->selectOne('settings', 'id', 1);

    // merge db settings with config/config.ini settings
    // db settings override ini file settings
    conf::$vars['coscms_main'] =
        array_merge(conf::$vars['coscms_main'] , $db_settings);
      
    // run level 2: set locales 
    $ml->runLevel(2);
    
    // set locales
    intl::setLocale ();
    
    // set default timezone
    intl::setTimezone();
    
    // runlevel 3 - init session
    $ml->runLevel(3);

    // start session
    session::initSession();
    session::checkAccount();
    
    
    
    // set account timezone if enabled - can only be done after session
    // as user needs to be logged in
    intl::setAccountTimezone();

    // run level 4 - load language
    $ml->runLevel(4);
    
    // load a 'language_all' file or load all module system language
    // depending on configuration
    lang::loadLanguage();

    $ml->runLevel(5);
    
    // load url routes if any
    dispatch::setDbRoutes();
    
    $ml->runLevel(6);

    $controller = null;
    $route = dispatch::getMatchRoutes();

    if ($route) {
        // if any route is found we get controller from match
        // else we load module in default way
        $controller = $route['controller'];
    } 
    
    // set module info
    $ml->setModuleInfo($controller);
    
    // load module
    $ml->initModule();

    // include template class found in _COS_HTDOCS . '/templates'
    // only from here we should use template class. 
    // template translation will override module translations
    $layout = new layout();
    // we first load menus here so we can se what happened when we
    // init our module. In case of a 404 not found error we don't want
    // to load module menus
    $layout->loadMenus();
    
    // init blocks
    $layout->initBlocks();

    
    // if any matching route was found we check for a method or function
    if (isset($route['method'])) {
        $str = dispatch::call($route['method']);       
    } else {
        // or we use default ('old') module loading
        $str = $ml->getParsedModule();
    }
    
    mainTemplate::printHeader();
    echo '<div id="content_module">'.$str.'</div>';

    $ml->runLevel(7);
    mainTemplate::printFooter();   
    conf::$vars['final_output'] = ob_get_contents();
    ob_end_clean();

    // Last divine intervention
    // e.g. Dom or Tidy
    $ml->runLevel(8); 
    echo conf::$vars['final_output'];
}
