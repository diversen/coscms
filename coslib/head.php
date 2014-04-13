<?php

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


// set some common register vars
config::$vars['coscms_base'] = _COS_PATH;
config::$vars['coscms_main'] = array();
config::$vars['coscms_main']['module'] = array();
config::$vars['coscms_debug'] = array();
config::$vars['coscms_lang'] = array();
config::$vars['coscms_debug']['timer']['start'] = microtime(true);
config::$vars['coscms_debug']['coscms_base']  = config::$vars['coscms_base'];
config::$vars['coscms_debug']['include_path'] = ini_get('include_path');

// Note: If Cli mode there is no runLevels
// Therefore: config from database which are merged with config settings
// from file is NOT loaded in Cli mode: You will need to set these
// settings in config.ini

if (!config::isCli()) {
    config::loadMain();
} else {
    config::loadMainCli();
}

// define all constant - based on _COS_PATH and config.ini
config::defineCommon();

// set include path
$ini_path = ini_get('include_path');
ini_set('include_path', 
    _COS_PATH . PATH_SEPARATOR . 
    _COS_MOD_PATH . PATH_SEPARATOR . 
        $ini_path . PATH_SEPARATOR);



// Important!
// 
// No runLevels are run in Cli mode
// So if you have e.g. the configdb module installed
// this will not affect settings in any way
// If you run commands, e.g. cron jobs, that depends on 'e.g.' language
// you will need to set language in config/config.ini

// This is only if commandline mode is not specified  
if (!config::isCli()){
    // load config/config.ini
    
    
    // check if there exists a shared ini file
    // shared ini is used if we want to enable settings between hosts
    // which share same code base. 
    // e.g. when updating all sites, it is a good idea to set the following flag
    // site_update = 1
    // this flag will send correct 503 headers, when we are updating our site. 
        
    // check if we are in debug mode and display errors
    if (config::getMainIni('debug')) {
        ini_set('display_errors', 1);
    }

    // if site is being updaing we send temporarily headers
    // and display an error message
    if (config::getMainIni('site_update')) {
        http::temporarilyUnavailable();
    }

    
    // set a unified server_name if not set in config file. 
    $server_name = config::getMainIni('server_name');
    if (!$server_name){
        config::setMainIni('server_name', $_SERVER['SERVER_NAME']);
    }

    // redirect to uniform server name is set in config.ini
    // e.g. www.testsite.com => testsite.com
    $server_redirect = config::getMainIni('server_redirect');
    if (isset($server_redirect)) {
        http::redirectHeaders($server_redirect);
    }
    
    // redirect to https is set in config.in
    // force anything into ssl mode
    $server_force_ssl = config::getMainIni('server_force_ssl');
    if (isset($server_force_ssl)) {
        http::sslHeaders();
    }

           
    // catch all output
    ob_start();
   
    // init module loader. 
    // after this point we can check if module exists and fire events connected to
    // installed modules
    $db = new db();
    $moduleloader = new moduleloader();
    
    // runlevel 1: merge db config
    $moduleloader->runLevel(1);

    // select all db settings and merge them with ini file settings
    $db_settings = $db->selectOne('settings', 'id', 1);

    // merge db settings with config/config.ini settings
    // db settings override ini file settings
    config::$vars['coscms_main'] =
        array_merge(config::$vars['coscms_main'] , $db_settings);
      
    // run level 2: set locales 
    $moduleloader->runLevel(2);
    
    // set locales
    intl::setLocale ();
    
    // set default timezone
    intl::setTimezone();
    
    // runlevel 3 - init session
    $moduleloader->runLevel(3);

    // start session
    session::initSession();
    
    // set account timezone if enabled - can only be done after session
    // as user needs to be logged in
    intl::setAccountTimezone();

    // run level 4 - load language
    $moduleloader->runLevel(4);
    
    // load a 'language_all' file or load all module system language
    // depending on configuration
    lang::loadLanguage();


    $moduleloader->runLevel(5);
    
    // load url routes if any
    uri_dispatch::setDbRoutes();
    
    $moduleloader->runLevel(6);

    $controller = null;
    $route = uri_dispatch::getMatchRoutes();
    if ($route) {
        // if any route is found we get controller from match
        // else we load module in default way
        $controller = $route['controller'];
    } 
    
    // set module info
    $moduleloader->setModuleInfo($controller);
    
    // load module
    $moduleloader->initModule();

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
        $str = uri_dispatch::call($route['method']);       
    } else {
        // or we use default ('old') module loading
        $str = $moduleloader->getParsedModule();
    }
    
    mainTemplate::printHeader();
    echo '<div id="content_module">'.$str.'</div>';

    $moduleloader->runLevel(7);
    mainTemplate::printFooter();   
    config::$vars['final_output'] = ob_get_contents();
    ob_end_clean();

    // Last divine intervention
    // e.g. Dom or Tidy
    $moduleloader->runLevel(8); 
    echo config::$vars['final_output'];
}
