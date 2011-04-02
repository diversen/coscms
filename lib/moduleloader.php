<?php

/**
 * File contains class for loading modules
 *
 * @package    coslib
 */

/**
 * Class for loading modules
 *
 * @package    coslib
 */
class moduleLoader {

    /**
     *
     * @var array all enabled modules
     */
    public static $modules = array();

    /**
     *
     * @var array holding different run levels
     */
    private $levels = array();

    /**
     *
     * @var array $info holding info about files to load.
     */
    public $info = array();
    /**
     * @var     array   static variable which can be set in case we don't
     *                  want to load called module. Used for enablingloading
     *                  of error module when an error code has been set.
     *                  self::$status[403] or self::$status[404]
     */
    public static $status = array();

    /**
     *
     * @var array   var holding modules ini settings. Used if we want to
     *              load ini settings from other modules than current
     */
    public static $iniSettings = array();
    /**
     * constructer recieves module list and places them in $this->levels where
     * we can see at which run level a module should be used.
     *
     * @param array $modules all enabled modules
     */
    public function __construct(){
        //$this->connect();
        self::$modules = self::getAllModules(); //$this->selectAll('modules');
        //print_r($this->modules);
        $this->setLevels();

        if (!isset(register::$vars['coscms_main']['module'])){
            register::$vars['coscms_main']['module'] = array();
        }
    }

    /**
     * method for getting all modules from db
     *
     * @return array    array with all rows from modules table
     */
    public static function getAllModules (){

        // we connect here because this is the first time we use database
        // in the system

        if (!empty(self::$modules)) {
            return self::$modules;
        }

        $db = new db();
        $db->connect();

        return $db->selectAll('modules');
    }

    /**
     *
     *
     * @param string $parent
     * @return array array containing child modules.
     */
    public static function getChildModules ($parent){
        static $children = array();
        if (isset($children[$parent])) return $children[$parent];

        foreach (self::$modules as $key => $val){
            if ($val['parent'] == $parent){
                $children[$parent][] = $val['module_name'];
            }
        }

        if (empty($children[$parent])){
            return array();
        }

        return $children[$parent];
    }

    /**
     * method for getting parent module name.
     * 
     * @param string    module
     * @return string   parent module
     */
    public static function getParentModule ($module){        
        static $parent;
        if (isset($parent)) return $parent;
        foreach (self::$modules as $key => $val){
            if ($val['module_name'] != $module) { 
                continue;
            } else {
                if (isset($val['parent'])){
                    $parent = $val['parent'];
                    return $parent;
                }
            }
        }
        return null;
    }

    /**
     * method for placeing all modules in $this->levels according to run_level
     */
    private function setLevels(){
        foreach (self::$modules as $key => $val){
            $module_levels = explode(',', $val['run_level']);
            foreach ($module_levels as $k => $v){
                $this->levels[$v][] = $val['module_name'];
            }
        }
    }

    /**
     * method for checking if a module is installed.
     */
    private function isInstalledModule($module){
        foreach (self::$modules as $key => $val){
            if ($val['module_name'] == $module){
                return true;
            }

        }
        return false;
    }

    /**
     * method for running a module at a exact runlevel.
     *
     * @param int run a runlevel of the system.
     */
    public function runLevel($level){

        if (!isset($this->levels[$level])) return;
        foreach($this->levels[$level] as $key => $val){
            $class_path = _COS_PATH . "/modules/$val/model.$val.inc";
            include_once $class_path;
            $class = new $val;
            $class->runLevel($level);
        }
    }

    /**
     * method for setting info for homepage controller /
     *
     */
    public function setHomeModuleFiles(){
        $frontpage_module = register::$vars['coscms_main']['frontpage_module'];//$_COS_MAIN['frontpage_module'];
        $this->info['base'] = $base = _COS_PATH . "/modules";
        $this->info['language_file'] = $base . "/$frontpage_module" . '/lang/' . register::$vars['coscms_main']['language'] . '/language.inc';
        $this->info['ini_file'] =  $base . "/$frontpage_module"  . "/$frontpage_module" . '.ini';
        $this->info['model_file'] = $base . "/$frontpage_module"  . "/model." . $frontpage_module  . ".inc";
        $this->info['view_file'] = $base . "/$frontpage_module"  . "/view." . $frontpage_module . ".inc";

        $controller_dir = $base . "/$frontpage_module/";

        $first = uri::fragment(0);
        
        if (!empty($first)){
            $controller_file = $controller_dir . $first . ".php";
        } else {
            $controller_file = $controller_dir . "index.php";
        }
        $this->info['controller_file'] = $controller_file;

    }
    
