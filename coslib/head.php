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

if (!config::isCli()) {
    config::loadMain();
} else {
    config::loadMainCli();
}

$module_dir = config::getMainIni('module_dir');
if (!$module_dir) $module_dir ='modules';

$ini_path = ini_get('include_path');
ini_set('include_path', 
    _COS_PATH . PATH_SEPARATOR . 
    _COS_PATH . "/$module_dir" . PATH_SEPARATOR . 
        $ini_path . PATH_SEPARATOR);

// deinfe all constant - based on _COS_PATH and config.ini
config::defineCommon();


// This is only if commandline mode is not specified  
if (!config::isCli()){
    // load config/config.ini
    
    
    // check if there exists a shared ini file
    // shared ini is used if we want to enable settings between hosts
    // which share same code base. 
    // e.g. when updating all sites, it is a good idea to set the following flag
    // site_update = 1
    // this flag will send correct 503 headers, when we are updating our site. 
    
    $shared_ini = _COS_PATH . '/config/shared.ini';
    if (file_exists($shared_ini)) {
        $ini = config::getIniFileArray($shared_ini, true);
        config::$vars['coscms_main'] =
            array_merge(
                config::$vars['coscms_main'],
                $ini
            );
        
    }
        
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
    $moduleloader->runLevel(1);

    // select all db settings and merge them with ini file settings
    $db_settings = $db->selectOne('settings', 'id', 1);

    // merge db settings with config/config.ini settings
    config::$vars['coscms_main'] =
        array_merge(config::$vars['coscms_main'] , $db_settings);
      
    // run level 2: Just after configuration from file have been set
    // in order to change e.g. file settings you can change the now.
    // See module configdb for example. 
    $moduleloader->runLevel(2);
    
    // init language from DB if we did not load all language as a single file
    $lang_all = config::getMainIni('language_all');
    if ($lang_all) {
        lang::loadTemplateAllLanguage();
    } else {
        lang::init();
    } 
    
    // find out what locales we are using
    if (isset(config::$vars['coscms_main']['locale'])){
        $locale = config::$vars['coscms_main']['locale'];
    } else {
        $locale = config::$vars['coscms_main']['language'].'.UTF8';
    }

    // set locale for time and monetary
    // if the array locale is not sepcified we set time and money
    // according to locales
    setlocale(LC_TIME, $locale);
    setlocale(LC_MONETARY, $locale);

    $moduleloader->runLevel(4);
    
    
    // set default timezone
    date_default_timezone_set(config::$vars['coscms_main']['date_default_timezone']);

    // start session
    session::initSession();

    $moduleloader->runLevel(5);
    
    
    // load url routes if any
    urldispatch::setDbRoutes();

    $controller = null;
    $route = urldispatch::getMatchRoutes();
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
    // inited module. In case of a 404 not found error we don't want
    // to load module menus
    $layout->loadMenus();
    
    // init blocks
    $layout->initBlocks();

    // if any matching route was found we check for a method or funciton
    if (isset($route['method'])) {
        $str = urldispatch::call($route['method']);       
    } else {
        // or we use default ('old') module loading
        $str = $moduleloader->getParsedModule();
    }
    
    mainTemplate::printHeader();
    echo $str;

    $moduleloader->runLevel(6);
    mainTemplate::printFooter();   
    config::$vars['final_output'] = ob_get_contents();
    ob_end_clean();

    // Last divine intervention
    // e.g. Dom or Tidy
    $moduleloader->runLevel(7); 
    echo config::$vars['final_output'];
}
