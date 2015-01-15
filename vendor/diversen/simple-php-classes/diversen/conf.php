<?php

namespace diversen;
use diversen\moduleloader;
/**
 * contains methods for getting and setting configuration. 
 * @package config
 */

/**
 * config class. 
 * 
 * read and load configuration files from config/config.ini and 
 * modules/e.g.  modules/som_module/some_module.ini  
 * @package config
 */

class conf {
    
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
        if (!isset(self::$vars['coscms_main']['module'][$key])){
            return null;
        }
        if (self::$vars['coscms_main']['module'][$key] == '0'){
            return null;
        }
        
        if (empty(self::$vars['coscms_main']['module'][$key])){
            return null;
        }
        return self::$vars['coscms_main']['module'][$key];
    }
    
    public static function setModuleIni ($key, $value) {
        self::$vars['coscms_main']['module'][$key] = $value;
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
     * without database override. E.g. with the modules settings and locales
     * @param   string  $key the ini setting key to get
     * @return  mixed   $val the value of the setting or null if not found. 
     *                       If 0 is found we also reutnr null
     */    
    public static function getMainIniFromFile ($key) {
        return self::getMainIniFromHolder($key, 'coscms_main_file');
    } 
    
    /**
     * return main ini from placeholder
     * @param string $key
     * @param string $holder
     * @return mixed $val
     */
    private static function getMainIniFromHolder ($key, $holder = 'coscms_main') {
        if (!isset(self::$vars[$holder][$key])){
            return null;
        }
        
        if (self::$vars[$holder][$key] == '0'){
            return null;
        }
        
        
        if (self::$vars[$holder][$key] == 'true') {
            return true;
        }
        
        if (self::$vars[$holder][$key] == 'false') {
            return false;
        }
        return self::$vars[$holder][$key];    
    } 
    

    
    /**
     * method for getting a main ini setting found in config/config.ini
     * @param   string  $key the ini setting key to get
     * @return  mixed   $val the value of the setting or null if not found. 
     *                       If 0 is found we also reutnr null
     */    
    public static function getMainIniAsString($key) {
        if (!isset(self::$vars['coscms_main'][$key])){
            return null;
        }
        
        if (self::$vars['coscms_main'][$key] == '0'){
            return null;
        }
               
        if (self::$vars['coscms_main'][$key] == 'true') {
            return "true";
        }
        
        if (self::$vars['coscms_main'][$key] == 'false') {
            return "false";
        }
        return self::$vars['coscms_main'][$key];      
    }
    
    /**
     * sets a main ini setting, e.g. override from database
     * @param string $key the key to set
     * @param string $value the value to set the key with
     */
    public static function setMainIni ($key, $value) {
        self::$vars['coscms_main'][$key] = $value;
    }
    
    /**
     * sets a main ini setting, e.g. override from database
     * @param string $key the key to set
     * @param string $value the value to set the key with
     */
    public static function setMainIniWithArray ($ary) {
        foreach ($ary as $key => $val) {
            self::$vars['coscms_main'][$key] = $val;
        }
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
        $server_force_ssl = self::getMainIni('server_force_ssl');
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
     * the correct virtual host. E.g. config/multi/www.testsite.com/config.ini
     * where domain is www.testsite.com . 
     * 
     * In normal mode the domain name is checked against $_SERVER['SERVER_NAME'].
     * If this name matches file config/multi/domain/config.ini then this
     * file will be used. 
     * 
     * If file is not set it is the normal config/config.ini which will be included. 
     * 
     * @return string $filename the filname of the config file we should load.  
     */
    
    public static function getConfigFileName () {
        //return get_config_file();
        // determine host and see if we use virtual hosting
        // where one code base can be used for more virtual hosts.
        // this is set with the domain flag in ./coscli.sh
        if (self::isCli()){
            if (isset(self::$vars['domain']) && self::$vars['domain'] != 'default'){
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
     * var holding which env we live in
     * @var string 
     */
    public static $env = null;
    
    /**
     * try to match ini settings server name with $_SERVER['SERVER_NAME']
     * This is used in order to determine if we are on development, stage,
     * or production server in web mode 
     * @param string $server_name e.g. *.testserver.com or www.testserver.com
     * @return boolean $res true if there is match else false
     */
    public static function serverMatch ($server_name) {
        
        if ($server_name == $_SERVER['SERVER_NAME']) {
            return true;
        }
        
        if ($server_name == $_SERVER['SERVER_NAME'] . ":" . $_SERVER['SERVER_PORT']) {
            return true;
        }
        
        if (fnmatch("*.$server_name", $_SERVER['SERVER_NAME'])) {
            return true;
        }
        return false;
    }
    
    /**
     * returns server env (development, stage, production) from match
     * between $_SERVER['SERVER_NAME'] and main ini server_name setting
     * If no match we return production. Notice that self::$env will
     * be set and base system only calls this function once.  
     * @return string $env development, stage, production - if no match
     *                      function will return production
     */
    public static function getEnvServer () {
        if (self::serverMatch(self::getMainIni('server_name'))) {
            self::$env = 'production';
            return 'production';
        }

        if (isset(self::$vars['coscms_main']['stage'])) {
            if (self::serverMatch(self::$vars['coscms_main']['stage']['server_name'])) {
                self::$env = 'stage';
                return 'stage';
            }
        }

        if (isset(self::$vars['coscms_main']['development'])) {
            if (self::serverMatch(self::$vars['coscms_main']['development']['server_name'])) {
                self::$env = 'development';
                return 'development';
            }
        }
        return 'production';
    }
    
    /**
     * returns cli env (development, stage, production) based on php function 'gethostname'
     * If production = 1 in config/shared.ini then this will return 'production'
     * We can set production in shared.ini as many developers use the same 
     * server for production and stage, and then this will override the hostname
     * and development or stage settings will not be loaded
     * @return string $env development, stage, production
     */
    public static function getEnvCli () {
                    
        if (self::getMainIni('production') == 1) {
            return 'production';
        }

        if (isset(self::$vars['coscms_main']['development'])) {
            if (in_array(self::getHostnameFromCli(), self::getHostnameFromIni('development'))) {
                return 'development';
            }
        }

        if (isset(self::$vars['coscms_main']['stage'])) {
            if (in_array(self::getHostnameFromCli(), self::getHostnameFromIni('stage'))) {
                return 'stage';
            }
        }
        return 'production';
    }
    
    /**
     * get environemnt - production, stage, or development for Cli og server
     * This is called just after main ini has been loaded
     * In this way we can always know the correct env if we need it. 
     * @return string production, stage, or development
     */
    public static function getEnv () {
        
        if (self::$env) {
            return self::$env;
        }
        
        if (!self::isCli()) {
            return self::getEnvServer();
        } else {
            return self::getEnvCli();
        }
    }
    
    /**
     * Function for loading the main config file
     * found in config/config.ini
     * 
     * You can place global configuration in this file. 
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
        
        $config_file = self::getConfigFileName();    
        if (!file_exists($config_file)){
            return;
        } else {
            self::$vars['coscms_main'] = self::getIniFileArray($config_file, true);
            
            // set them in coscms_main_file, so it is possbile
            // to get original value without db viewing e.g. db override. 
            self::$vars['coscms_main_file'] = self::$vars['coscms_main'];
            
            // check if any shared.ini settings should be merged
            self::mergeSharedIni();
            
            if ( self::getEnv() == 'production' ) {

                    // We are on REAL server and exits without
                    // adding additional settings for stage or development
                    // or CLI mode. 
                    return; 
            }

            // Test if we are on stage server. 
            // Overwrite register settings with stage settings
            if ( self::getEnv() == 'stage') {

                    // we are on development, merge and overwrite normal settings with
                    // development settings.
                    self::$vars['coscms_main'] =
                    array_merge(
                        self::$vars['coscms_main'],
                        self::$vars['coscms_main']['stage']
                    );
                    self::$vars['coscms_main']['development'] = 'stage';
                    return;

            }
            // We are on development server. 
            // Overwrite register settings with development settings
            if (self::getEnv() == 'development') {

                    self::$vars['coscms_main'] =
                    array_merge(
                        self::$vars['coscms_main'],
                        self::$vars['coscms_main']['development']
                    );
                    self::$vars['coscms_main']['development'] = 'development';

            }
        }
    }
    
        
    /**
     * load main cli configuration
     */
    public static function loadMainCli () {
        $config_file = self::getConfigFileName();
        
        if (!file_exists($config_file)){
            return;
        } else {
            self::$vars['coscms_main'] = self::getIniFileArray($config_file, true);
            
            // AS 'production' often is on the same server as 'stage', we 
            // need to set: 
            // 
            // production= 1
            // 
            // in config/shared.ini in order to know if we are on production or on
            // stage. 
            // 
            self::mergeSharedIni();
            
            if (self::getMainIni('production') == 1){
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
            if (self::getEnv() == 'stage'){

                    // we are on development, merge and overwrite normal settings with
                    // development settings and return
                    self::$vars['coscms_main'] =
                    array_merge(
                        self::$vars['coscms_main'],
                        self::$vars['coscms_main']['stage']
                    );
                    return;
                //}
            }
            // We are on development server. 
            // Overwrite register settings with development settings
            // Development settings will ALSO be added to CLI
            // ini settings
            if (self::getEnv() =='development') {
                    self::$vars['coscms_main'] =
                    array_merge(
                        self::$vars['coscms_main'],
                        self::$vars['coscms_main']['development']
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
     *  _COS_PATH which all other defines are based on
     */
    
    public static function defineCommon () {
        
        $htdocs_path = self::getMainIni('htdocs_path');

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
        $mod_dir = self::getMainIni('module_dir');

        if (!$mod_dir) {
            $mod_dir = 'modules';
        }

        define ('_COS_MOD_DIR', $mod_dir);
        define ('_COS_MOD_PATH', _COS_PATH . '/' . _COS_MOD_DIR);
        
        /**
         * define path to htdocs files (uploads)
         */
        $files_dir = self::getMainIni('htdocs_files');
        if (!$files_dir) {
            define('_COS_FILES',  'files');
        } else {
            define('_COS_FILES',  $files_dir);
        }
    }
    
    /**
     * gets the hostname from ini settings. You can use multiple hostnames 
     * in order to work on diffrent machines in the same environment. 
     * The hostname is set in the ini settings hostname
     * @param type $section if normal default section. Else stage or development
     * @return array $hostnames array with all hostnames
     *               hostnames are set in config/config.ini in ini setting 'hostname'
     *               you can add multiple hosts e.g. for development by seperating
     *               them with a ':' e.g. dennis-desktop:dennis-laptop
     */
    public static function getHostnameFromIni ($section = null) {
        if (!$section) {
            $hostnames = @self::$vars['coscms_main']['hostname'];
        }
        
        if ($section == 'stage') {
            $hostnames = @self::$vars['coscms_main']['stage']['hostname'];
        }
        
        if ($section == 'development') {
            $hostnames = @self::$vars['coscms_main']['development']['hostname'];
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
            $shared = self::getIniFileArray($shared_ini);
            self::$vars['coscms_main'] =
                    array_merge(
                        self::$vars['coscms_main'],
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
        if (isset(self::$vars['coscms_main'])) {
            self::$vars['coscms_main']+= $config;
        } else {
            self::$vars['coscms_main'] = $config;
        }
    }
    
    /**
     * load config from a php file. 
     * in the php file the configuration has to be set in: $config = array();
     * @param string $file
     */
    public static function loadPHPModuleConfig($file) {
        include $file;
        if (isset(self::$vars['coscms_main']['module'])) {
            self::$vars['coscms_main']['module']+= $config;
        } else {
            self::$vars['coscms_main']['module'] = $config;
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
     * get the computers hostname from command line
     * @return  string $hostname
     */
    public static function getHostnameFromCli () {
        return gethostname();
    }
    
    
    /**
     * method for getting a path to a module
     * @param   string  $module the module
     * @return  string  $path the module path
     */
    public static function getModulePath ($module) {
        return _COS_PATH . '/' . _COS_MOD_DIR . '/' . $module;
    }
    
    /**
     * method for getting a path to a template
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
        $domain = self::getMainIni('domain');
        
        // check if special files_storage self is set
        $storage = self::getMainIni('files_storage');
        if ($storage) {
            $files_path = $storage;
        } else {
            $files_path = _COS_HTDOCS . "/files";
        }
        
        if ($domain == 'default') {
            $files_path.= "/default";
        } else {
            $files_path.= "/$domain";
        }
        
        if ($file) {
            return $files_path . $file;
        }
        
        return $files_path;
    }

    /**
     * method for getting "domain". Domain should not be confused with
     * server_name. Domain is used when multiple server_names share the 
     * same code base. 
     * 
     * Inside config/ folder there is a dir (or make it) called
     * multi. If you have two hosts pointing to the same document root, e.g. 
     * default and default2 you can make a folder called multi/default2 and
     * add a file called config.ini inside this folder. When a client request
     * http:://domain2/ this will be served from the confiuration file 
     * multi/default2/config.ini 
     * 
     * In Cli Env you can specify -d as first argument,
     * e.g. ./coscli.sh -d default2 db --con and the default2 config file 
     * will be used. All sub domains will also respond to diffrent environment, 
     * stage, development, production (speicifed as sections in config.ini 
     * settings)
     * 
     * If no domain is given, the default config/config.ini will be loaded. 
     *  
     * @return string $domain the current domain
     */
    public static function getDomain () {
        $domain = self::getMainIni('domain');
        return $domain;       
    }
    
    /**
     * method for getting server name. If not set in config.ini ('server_name')
     * $_SERVER['SERVER_NAME'] will be used
     * @return string $server_name the server name.  
     *                
     */
    public static function getServerName () {
        $server_name = self::getMainIni('server_name');
        if (!$server_name) { 
            $server_name = $_SERVER['SERVER_NAME'];
        }
        return $server_name;     
    }
    
    /**
     * returns http|https:://server_name
     * based on configuration
     * @return string $str server name with http|https scheme
     */
    public static function getSchemeWithServerName () {
        return self::getHttpScheme() . "://" . self::getServerName();
    }
     
   /**
    * method for getting the web path to files folder. 
    * @param string|null $file optional. a filename to attach to path. You need to attach '/'
    * @return string $path the web path to the file
    */
    public static function getWebFilesPath ($file = null) {
        if ($file) {
            return "/files/" . self::getDomain() . $file; 
        } 
        return "/files/" . self::getDomain();
    }
    
   /**
    * transform an array into a ini file string. It takes into account what
    * environment we are in, development, stage or production. It also 
    * takes into account if language is used for transforming the ini string.  
    * @param   array     $ary an array parsed with with parse_ini_file
    * @return  string    $str ini string readable by parse_ini_file
    */
    public static function arrayToIniFile ($ary, $check_lang = true) {
        
        // locales are almost always installed but we test anyway
        // sort of a base module
        if ($check_lang) {
            if (moduleloader::isInstalledModule('locales')) {
                $locales = \locales_module::getLanguages();
            }
        } else {
            $locales = array();
        }
        
        // check for locales in config array, e.g. en or en_GB or da
        $locale_sections = '';
        foreach ($locales as $row) {
            $lang = $row['language'];
            if (isset($ary[$lang])) {
                $locale_sections.= "[$lang]\n";
                $locale_sections.= self::parseIniSection($ary[$lang]);
                unset($ary[$lang]);
            }
        }

        if (isset($ary['stage'])) {
            $stage = $ary['stage'];
            unset($ary['stage']);
        }
        
        if (isset($ary['development'])) {
            $development = $ary['development'];
            unset($ary['development']);
        }
        
        
        $content = self::parseIniSection($ary);
        if (!empty($locale_sections)) {
            $content.=$locale_sections;
        }
        
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
    
    /**
     * parses a ini section
     * @param array $ary
     * @return string $content a .ini string
     */
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
        $server_name = self::getMainIni('server_name');
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
