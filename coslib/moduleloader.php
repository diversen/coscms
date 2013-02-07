<?php

/**
 * File contains class for loading modules
 *
 * @package    moduleloader
 */

/**
 * define path to modules
 */

$mod_dir = config::getMainIni('module_dir');

if (!$mod_dir) {
    $mod_dir = 'modules';
}

define ('_COS_MOD_DIR', $mod_dir);
define ('_COS_MOD_PATH', _COS_PATH . '/' . _COS_MOD_DIR);

/**
 * Class for loading modules
 *
 * @package    moduleloader
 */
class moduleloader {

    /**
     * all enabled modules
     * @var array $modules 
     */
    public static $modules = array();

    /**
     * holding different run leve
     * @var array $lvelsls
     */
    public $levels = array();

    /**
     * holding info about files to load when loaidng module.
     * @var array $info 
     */
    public $info = array();
    
    /**
     *                  
     *                  static variable which can be set in case we don't
     *                  want to load called module. Used for enablingloading
     *                  of error module when an error code has been set.
     *                  self::$status[403] or self::$status[404]
     * @var     array   $status 

     */
    public static $status = array();

    /**
     * holding module ini settings.
     * @var array $iniSettings 
     */
    public static $iniSettings = array();
    
    
    /**
     * constructer recieves module list and places them in $this->levels where
     * we can see at which run level modules should be run.
     * 
     * ModuleLoader will call self::getAllModules which in turn will
     * connect first time to database. 
     */
    public function __construct(){
        self::$modules = self::getAllModules(); 
        $this->setLevels();


    }
    
    /**
     * method for setting a status code 403 or 404
     * @param int $code
     */
    public static function setStatus ($code) {
        moduleloader::$status[$code] = 1;
    }

    /**
     * method for getting all modules from db. This is the first time we 
     * connect to database. 
     * 
     * @return array $ary array with all rows from modules table
     */
    public static function getAllModules (){

        if (!empty(self::$modules)) {
            return self::$modules;
        }
        
        static $modules = null;
        if ($modules) return $modules;
        
        // we connect here because this should be 
        // the first time we use the database
        // in the system
        
        $db = new db();
        $db->connect();

        return $db->selectAll('modules');
    }
    
    /**
     * get all installed module name only
     * @return array $ary array of module names 
     */
    public static function getInstalledModuleNames () {
        $mods = self::getAllModules();
        $ins = array ();
        foreach ($mods as $val) {
            $ins[] = $val['module_name'];
        }
        return $ins;
    } 

    /**
     * moduleExists alias of isInstalledModule
     * @param string $module_name
     * @return boolean $res true if module exists else false.  
     */
    public static function moduleExists ($module_name) {
        return self::isInstalledModule($module_name);
    }
    
    
    