    /**
     * method for setting info for homepage controller /
     */
    public function setErrorModuleFiles(){     
        $error_module = 'error';
        $this->info['base'] = $base = _COS_PATH . "/modules";
        $this->info['language_file'] = $base . "/$error_module" . '/lang/' . register::$vars['coscms_main']['language'] . '/language.inc';
        $this->info['ini_file'] =  $base . "/$error_module"  . "/$error_module" . '.ini';
        $this->info['model_file'] = $base . "/$error_module"  . "/model." . $error_module  . ".inc";
        $this->info['view_file'] = $base . "/$error_module"  . "/view." . $error_module . ".inc";

        if (isset(self::$status[404])){
            $controller_file = $base . "/$error_module". '/404.php';
        }
        if (isset(self::$status[403])){           
            $controller_file = $base . "/$error_module". '/403.php';
        }

        $this->info['controller_file'] = $controller_file;
        $this->info['controller'] = "403.php";
    }

    /**
     * method for setting a parsing modules info
     */
    public function setModuleFiles (){
        $uri = uri::getInstance();
        $info = $uri->getInfo();

        


        if (empty($info['module_base'])){
            $this->setHomeModuleFiles();
            return;
        }

        // if we only have on fragment means we are in frontpage module
        $frontpage_module = register::$vars['coscms_main']['frontpage_module'];

        if ($uri->numFragments() == 1){
            $info['module_base_name'] = $frontpage_module ;
            $this->info['base'] = $base = _COS_PATH . "/modules/$frontpage_module";
        } else {
            // set info so we can load all a modules files
            $this->info['base'] = $base = _COS_PATH . "/modules";
        }


        
        $this->info['language_file'] = $base . $info['module_base'] . '/lang/' . register::$vars['coscms_main']['language'] . '/language.inc';
        $this->info['ini_file'] =  $base . $info['module_base'] . $info['module_base'] . '.ini';
        $this->info['ini_file_php'] =  $base . $info['module_base'] . $info['module_base'] . '.php.ini';
        $this->info['model_file'] = $base . $info['controller_path_str'] . "/model." . $info['module_frag'] . ".inc";
        $this->info['view_file'] = $base . $info['controller_path_str'] . "/view." . $info['module_frag'] . ".inc";        
        $controller_file = $base . $info['controller_path_str'] . '/' . $info['controller'] . '.php';
        
        
        $this->info['controller_file'] = $controller_file;
        $this->info['controller'] = $info['controller'];

        // all we need is a controller. anything else is optional
        if (!file_exists($this->info['controller_file'])){
            self::$status[404] = 1;
            $this->setErrorModuleFiles();    
        }

        if (!$this->isInstalledModule($info['module_base_name'])){
            self::$status[404] = 1;
            $this->setErrorModuleFiles(); 
        }
    }



