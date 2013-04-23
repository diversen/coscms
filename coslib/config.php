<?php

/**
 * contains methods for getting config settings. 
 * @package config
 */

/**
 * config class. 
 * 
 * read and load configuration files from config/config.ini and 
 * modules/e.g.  modules/som_module/some_module.ini  
 * @package config
 */

class config {
    
    /**
     * var holding all config values
     * @var array $vars
     */
    public static $vars = array ();
    
    
    /**
     * method for getting a module ini settings
     * @param string $key the key of the ini settng to get 
     * @return mixed $value the value of the setting or null if no value was found
     */
    public static function getModuleIni($key) {
        if (!isset(config::$vars['coscms_main']['module'][$key])){
            return null;
        }
        if (config::$vars['coscms_main']['module'][$key] == '0'){
            return null;
        }
        
        if (empty(config::$vars['coscms_main']['module'][$key])){
            return null;
        }
        return config::$vars['coscms_main']['module'][$key];
    }
    
    /**
     * method for getting a main ini setting found in config/config.ini
     * or in database override
     * @param   string  $key the ini setting key to get
     * @return  mixed   $val the value of the setting or null if not found. 
     *                       If 0 is found we also reutnr null
     */    
    public static function getMainIni($key) {
          return self::getMainIniFromHolder($key);
    }
    
    /**
     * method for getting a main ini setting found in config/config.ini
     * @param   string  $key the ini setting key to get
     * @return  mixed   $val the value of the setting or null if not found. 
     *                       If 0 is found we also reutnr null
     */    
    public static function getMainIniFromFile ($key) {
        //print_r(config::$vars['coscms_main_file']);
        return self::getMainIniFromHolder($key, 'coscms_main_file');
    } 
    
    /**
     * return main ini from placeholder
     * @param string $key
     * @param string $holder
     * @return mixed $val
     */
    private static function getMainIniFromHolder ($key, $holder = 'coscms_main') {
        if (!isset(config::$vars[$holder][$key])){
            return null;
        }
        
        if (config::$vars[$holder][$key] == '0'){
            return null;
        }
        
        
        if (config::$vars[$holder][$key] == 'true') {
            return true;
        }
        
        if (config::$vars[$holder][$key] == 'false') {
            return false;
        }
        return config::$vars[$holder][$key];    
    } 
    

    
    /**
     * method for getting a main ini setting found in config/config.ini
     * @param   string  $key the ini setting key to get
     * @return  mixed   $val the value of the setting or null if not found. 
     *                       If 0 is found we also reutnr null
     */    
    public static function getMainIniAsString($key) {
        if (!isset(config::$vars['coscms_main'][$key])){
            return null;
        }
        
        if (config::$vars['coscms_main'][$key] == '0'){
            return null;
        }
        
        
        if (config::$vars['coscms_main'][$key] == 'true') {
            return "true";
        }
        
        if (config::$vars['coscms_main'][$key] == 'false') {
            return "false";
        }
        return config::$vars['coscms_main'][$key];      
    }
    
    /**
     * sets a main ini setting, e.g. override from database
     * @param string $key the key to set
     * @param string $value the value to set the key with
     */
    public static function setMainIni ($key, $value) {
        config::$vars['coscms_main'][$key] = $value;
    }
    
    /**
     * sets a main ini setting, e.g. override from database
     * @param string $key the key to set
     * @param string $value the value to set the key with
     */
    public static function setMainIniWithArray ($ary) {
        foreach ($ary as $key => $val) {
            config::$vars['coscms_main'][$key] = $val;
        }
    }
    
    /**
     * 
     */
    public static function getLanguageAry ($language = null) {
        if (!$language) $language = config::getMainIni ('language');
        $ary = explode('_', $language);
        return $ary;
    }
    
    /**
     * parse ini with this and they will be cached with APC
     * @param string $file
     * @param boolean $sections
     * @return array $ini settings 
     */
    public static function getIniFileArray ($file, $sections = null) {
        ob_start();
        include $file;
	$str = ob_get_contents();
        ob_end_clean();
        return parse_ini_string($str, $sections);
    }
    