    /**
     *
     * get child modules to a parent module
     * @param string $parent name of parent module
     * @return array $ary containing child modules.
     */
    public static function getChildModules ($parent){
        static $children = array();
        if (isset($children[$parent])) return $children[$parent];

        foreach (self::$modules as $val){
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
     * method for getting a modules parent name.
     * 
     * @param string    $module the module to examine for a parent
     * @return mixed    $res if a parent module is found we return the parent module name
     *                       else we return null
     */
    public static function getParentModule ($module){        
        static $parent = null;
        if (isset($parent)) return $parent;
        foreach (self::$modules as $val){
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
     * 
     * method for placeing all modules in $this->levels 
     * according the modules run_levels
     */
    public function setLevels(){
        foreach (self::$modules as $key => $val){
            $module_levels = explode(',', $val['run_level']);
            foreach ($module_levels as $k => $v){
                $this->levels[$v][] = $val['module_name'];
            }
        }
    }

    /**
     * check if a module is installed / exists
     * @param string $module the module we examine
     * @return boolean $res boolean
     */
    public static function isInstalledModule($module){  
        static $installed = array ();
        if (isset($installed[$module])) return true;
        foreach (self::$modules as $val){
            if ($val['module_name'] == $module){
                $installed[$module] = true;
                return true;
            }
        }
        return false; 
    }

    /**
     * method for running a module at a exact runlevel.
     * This is used in coslib/head.php (bootstrap file)
     *
     * @param int $level the runlevel to run [1- 7]
     */
    public function runLevel($level){
        if (!isset($this->levels[$level])) return;
        foreach($this->levels[$level] as $val){
            moduleloader::setModuleIniSettings($val);
            $class_path = _COS_MOD_PATH . "/$val/model.$val.inc";
            include_once $class_path;
            $class = new $val;
            $class->runLevel($level);
        }
    }

    /**
     * method for setting info for home module info
     * home module is set in config/config.ini with frontpage_module
     * home module deals with requests to /
     */
    public function setHomeModuleInfo(){
        
        $frontpage_module = config::getMainIni('frontpage_module');
        $this->info['module_name'] = $frontpage_module;
        $this->info['module_base_name'] = $frontpage_module;
        $this->info['base'] = $base = _COS_MOD_PATH;
        $this->info['language_file'] = $base . "/$frontpage_module" . '/lang/' . config::getMainIni('language') . '/language.inc';
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
        $this->info['controller'] = 'index';
        $this->info['module_class'] = $this->info['module_name'] . "_module";

    }
    
    /**
     * method for setting info for error module. E.g. we recieve
     * a 404 or 403 from a module, and we want to let the error module
     * take care
     * 
     * Notice: At the moment you can not have your own error module. 
     * But it wil lbe easy to implement at some point. 
     * 
     */
    public function setErrorModuleInfo(){
        
        $error_module = config::getMainIni('error_module');
        if (!$error_module) {
            $error_module = 'error';
        }
        $this->info['module_name'] = $error_module;
        $this->info['module_base_name'] = $error_module;
        $this->info['base'] = $base = _COS_MOD_PATH;
        
        $this->info['language_file'] = $base . "/$error_module" . '/lang/' . config::getMainIni('language'). '/language.inc';
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
        $this->info['module_class'] = $this->info['module_name'];
        
    }
    

    /**
     * Method for setting the requested module info
     */
    public function setModuleInfo ($route = null){

        $uri = uri::getInstance($route);
        $info = uri::getInfo();
       
        // if no module_base is set in the URI::info we can will use
        // the home module
        if (empty($info['module_base'])){
            $this->setHomeModuleInfo();
            return;
        }

        // if we only have one fragment 
        // means we are in frontpage module
        $frontpage_module = config::getMainIni('frontpage_module');
        $this->info['module_name'] = $info['module_name'];
        if ($uri->numFragments() == 1){         
            $this->info['module_base_name'] = $frontpage_module;
            $this->info['module_class'] = $this->info['module_name'];
            $this->info['base'] = $base = _COS_MOD_PATH . "/$frontpage_module";
        } else {
            $this->info['module_base_name'] = $info['module_base_name'];
            $this->info['module_class'] = str_replace('/', '_', $this->info['module_name']);
            $this->info['base'] = $base = _COS_MOD_PATH;
        }

        
        $this->info['language_file'] = $base . $info['module_base'] . '/lang/' . config::getMainIni('language'). '/language.inc';
        $this->info['ini_file'] =  $base . $info['module_base'] . $info['module_base'] . '.ini';
        $this->info['ini_file_php'] =  $base . $info['module_base'] . $info['module_base'] . '.php.ini';
        $this->info['model_file'] = $base . $info['controller_path_str'] . "/model." . $info['module_frag'] . ".inc";
        $this->info['view_file'] = $base . $info['controller_path_str'] . "/view." . $info['module_frag'] . ".inc";    
         
        $controller_file = $base . $info['controller_path_str'] . '/' . $info['controller'] . '.php';
        
        $this->info['controller_file'] = $controller_file;
        $this->info['controller'] = $info['controller'];
        //$this->info['module_class'] = 'cosmod_' . $info['module_base_name'] . "_module";
        


    }

    /**
     * method for initing a module
     * Loads ini file if exists. If ini file exists. 
     * Check for PHP ini PHP config exists. 
     * Sets module template if specified. 
     * Sets controller specific template if specified. 
     * 
     * Then loads the model file model.module_name.inc if exists
     * Then loads the view file view.module_name.inc if exists
     * Then load language if exists
     * 
     * If any module then have to be called. We init any modules
     * from information found in the module table. These modules
     * has the flag load_on set 
     */
    public function initModule(){
        
        $module = $this->info['module_name'];       
        moduleloader::includeModule($module);


        // set module template if specified
        // e.g. in account.ini:
        // template = 'clean'
        if (isset(config::$vars['coscms_main']['module']['template'])){
            config::$vars['coscms_main']['template'] = config::$vars['coscms_main']['module']['template'];
        }

        // load controller specific template if specified
        // e.g. set this is account.ini:
        // page_template = '/account/admin/list:clean';
        // can only be used for one controller per module.
        if (isset(config::$vars['coscms_main']['module']['template_controller'])){
            $page_template = explode (':', config::$vars['coscms_main']['module']['template_controller']);   
            $controller_path = "/" . $this->info['module_name'] . "/" . $this->info['controller'];   
            if ($controller_path == $page_template[0]){
                config::$vars['coscms_main']['template'] = $page_template[1];
            }
        }
    }
    
    /**
     * var holding a reference name
     * @var mixed $reference 
     */
    public static $reference = null;
    
    /**
     * var holding a referenceId
     * @var int $referenceId 
     */
    public static $referenceId = 0;
    
    /**
     * var id holding inline parent id
     * @var int $id
     */
    public static $id;
    
    /**
     * var holding referenceLink. E.g. for pointing back to parent modules
     * page
     * @var string $referenceLink
     */
    public static $referenceLink = null;
    
    /**
     * var holding redirect reference when called object has performed
     * e.g. a correct submission
     * @var string $referenceRedirect
     */
    public static $referenceRedirect = null;
    
    /**
     * var holding reference options sent to called object
     * @var array $referenceOptions
     */
    public static $referenceOptions = null;
    
    /**
     * method for including a reference module
     * @param int $frag_reference_id
     * @param int $frag_id
     * @param string $frag_reference_name
     */
    public static function includeRefrenceModule (
            $frag_reference_id = 2, 
            
            // reserved. Will be set by the module in reference
            // e.g. will be set in files when used in content.
            
            $frag_id = 3,
            $frag_reference_name = 4) {    
        
        $reference = uri::$fragments[$frag_reference_name];  
        $id = uri::$fragments[$frag_id]; 
        $extra =  uri::getInstance()->fragment($frag_reference_name +1); 
        
        if (isset($extra) && !empty($extra)) {
            $reference.= "/$extra";
        }
        
        // normal this will not be set. 
        // because imagine this situation
        // $id = uri::$fragments[$frag_id];
        $reference_id = uri::$fragments[$frag_reference_id];

        if (!isset($reference)){
            return false;
        }
        
        $res = moduleloader::includeModule($reference);
        if ($res) {
            // transform a reference (e.g. contentArticle) into a class name
            // (contentArticle)
            $class = moduleloader::modulePathToClassName($reference);
            self::$reference = $reference;
            self::$id = $id;
            self::$referenceId = $reference_id;
            
            // we only need this if we just need a link to point to 'parent' module
            if (method_exists($class, 'getLinkFromId')) {            
                self::$referenceLink = $class::getLinkFromId(
                    moduleloader::$referenceId, moduleloader::$referenceOptions);
            }
            
            // we need this if we want to redirect if a submission was valid
            if (method_exists($class, 'getRedirect')) {
                self::$referenceRedirect = $class::getRedirect(
                    moduleloader::$referenceId, moduleloader::$referenceOptions);
                
                // check if url is a rewritten one
                self::$referenceRedirect = html::getUrl(self::$referenceRedirect);
            }
            return true;
        }
        return false;
    }
    
    /** 
     * return all set reference info as an array 
     * @return array $ary array 
     *                      (parent_id, inline_parent_id, reference, link, redirect)
     */
    public static function getReferenceInfo () {
        $ary = array ();
        $ary['parent_id'] = self::$referenceId;
        $ary['inline_parent_id'] = self::$id;
        $ary['reference'] = self::$reference;
        $ary['link'] = self::$referenceLink;
        $ary['redirect'] = self::$referenceRedirect;
        $ary['options'] = self::$referenceOptions;
        return $ary;
    }

    /**
     * return modules classname from a modules path.
     * e.g. account_profile will return accountProfile
     * e.g. content/article will return contentArticle
     * 
     * @param  string   $path (e.g. account/profile)
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
     * returns a path from a module class name.
     * e.g. content_article will return content/article
     * @param string $class
     * @return string $module_path 
     */
    public static function moduleClassToModelPath ($class) {
        return $module_path = str_replace('_', '/', $class);
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
     * method for loading a parsing module
     *
     * @return string the parsed modules html
     */
    public function loadModule(){
        
        $info = $this->info;       
        $controller = $this->info['controller'];

        // only allow installed modules
        if (!$this->isInstalledModule($this->info['module_base_name'])){
            self::$status[404] = 1;
            $this->setErrorModuleInfo(); 
        }

        $module_class = 'cosmod_' . $this->info['module_class'] . "_module";
        $method_exists = @method_exists($module_class, $controller);
        
        
        // We need is a controller
        if (!file_exists($this->info['controller_file']) && !$method_exists){
            self::$status[404] = 1;
            $this->setErrorModuleInfo();    
        }

        if ($method_exists) {
            $module_class::$controller();
            $str = ob_get_contents();
            ob_clean();
            return $str;
        } 
        
        
        if (!file_exists($this->info['controller_file']) ){    
            self::$status[404] = 1;
        }  else {
            include_once $this->info['controller_file'];
        }
        if (isset(self::$status[403])){
            $this->setErrorModuleInfo();
            $this->initModule();
            include_once $this->info['controller_file'];  
        }

        if (isset(self::$status[404])){
            $this->setErrorModuleInfo();
            $this->initModule();
            include_once $this->info['controller_file'];
        }

        $str = ob_get_contents();
        ob_clean();
        return $str;
    }
    
    /**
     * method for setting a modules ini settings.
     * @param string $module
     * @param string $type module or template
     * @return void
     */
    public static function setModuleIniSettings($module, $type = 'module'){

        static $set = array();     
        if (isset($set[$module])) {
            return;
        }
        
        if (!isset(config::$vars['coscms_main']['module'])){
            config::$vars['coscms_main']['module'] = array();
        }

        $set[$module] = $module;
        if ($type == 'module') {
            $ini_file = _COS_MOD_PATH . "/$module/$module.ini";
        } else {
            $ini_file = _COS_HTDOCS . "/templates/$module/$module.ini";
        }
        
        if (!file_exists($ini_file)) {
            return;
        }
        
       
        $module_ini = config::getIniFileArray($ini_file, true);
        if (is_array($module_ini)){
            config::$vars['coscms_main']['module'] = array_merge(
                config::$vars['coscms_main']['module'],
                $module_ini
            );
        }

        // check if development settings exists.
        if (isset($module_ini['development'])){
            if ( config::getMainIni('server_name') == @$_SERVER['SERVER_NAME']){
                config::$vars['coscms_main']['module'] =
                    array_merge(
                        config::$vars['coscms_main']['module'],
                        $module_ini['development']
                    );
            }
        }
        
        // check if stage settings exists.
        if (isset(self::$iniSettings[$module]['stage'])){
            if ( config::getMainIni('server_name') == @$_SERVER['SERVER_NAME'] ){
                config::$vars['coscms_main']['module'] =
                    array_merge(
                        config::$vars['coscms_main']['module'],
                        self::$iniSettings[$module]['stage']
                    );
            }
        }
    }
    
    public static function setModuleClassSettings ($module) {
        static $set = array();     
        if (isset($set[$module])) {
            return;
        }

        $set[$module] = $module;

        $config_file = _COS_MOD_PATH . "/$module/config.php";
        if (!file_exists($config_file)) {
            return;
        }
        
        include_once $config_file;
        $class = $module . "Config";
        $obj = new $class;
        return $obj->getConfig();      
    }
    

    /**
     * method for getting modules pre content. pre content is content shown
     * before the real content of a page. E.g. admin options if any. 
     * 
     * @param array $modules the modules which we want to get pre content from
     * @param array $options spseciel options to be send to the sub module
     * @return string   the parsed modules pre content as a string
     */
    public static function subModuleGetPreContent ($modules, $options) {
        $str = '';
        $ary = array();
        if (!is_array($modules)) return array ();
        foreach ($modules as $val){
            $str = '';
            if (method_exists($val, 'subModulePreContent') && moduleloader::isInstalledModule($val)){
                $str = $val::subModulePreContent($options);
                if (!empty($str)) {
                    $ary[] = $str;
                }
            }
        }
        
        return $ary;
        //return self::parsePreContent($ary);
    }
    
    /**
     * method for getting modules pre content. pre content is content shown
     * before the real content of a page. E.g. admin options if any. 
     * 
     * @param array $modules the modules which we want to get pre content from
     * @param array $options spseciel options to be send to the sub module
     * @return string   the parsed modules pre content as a string
     */
    public static function subModuleGetAdminOptions ($modules, $options) {
        $str = '';
        $ary = array();
        
        if (!is_array($modules)) return array ();
        foreach ($modules as $val){
            if (method_exists($val, 'subModuleAdminOption') && moduleloader::isInstalledModule($val)){
                $str = $val::subModuleAdminOption($options);
                if (!empty($str)) $ary[] = $str;
            }
        }
        return $ary;
    }
    
    /**
     * method for building a reference url
     * @param string $base
     * @param array $params
     * @return string $url 
     */
    public static function buildReferenceURL ($base, $params) {
        if (isset($params['id'])) {
            $extra = $params['id'];
        } else {
            $extra = 0;
        }
        
        $url = $base . "/$params[parent_id]/$extra/$params[reference]";
        return $url;
    }
    
    /**
     * method for parsing the admin options. As there can be more modules
     * we iritate over an array of sub modules and return the admin menu
     * as a string. 
     * 
     * @param array $ary the array of strings
     * @return string $str string
     */
    public static function parseAdminOptions ($ary = array()){
        $num = count($ary);
        $str = "<div id =\"content_menu\">\n";
        $str.= "<ul>\n";
        foreach ($ary as $val){
            $num--;
            if ($num) {
                $str.= "<li>" . $val . MENU_SUB_SEPARATOR .  "</li>\n";
            } else {
                $str.= "<li>" . $val . "</li>\n";
            }
        }
        $str.= "</ul>\n";
        $str.= "</div>\n";
        return $str;
    }

    /**
     * method for setting inline content
     * @param array $modules
     * @param array $options
     * @return string 
     */
    public static function subModuleGetInlineContent ($modules, $options){
        $ary = array ();
        if (!is_array($modules)) return $ary;
        foreach ($modules as $val){
            if (method_exists($val, 'subModuleInlineContent') && moduleloader::isInstalledModule($val)){
                $str = $val::subModuleInlineContent($options);
                if (!empty($str)) $ary[] = $str;
            }
        }
        return $ary;
    }

    /**
     * method for getting post content of some modules
     * @param type $modules
     * @param type $options
     * @return string the post content as a string. 
     */
    public static function subModuleGetPostContent ($modules, $options){

        $ary = array ();
        if (!is_array($modules)) return $ary;
        foreach ($modules as $val){
            if (method_exists($val, 'subModulePostContent') && moduleloader::isInstalledModule($val)){
                $str = $val::subModulePostContent($options);
                if (!empty($str)) $ary[] = $str;
            }
        }
        return $ary;
        
    }

    /**
     *method for including modules
     * @param array $modules
     * @return false|void   false if no modules where given.  
     */
    public static function includeModules ($modules) {
        if (!is_array($modules)) return false;
        foreach ($modules as $val) {
            moduleloader::includeModule ($val);
        }
    }
    
    /**
     * include a module from a static call e.g. account::create will include 
     * module account
     * @param string $call the static call
     */
    public static function includeModuleFromStaticCall ($call){
        $call = explode ('::', $call);
        $module = $call[0];
        return moduleloader::includeModule($module);
    }
     
    /**
     * include a module
     * @param string $module
     */
    public static function includeModule ($module) {
        static $modules = array ();
        if (isset($modules[$module])){
            // module has been included
            return true;
        }

        $module_path = _COS_MOD_PATH . '/' . $module;
        $ary = explode('/', $module);

        $load = array_pop($ary);
        $model_file = $module_path . '/' . "model.$load.inc";  
        $view_file = $module_path . '/' . "view.$load.inc";
        $ary = explode('/', $module);

        $base = $ary[0];
        
        // lang and ini only exists in base module
        lang::loadModuleLanguage($base);
        moduleloader::setModuleIniSettings($base);
        moduleloader::setModuleClassSettings($base);
        
        if (file_exists($view_file)){
            include_once $view_file;
        }
        
        if (file_exists($model_file)){  
            include_once $model_file;
            $modules[$module] = true;
            
            
            
            return true;
        } 
        
        return false;
        
        
    }
    
    /**
     * include template common.inc
     * @param string $template
     */
    public static function includeTemplateCommon ($template) {
        static $included = array ();
        if (!isset($included[$template])) {
            include_once _COS_HTDOCS . "/templates/$template/common.inc";
        }
        $included[$template] = true;
    }
    
    /**
     * include a model file
     * @param string $model e.g. accuount/create
     */
    public static function includeModel ($model) {
        $module_path = _COS_MOD_PATH  . '/' . $model;
        $ary = explode('/', $model);
        $last = array_pop($ary);
        $model_file = $module_path . '/' . "model.$last.inc";
        if (file_exists($model_file)) {
            include_once $model_file;
        }
    }
    
    /**
     * method for including a controller
     * @param string $controller
     */
    public static function includeController ($controller) {
        $module_path = config::$vars['coscms_base']  . '/' . _COS_MOD_DIR . '/' . $controller;
        $controller_file = $module_path . '.php';
        include_once $controller_file;
    }
    
    /**
     * inits a filter. Loads it and set ini settings.
     * @param type $filter
     */
    public static function initFilter ($filter) {
        // check for filter in coslib first. 
        if (!$filter) return;
        
        $file_path = _COS_PATH . "/coslib/filters/$filter.php";
        if (file_exists($file_path)) {             
            include_once "coslib/filters/$filter.php";
            return;
        }
            

        $class_path = _COS_MOD_PATH . "/filter_$filter/$filter.inc";
        include_once $class_path;
        moduleloader::setModuleIniSettings("filter_$filter");
    }
    
    /**
     * method for including filters
     * @param array|string $filters
     */
    public static function includeFilters ($filter) {
        static $loaded = array();

        if (!is_array($filter)){
            self::initFilter($filter);
            $loaded[$filter] = true;
        }

        if (is_array ($filter)){
            foreach($filter as  $val){
                if (isset($loaded[$val])) continue;
                self::initFilter($val);
                $loaded[$val] = true;
            }
        }
    }
    
    /**
     * getting filter help from filters
     * @param array $filters
     * @return string $filters_help
     */
    public static function getFiltersHelp ($filters) {
        if (empty($filters)) return '';
        
        moduleloader::includeFilters($filters);
        $str = '<span class="small-font">';
        $i = 1;

        foreach($filters as $val) {
            $str.= $i . ") " .  lang::translate("filter_" . $val . "_help") . "<br />";
            $i++;
        }
        
        $str.='</span>';
        return $str;
    }
    
    /**
     * get filtered content
     * @param array $filters
     * @param string $content
     * @return string $content
     */
    public static function getFilteredContent ($filter, $content) {
        if (!$filter) return $content;
        
        if (!is_array($filter)){

            moduleloader::includeFilters($filter);
            $class = $filter;
            $filter_class = new $class;

            if (is_array($content)){
                foreach ($content as $key => $val){
                    $content[$key] = $filter_class->filter($val);
                }
            } else {
                $content = $filter_class->filter($content);
            }

            return $content;
        }

        if (!empty ($filter)){

            foreach($filter as $key => $val){
                moduleloader::includeFilters($val);
                $class = $val; 
                $filter_class = new $class;
                if (is_array($content)){
                    foreach ($content as $key => $val){
                        $content[$key] = $filter_class->filter($val);
                    }
                } else {
                    $content = $filter_class->filter($content);
                }
            }
            return $content;
        }
    return '';;
    }
}


/**
 * @ignore
 * @param type $template
 */
function include_template_inc ($template){
    moduleloader::includeTemplateCommon($template);
}

/**
 * includes a module
 * @ignore
 * @param string $module
 * @return boolean $res true on success and false on failure
 */
function include_module($module){
    return moduleloader::includeModule($module);
}

/**
 * function for including the model file only
 * @ignore
 * @param   string   $module the module where the model file exists 
 *                   e.g. (content/article)
 */
function include_model($model){
    moduleloader::includeModel($model);
}



/**
 * function for including a controller
 * @ignore
 * @param string    $controller the controller to include (e.g. content/article/add)
 */
function include_controller($controller){
    moduleloader::includeController($controller);
}

/**
 * function for including a filter module
 * @ignore
 * @param   array|string   $filter string or array of string with 
 *                         filters to include
 *
 */
function include_filters ($filter){
    moduleloader::includeFilters($filter);
}

/**
 * function for getting filters help string
 * @ignore
 * @param string|array $filters the filter or filters from were we wnat to get
 *                     help strings
 * @return string $string the help strings of all filters. 
 */
function get_filters_help ($filters) {
    moduleloader::getFiltersHelp($filters);
    
}

/**
 * function for getting filtering content
 * @ignore
 * @param  string|array    $filter the string or array of filters to use
 * @param  string|array    $content the string or array (to use filters on)
 * @return string|array    $content the filtered string or array of strings
 */
function get_filtered_content ($filter, $content){   
    return moduleloader::getFilteredContent($filter, $content);
}
