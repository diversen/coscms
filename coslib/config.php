<?php

/**
 * contains methods for getting config settings. 
 * @package coslib
 */

/**
 * one linier register class. 
 * @package coslib
 */
class register {
    public static $vars = array();
}

/**
 * method for getting a module ini settings
 * @param string $key the key of the ini settng to get 
 * @return mixed $value the value of the setting or null if no value was found
 */
function get_module_ini($key){
    if (!isset(register::$vars['coscms_main']['module'][$key])){
        return null;
    }
    if (register::$vars['coscms_main']['module'][$key] == '0'){
        return null;
    }
    return register::$vars['coscms_main']['module'][$key];
}

/**
 * method for getting a main ini setting found in config/config.ini
 * @param   string  $key the ini setting key to get
 * @return  mixed   $val the value of the setting or null if not found. 
 *                       If 0 is found we also reutnr null
 */
function get_main_ini($key){
    if (!isset(register::$vars['coscms_main'][$key])){
        return null;
    }
    if (register::$vars['coscms_main'][$key] == '0'){
        return null;
    }
    return register::$vars['coscms_main'][$key];
}

/**
 * method for getting a path to a module
 *
 * @param   string  $module the module
 * @return  string  $path the module path
 */
function get_module_path ($module){
    return _COS_PATH . '/modules/' . $module;
}


/**
 * parse ini with this and they will be cached with APC
 * @param string $file
 * @param boolean $sections
 * @return array $ini settings 
 */
function parse_ini_file_ext ($file, $sections = null) {
    ob_start();
    include $file;
    $str = ob_get_contents();
    ob_end_clean();
    return parse_ini_string($str, $sections);
}

/**
 * function for getting name of main configuration file 
 * config/config.ini. 
 * 
 * If in CLI mode the --domain options need to be set in order to fetch
 * the correcgt virtual host. E.g. config/multi/domain/config.ini
 * where domain is the domain flag. 
 * 
 * In normal mode the domain name is checked using $_SERVER['SERVER_NAME'].
 * If this name matches file config/multi/domain/config.ini then this
 * file will be used. 
 * 
 * If file not set it is the normal config/config.ini which will be included. 
 * 
 * @return string $filename the filname of the config file.  
 */
function get_config_file() {
    // determine host and see if we use virtual hosting
    // where one code base can be used for more virtual hosts.
    // this is set with the domain flag in ./coscli.sh
    if (defined('_COS_CLI')){
        if (isset(register::$vars['domain']) && register::$vars['domain'] != 'default'){
            $config_file = _COS_PATH . "/config/multi/". register::$vars['domain'] . "/config.ini";
        } else {
            $config_file = _COS_PATH . "/config/config.ini";
        }
    } else {
        $virtual_host_dir = _COS_PATH . "/config/multi/$_SERVER[SERVER_NAME]";
        if (file_exists($virtual_host_dir)){
            $config_file = $virtual_host_dir . "/config.ini";
        } else {
            $config_file = _COS_PATH . "/config/config.ini";
        }
    }
    return $config_file;
}
/**
 * Function for loading the config file
 * In order for this to work you need to have in your config file:
 *  
 * server_name = "coscms.org"
 * 
 * In order to set settiings for development or stage: 
 * 
 * Add to the [development] or [stage] section the server_name
 * for stage or development, e.g.:
 * 
 * [stage]
 * server_name = "coscms" 
 * 
 * This will be compared to the $_SERVER['SERVER_NAME'] variable
 * and if there is a match the stage settings will override
 * the default settings. Same goes for development 
 */
function load_config_file () {
    $config_file = get_config_file();
    
    if (!file_exists($config_file)){
        //define ("NO_CONFIG_FILE", true);
        return;
    } else {
        register::$vars['coscms_main'] = parse_ini_file_ext($config_file, true);
        if (
            (@register::$vars['coscms_main']['stage']['server_name'] ==
                @$_SERVER['SERVER_NAME'])
                AND !defined('_COS_CLI') )
            {
                // We are on REAL server and exists without
                // adding additional settings for stage or development
                // or CLI mode. 
                return; 
        }
        
        // Test if we are on stage server. 
        // Overwrite register settings with stage settings
        // Note that ini settings for development will
        // NOT take effect on CLI ini settings
        if (isset(register::$vars['coscms_main']['stage'])){
            if (
                (register::$vars['coscms_main']['stage']['server_name'] ==
                    @$_SERVER['SERVER_NAME'])
                    AND !defined('_COS_CLI') )
                {
                
                // we are on development, merge and overwrite normal settings with
                // development settings.
                register::$vars['coscms_main'] =
                array_merge(
                    register::$vars['coscms_main'],
                    register::$vars['coscms_main']['stage']
                );
                return;
            }
        }
        // We are on development server. 
        // Overwrite register settings with development settings
        // Development settings will ALSO be added to CLI
        // ini settings
        if (isset(register::$vars['coscms_main']['development'])){
            if (
                (register::$vars['coscms_main']['development']['server_name'] ==
                    @$_SERVER['SERVER_NAME'])
                    OR defined('_COS_CLI') )
                {
                
                register::$vars['coscms_main'] =
                array_merge(
                    register::$vars['coscms_main'],
                    register::$vars['coscms_main']['development']
                );
            }
        }
    }
}

/**
 * function for getting a full path to public files folder when doing e.g. uploads
 * @return string $files_path the full file path 
 */
function get_files_path () {
    $domain = get_main_ini('domain');
    if ($domain == 'default') {
        $files_path = _COS_PATH . "/htdocs/files/default";
    } else {
        $files_path = _COS_PATH . "/htdocs/files/$domain";
    }
    return $files_path;
}

/**
 * method for getting the web path to files folder. 
 * @param string $file the file to get path from
 * @return string $path the web path to the file
 */
function get_files_web_path ($file) {
    return "/files/" . get_domain() . $file; 
}

/**
 * method for getting domain. 
 * @return string $domain the current domain
 */
function get_domain () {
    $domain = get_main_ini('domain');
    return $domain;
}