    /**
     * get http or https depending on configuration.
     * @return string $str https|http
     */
    public static function getHttpScheme () {
        $server_force_ssl = config::getMainIni('server_force_ssl');
        if ($server_force_ssl) {
            return "https";
        } else {
            return "http";
        }
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
    
    public static function getConfigFileName () {
        //return get_config_file();
        // determine host and see if we use virtual hosting
        // where one code base can be used for more virtual hosts.
        // this is set with the domain flag in ./coscli.sh
        if (config::isCli()){
            if (isset(config::$vars['domain']) && config::$vars['domain'] != 'default'){
                $config_file = _COS_PATH . "/config/multi/". config::$vars['domain'] . "/config.ini";
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
     * get environemnt - production, stage, or development
     * @return string|null production, stage, or development - or null if not set
     */
    public static function getEnv () {
        
        if (!config::isCli()) {

            if ( config::$vars['coscms_main']['server_name'] ==
                    $_SERVER['SERVER_NAME'] ) {
                return 'production';
            }


            if (isset(config::$vars['coscms_main']['stage'])){
                    if ( config::$vars['coscms_main']['stage']['server_name'] ==
                            $_SERVER['SERVER_NAME']) {
                        return 'stage';
                    }
            }

            if (isset(config::$vars['coscms_main']['development'])){
                    if ( config::$vars['coscms_main']['development']['server_name'] ==
                            $_SERVER['SERVER_NAME']) {
                        return 'development';
                    }
            }

            return null;
        } else {
            
            if (isset(config::$vars['coscms_main']['development'])){
                if (in_array(config::getHostnameFromCli(), config::getHostnameFromIni('development') ) ) {
                    return 'development';
                }
            }
            
            if (isset(config::$vars['coscms_main']['stage'])){
                if (in_array(config::getHostnameFromCli(), config::getHostnameFromIni('stage')) ) {
                    return 'stage';
                }
            }
        }

    }
    
    
    
    /**
     * Function for loading the main config file
     * found in config/config.ini
     * 
     * You can place global configuration in this file. 
     * 
     * In order to set settiings for development or stage server 
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
    public static function loadMain () {
        if (config::isCli()) {
            return;
        }
        
        $config_file = config::getConfigFileName();
    
        if (!file_exists($config_file)){
            return;
        } else {
            config::$vars['coscms_main'] = config::getIniFileArray($config_file, true);
            // set them in coscms_main_file, so it is possbile
            // to get original value without db override. 
            config::$vars['coscms_main_file'] = config::$vars['coscms_main'];
            if ( config::getEnv() == 'production' ) {

                    self::$vars['coscms_main']['development'] = 'real';
                    // We are on REAL server and exits without
                    // adding additional settings for stage or development
                    // or CLI mode. 
                    return; 
            }

            // Test if we are on stage server. 
            // Overwrite register settings with stage settings
            if ( config::getEnv() == 'stage') {

                    // we are on development, merge and overwrite normal settings with
                    // development settings.
                    config::$vars['coscms_main'] =
                    array_merge(
                        config::$vars['coscms_main'],
                        config::$vars['coscms_main']['stage']
                    );
                    self::$vars['coscms_main']['development'] = 'stage';
                    return;

            }
            // We are on development server. 
            // Overwrite register settings with development settings
            if (config::getEnv() == 'development') {

                    config::$vars['coscms_main'] =
                    array_merge(
                        config::$vars['coscms_main'],
                        config::$vars['coscms_main']['development']
                    );
                    self::$vars['coscms_main']['development'] = 'development';

            }
        }
    }
    
        
    /**
     * load main cli configuration
     */
    public static function loadMainCli () {
        $config_file = config::getConfigFileName();
        
    
        if (!file_exists($config_file)){
            return;
        } else {
            config::$vars['coscms_main'] = config::getIniFileArray($config_file, true);
            
            // as 'production' often is on the same server as 'stage', we 
            // need to set: 
            // 
            // production= 1
            // 
            // in locale.ini in order to know if we are on production or on
            // stage. 
            // 
            self::mergeSharedIni();
            if (config::getMainIni('production') == 1){
                    // We are on REAL server and exists without
                    // If this is set. 
                    // adding additional settings for stage or development
                    // or CLI mode
                    return; 
            }

            // Test if we are on stage server. 
            // Overwrite register settings with stage settings
            // Note that ini settings for development will
            // NOT take effect on CLI ini settings
            if (config::getEnv() == 'stage'){

                    // we are on development, merge and overwrite normal settings with
                    // development settings and return
                    config::$vars['coscms_main'] =
                    array_merge(
                        config::$vars['coscms_main'],
                        config::$vars['coscms_main']['stage']
                    );
                    return;
                //}
            }
            // We are on development server. 
            // Overwrite register settings with development settings
            // Development settings will ALSO be added to CLI
            // ini settings
            if (config::getEnv() =='development') {

                    config::$vars['coscms_main'] =
                    array_merge(
                        config::$vars['coscms_main'],
                        config::$vars['coscms_main']['development']
                    );
                //}
            }
        }
    }
    
    /**
     * defines all common constants after loading main ini file. 
     * 
     * _COS_HTDOCS
     * _COS_MOD_DIR
     * _COS_MOD_PATH
     * 
     *  Except: 
     * 
     *  _COS_PATH
     */
    
    public static function defineCommon () {
        
        $htdocs_path = config::getMainIni('htdocs_path');

        // default htdocs path
        if (!$htdocs_path) {
            define('_COS_HTDOCS', _COS_PATH . '/htdocs');
        }

        // if coslib path is the same as the cos htdocs path
        if ($htdocs_path == '_COS_PATH') {
            define('_COS_HTDOCS', _COS_PATH);
        }

        /**
         * define path to modules
         */

        $mod_dir = config::getMainIni('module_dir');

        if (!$mod_dir) {
            $mod_dir = 'modules';
        }

        define ('_COS_MOD_DIR', $mod_dir);
        define ('_COS_MOD_PATH', _COS_PATH . '/' . _COS_MOD_DIR);
    }
    
    /**
     * 
     * @param type $section if normal default section. Else stage or development
     * @return array $hostnames array with hostnames
     *               hostnames are set in config/config.ini in ini setting 'hostname'
     *               you can add multiple hosts e.g. for development by seperating
     *               them with a :
     */
    public static function getHostnameFromIni ($section = null) {
        if (!$section) {
            $hostnames = @config::$vars['coscms_main']['hostname'];
        }
        
        if ($section == 'stage') {
            $hostnames = @config::$vars['coscms_main']['stage']['hostname'];
        }
        
        if ($section == 'development') {
            $hostnames = @config::$vars['coscms_main']['development']['hostname'];
        }
        
        if (!$hostnames) return array ();        
        $ary = explode(':', $hostnames);
        return $ary;
        
    }
    
    /**
     * merge shared.ini with main ini file settings
     * This method was made in order to allow stage and production
     * on the same server. 
     * 
     * You simply specify: 
     * 
     * production = 1
     * 
     * Before you did rely on hostname, which often is the same on 
     * production and stage. 
     */
    public static function mergeSharedIni () {
        $shared_ini = _COS_PATH . "/config/shared.ini";
        if (file_exists($shared_ini)) {
            $shared = config::getIniFileArray($shared_ini);
            config::$vars['coscms_main'] =
                    array_merge(
                        config::$vars['coscms_main'],
                        $shared
                    );
        }
    }

    
    /**
     * loads a config file were there is a PHP array
     * the file needs to have a variable called $config e.g.
     * $config = array ('my_setting' => true')
     * @param string $file
     */
    public static function loadPHPConfigFile($file) {
        include $file;
        if (isset(config::$vars['coscms_main'])) {
            config::$vars['coscms_main']+= $config;
        } else {
            config::$vars['coscms_main'] = $config;
        }
    }
    
    /**
     * load config from a php file. 
     * in the php file the configuration has to be set in: $config = array();
     * @param string $file
     */
    public static function loadPHPModuleConfig($file) {
        include $file;
        if (isset(config::$vars['coscms_main']['module'])) {
            config::$vars['coscms_main']['module']+= $config;
        } else {
            config::$vars['coscms_main']['module'] = $config;
        }
    }
    
    /**
     * checks if we are in cli env
     * @return boolean $res true if we are and false
     */
    public static function isCli () {
        if (isset($_SERVER['SERVER_NAME'])){
            return false;
        }
        return true;
    }
    
    /**
     * get computers hostname from command line
     * @return  string $hostname
     */
    public static function getHostnameFromCli () {
        return gethostname();
        //return trim(shell_exec('hostname'));
    }
    
    
    /**
     * method for getting a path to a module
     *
     * @param   string  $module the module
     * @return  string  $path the module path
     */
    public static function getModulePath ($module) {
        return _COS_PATH . '/' . _COS_MOD_DIR . '/' . $module;
    }
    
    /**
     * method for getting a path to a template
     *
     * @param   string  $template the template
     * @return  string  $path the template path
     */
    public static function getTemplatePath ($template) {
        return _COS_HTDOCS . '/templates/' . $template;
    }
    
   /**
    * function for getting a full path to public files folder when doing e.g. uploads
    * @param string|null $file optional file name to attach to path. You need to attach '/'
    * @return string $files_path the full file path 
    */
    public static function getFullFilesPath ($file = null) {
        $domain = config::getMainIni('domain');
        if ($domain == 'default') {
            $files_path = _COS_HTDOCS . "/files/default";
        } else {
            $files_path = _COS_HTDOCS . "/files/$domain";
        }
        
        if ($file) {
            return $files_path . $file;
        }
        
        return $files_path;
    }

    /**
     * method for getting domain. 
     * @return string $domain the current domain
     */
    public static function getDomain () {
        $domain = config::getMainIni('domain');
        return $domain;       
    }
    
    /**
     * method for getting domain. 
     * @return string $domain the current domain
     */
    public static function getServerName () {
        $server_name = config::getMainIni('server_name');
        if (!$server_name) $server_name = $_SERVER['SERVER_NAME'];
        return $server_name;     
    }
    
    public static function getSchemeWithServerName () {
        return config::getHttpScheme() . "://" . config::getServerName();
    }
     
   /**
    * method for getting the web path to files folder. 
    * @param string|null $file optional. a filename to attach to path. You need to attach '/'
    * @return string $path the web path to the file
    */
    public static function getWebFilesPath ($file = null) {
        if ($file) {
            return "/files/" . config::getDomain() . $file; 
        } 
        return "/files/" . config::getDomain();
    }
    
   /**
    * transform an array into a ini file string
    * @param   array     $ary array read from ini file with parse_ini_file
    * @return  string    $str ini string readable by parse_ini_file
    */
    public static function arrayToIniFile ($ary) {
        if (isset($ary['stage'])) {
            $stage = $ary['stage'];
            unset($ary['stage']);
        }
        
        if (isset($ary['development'])) {
            $development = $ary['development'];
            unset($ary['development']);
        }
        
        
        $content = self::parseIniSection($ary);
        if (isset($stage)) {
            $content.= "[stage]\n";
            $content.= self::parseIniSection($stage);
        }
        
        if (isset($development)) {
            $content.= "[development]\n";
            $content.= self::parseIniSection($development);
        }
        return $content;
    }
    
    public static function parseIniSection ($ary) {
        $content = '';
        foreach ($ary as $key => $val){
            if (is_array($val)){
                foreach ($val as $k => $v){
                    if(is_numeric($val) || is_bool($val)){
                        $content.= "{$key}[$k] = {$v}\n";
                    } else {
                        $content.= "{$key}[$k] = \"{$v}\"\n";
                    }
                }
            } else {
                if(is_numeric($val) || is_bool($val)){
                    $content.= "{$key} = {$val}\n";
                } else {
                    $content.= "{$key} = \"{$val}\"\n";
                }
            }
        }
        return $content;
    }
    
    /**
     * checks if we are on a windows server
     * @return boolean $res
     */
    public static function isWindows () {
        if (isset($_SERVER['WINDIR'])) {
            return true;
        }
        return false;
    }
    
    /**
     * returns name of web server user, e.g. www-data or false
     * if name can be fetched
     * @return mixed $user user on success or false on failure
     */
    public static function getServerUser () {
        $server_name = config::getMainIni('server_name');
        $url = 'http://' . $server_name . '/whoami.php';
        $handle = fopen($url, "r");
        if ($handle) {
            while (!feof($handle)) {
                $group = fgets($handle, 4096);
            }
        } else {
            return false;
        }
        fclose($handle);
        return $group;
    }
}

/**
 * @ignore
 * function methods of the above static class methods
 * mostly for backward issues
 * 
 */
function array_to_ini_file($ary){
    return config::arrayToIniFile($ary);
}

/**
 * @ignore
 */
function get_module_ini($key){
    return config::getModuleIni($key);
}

/**
 * @ignore
 */
function get_main_ini($key){
    return config::getMainIni($key);
}

/**
 * @ignore
 */
function get_module_path ($module){
    return config::getModulePath($module);
}

/**
 * @ignore
 */
function parse_ini_file_ext ($file, $sections = null) {
    return config::getIniFileArray($file, $sections);
}

/**
 * @ignore
 */
function get_config_file() {
    return config::getConfigFileName();
}

/**
 * @ignore
 */
function load_config_file () {
    config::loadMain();
}

/**
 * @ignore
 */
function get_files_path () {
    return config::getFullFilesPath();
}

/**
 * @ignore
 */
function get_files_web_path ($file) {
    return config::getWebFilesPath($file);
}

/**
 * @ignore
 */
function get_domain () {
    return config::getDomain();
}