    /**
     * method for initing a module
     *
     */
    public function initModule(){

        // load ini file if exists
        if (file_exists($this->info['ini_file'])){
            register::$vars['coscms_main']['module'] = parse_ini_file($this->info['ini_file'], true);

            // check if development settings exists.
            if (isset(register::$vars['coscms_main']['module']['development'])){
                // check if we are on a development server. 
                // Note: Development needs to be set in main config/config.ini

                if (
                    
                    register::$vars['coscms_main']['development']['server_name']
                        ==
                    @$_SERVER['SERVER_NAME']){


                    // we are on development, merge and overwrite normal settings with
                    // development settings.
                    register::$vars['coscms_main']['module'] =
                        array_merge(
                            register::$vars['coscms_main']['module'],
                            register::$vars['coscms_main']['module']['development']
                        );
                }
            }

            // load php ini if exists
            if (isset(register::$vars['coscms_main']['module']['load_php_ini'])){
                include $this->info['ini_file_php'];
                register::$vars['coscms_main']['module'] = array_merge(register::$vars['coscms_main']['module'], $_MODULE_SETTINGS);
            }

            // load moule template if specified
            if (isset(register::$vars['coscms_main']['module']['template'])){
                register::$vars['coscms_main']['template'] = register::$vars['coscms_main']['module']['template'];
            }

            // load controller specific template if specified
            if (isset(register::$vars['coscms_main']['module']['page_template'])){
                $page_template = explode (':', register::$vars['coscms_main']['module']['page_template']);
                if ($this->info['controller'] == $page_template[0]){
                    register::$vars['coscms_main']['template'] = $page_template[1];
                }
            }
        }
        
        // include model if exists
        if (file_exists($this->info['model_file'])){
            include_once $this->info['model_file'];
        }

        // include view file if exists
        if (file_exists($this->info['view_file'])){
            include_once $this->info['view_file'];
        }

        // include language file if exists.
        if (file_exists($this->info['language_file'])){
            include $this->info['language_file'];
            if (isset($_COS_LANG_MODULE)){
                lang::$dict = array_merge(lang::$dict, $_COS_LANG_MODULE);
            }
        }

        // load any modules connected to this module
        // we can see this is 'load_on' is set in module table
        $module_name = uri::$info['module_name'];
        foreach (self::$modules as $key => $val){
            if (!isset($val['load_on'])) continue;
            if ($val['load_on'] === $module_name){
                include_module($val['module_name']);
                $class_name = self::modulePathToClassName($val['module_name']);
                $class_object = new $class_name(); 
                $class_object->init();
                //self::modulePathToClassName('testmodule');
            }    
        }
    }

    /**
     * return modules classname from a modules path.
     * @param  string   $path (e.g. account_profile)
     * @return string   $classname (e.g. accountProfile)
     */
    public static function modulePathToClassName ($path){

        $ary = explode('/', $path);
        if (count($ary) == 1){
            $class = $path;
        }
        if (count($ary) == 2){
            $class = $ary[0] . ucfirst($ary[1]);
        }

        $ary = explode('_', $class);
        if (count($ary) == 1){
            return $ary[0];
        }
        if (count($ary) == 2){
            $str = $ary[0] . ucfirst($ary[1]);
            return $str;
        }
    }

    /**
     * returns a modules class path from modules path
     * @param   string $path (e.g. content/article)
     * @return  string $class_path (e.g. content/article/model.article.inc)
     */
    public static function modulePathToModelPath ($path){
        $ary = explode('/', $path);
        if (count($ary) == 1){
            return "$path/model.$path.inc";
        }
        if (count($ary) == 2){
            return "$path/model.$ary[1].inc";
        }
    }
    
    /**
     * method for loading a parsing module (runlevel 0)
     *
     * @return string the parsed modules html
     */
    public function loadModule(){
        include_once $this->info['controller_file'];        
        if (isset(self::$status[403])){
            $this->setErrorModuleFiles();
            $this->initModule();
            include_once $this->info['controller_file'];  
        }

        if (isset(self::$status[404])){
            $this->setErrorModuleFiles();
            $this->initModule();
            include_once $this->info['controller_file'];
        }

        $str = ob_get_contents();
        ob_clean();
        return $str;
    }
    
    /**
     * method for getting a modules ini settings.
     *
     * @return  array   array with ini settings of module.
     */
    public static function getModuleIniSettings($module){

        // only read ini file settings once.
        if (!isset(self::$iniSettings[$module])){
            self::setModuleIniSettings($module);
            return self::$iniSettings[$module];
        } else {
            return null;
        }
    }

    /**
     * method for getting a modules ini settings.
     *
     * @return  array   array with ini settings of module.
     */
    public static function setModuleIniSettings($module){

        // only read ini file settings once.
        //if (isset(self::$iniSettings[$module])){
        //    return self::$iniSettings[$module];
        // }
        if (!isset(self::$iniSettings['module'])){
            self::$iniSettings['module'] = array();
        }

        $ini_file = _COS_PATH . "/modules/$module/$module.ini";
        self::$iniSettings[$module] = parse_ini_file($ini_file, true);

        if (is_array(self::$iniSettings['module'])){
            register::$vars['coscms_main']['module'] = array_merge(
                register::$vars['coscms_main']['module'],
                self::$iniSettings[$module]
            );
        }
    }
}