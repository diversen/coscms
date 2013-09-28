<?php


/**
 * *
 * File which contains class for installing modules
 *
 * @package    moduleinstaller
 */

/**
 * used for add newline definiton, when doing web - or shell install of modules.
 */
include_once "coslib/shell/common.inc";

/**
 * class for installing a module or upgrading it.
 * base actions are:
 *
 * install: registers the module into the database
 *
 * update: checks module version in database and
 * perform any needed updates e.g. from version
 * 1.04 to 1.07 or 0.01 to 0.07
 * If more upgrades exist. Upgrade all one after one.
 *
 * remove: removes the module from the modules table
 * and also tables connected to module.
 * @package    moduleinstaller
 */
class moduleinstaller extends db {

    /**
     * holding array of info for the install
     * this is loaded from install.inc file and will read
     * the $_INSTALL var
     * @var array $installInfo 
     */
    public $installInfo = array();

    /**
     * holding error
     * @var string $error
     */
    public $error = null;
    
    /**
     * var holding notices
     * @var string 
     */
    public $notice = null;

    /**
     * holding confirm message
     * @var string $confirm
     */
    public $confirm;

    /**
     * constructor which will take the module to install, upgrade or delete
     * as param and set info about module to be installed, upgraded, etc.
     * if module is set
     *
     * @param   array $options
     */
    function __construct($options = null){
        
        $this->connect();
        
        if (isset($options)){
            return $this->setInstallInfo($options);
        }
    }

    /**
     * reads install info from modules/module_name/install.inc
     *
     * @param   array $options
     */
    public function setInstallInfo($options){
        
        // set module name
        // set module dir
        // set ini 
        // set ini dist
        
        $module_name = $options['module'];
        $module_dir = _COS_MOD_PATH . "/$module_name";
        $ini_file = $module_dir . "/$module_name.ini";
        $ini_file_dist = $module_dir . "/$module_name.ini-dist";

        // if profile we use profile's module ini-dist
        if (isset($options['profile'])){
            $ini_file_dist = _COS_PATH . "/profiles/$options[profile]/$module_name.ini-dist";
        }

        // check if module dir exists 
        if (file_exists($module_dir)){
            
            // check for an existing ini file
            // copy if we found if ini is found
            if (!file_exists($ini_file)){
                if (file_exists($ini_file_dist)){
                    copy ($ini_file_dist, $ini_file);
                    config::$vars['coscms_main']['module'] = config::getIniFileArray($ini_file);
                } 
            } else {
                config::$vars['coscms_main']['module'] = config::getIniFileArray($ini_file);
            }
            
            // load install.inc if exists
            $install_file = "$module_dir/install.inc";
            if (!file_exists($install_file)){
                $status = "Notice: No install file '$install_file' found in: '$module_dir'";
                cos_cli_print_status('NOTICE', 'y', $status);
            }
            
            // set a defaukt module_name - which is the module dir
            $this->installInfo['NAME'] = $module_name;
            
            // load install.inc if found
            if (file_exists($install_file)) {
                include $install_file;

                $this->installInfo = $_INSTALL;
                $this->installInfo['NAME'] = $module_name;
                
                // is menu item
                if (empty($this->installInfo['MAIN_MENU_ITEM'])){
                    $this->installInfo['menu_item'] = 0;
                } else {
                    $this->installInfo['menu_item'] = 1;
                }

                // run levels
                if (empty($this->installInfo['RUN_LEVEL'])){
                    $this->installInfo['RUN_LEVEL'] = 0;
                }
            } 
            
            // if no version we check if this is a git repo
            // as there is no version from a install.inc file
            // we always just use the latest tag
            if (!isset($this->installInfo['VERSION']) && defined('_COS_CLI')) {
                
                $command = "cd " . _COS_MOD_PATH . "/"; 
                $command.= $this->installInfo['NAME'] . " ";
                $command.= "&& git config --get remote.origin.url";

                $git_url = shell_exec($command);
                $tags = git_get_remote_tags($git_url);

                if (empty($tags)) {
                    $latest = 'master';
                } else {
                    $latest = array_pop($tags);
                }

                $this->installInfo['VERSION'] = $latest;
            }
        } else {
            $status = "No module dir: $module_dir";
            cos_cli_print_status('NOTICE', 'y', $status);
            return false;
        }
    }
    
    /**
     * checks if module source exists
     * @param string $module
     * @return boolean $res true if exists else false
     */
    public function sourceExists ($module) {
        $module_path = _COS_MOD_PATH . "/$module";
        if (file_exists($module_path)) {
            return true;
        }
        return false;
    }
    /**
     * method for checking if a module is installed or not
     * checking is just done by looking into the modules table of database
     * @param string $module 
     * @return  boolean true or false
     */
    public function isInstalled($module = null){
        // test if a module with $this->installInfo['MODULE_NAME']
        // already is installed.
        if (!isset($module)){
            $module = $this->installInfo['NAME'];
        }

        $row = $this->selectOne('modules', 'module_name', $module);

        if (!empty($row)){    
            return true;
        }
        return false;
    }

    /**
     * method for getting module info. Info is read from database
     *
     * @return array|false  false or array
     *                      if module we search for exists, we return the
     *                      install row else we return false
     */
    public function getModuleInfo(){
        // test if a module with $this->installInfo['MODULE_NAME']
        // already is installed.
        try {
            $row = $this->selectOne('modules', 'module_name', $this->installInfo['NAME'] );
        } catch (PDOException $e) {
            $this->fatalError($e->getMessage());
        }
        if (!empty($row)){
            return $row;
        }
        return false;
    }

    /**
     * get array of all modules
     * @return array  $modules assoc array of all modules
     */
    public static function getModules(){
        $db = new db();
        $db->connect();
        $modules = $db->selectAll('modules');
        return $modules;
    }

    /**
     * method to reload all languages
     * gets all modules
     * reloads language files one after another.
     */
    public function reloadLanguages(){        
        $modules = $this->getModules();
        foreach ($modules as $key => $val){
            $this->installInfo['NAME'] = $val['module_name'];
            $this->insertLanguage($val['module_name']);
        }
    }
    
    /**
     * method to reload all languages for system
     * These language files are placed in lang/
     * reloads language files one after another.
     */
    public function reloadCosLanguages(){        
        $modules = file::getFileList(_COS_PATH . "/lang/", array ('dir_only' => true));
        foreach ($modules as $val){
            $this->insertLanguage($val);
        }
    }
    
    /**
     * reloads config for all modules
     */
    public function reloadConfig () {
        $modules = $this->getModules();
        foreach ($modules as $val){
            $this->setInstallInfo($options = array ('module' => $val['module_name']));
            if (isset($this->installInfo['IS_SHELL']) && $this->installInfo['IS_SHELL'] == '1') {
                $this->update(
                        'modules', 
                        array('is_shell' => 1), 
                        array ('module_name' => $val['module_name'])
                        );
            } else {
                $this->update(
                        'modules', 
                        array('is_shell' => 1), 
                        array ('module_name' => NULL)
                        );
            }
            $this->insertRoutes();
        }
    }


    /**
     * method for upgrading all modules.
     */
    public function upgradeAll(){
        $modules = $this->getModules();
        foreach($modules as $val){
            // testing if this is working
            $upgrade = new moduleinstaller($val['module_name']);
            $upgrade->upgrade();
        }
    }
    
    /**
     * method for deleting routes for a module
     */
    public function deleteRoutes () {
        $this->delete('system_route', 'module_name', $this->installInfo['NAME']);
    }
    
    /**
     * method for inserting routes for modules
     */
    public function insertRoutes () {
        
        if (isset($this->installInfo['ROUTES'])) {
            $this->delete('system_route', 'module_name', $this->installInfo['NAME']);
            $routes = $this->installInfo['ROUTES'];
            foreach ($routes as $val) {
                foreach ($val as $route => $value) {
                    $insert = array (
                        'module_name' => $this->installInfo['NAME'],
                        'route' => $route,
                        'value' => serialize($value));
                    
                    $this->insert('system_route', $insert);
                }
            }  
        }
    }

    /**
     * method for inserting a language into the system language table
     * system language is messages which is needed outside of the module scope,
     * e.g. menu items.
     * @param string $module
     * @return  boolean $res false if no language file exists. Else return true.
     */
    public function insertLanguage($module = null){
        if (!$module) {
            $module = $this->installInfo['NAME'];
        }
        
        //$language_path = _COS_PATH . "/lang/$module/lang";
        //if (file_exists($language_path) ) {
            // system language
            // e.g. all filters
            // cosmarkdown, cosmedia
         
        //} else {
            // module language
            $language_path =
                _COS_PATH .
                '/' . _COS_MOD_DIR . '/' .
                $module .
                '/lang';
        //}
        
        $dirs = file::getFileList($language_path);
        if ($dirs == false){
            $this->notice = "Notice: No language dir in: $language_path " . NEW_LINE;
        }

        $this->delete('language', 'module_name', $module);
        if (is_array($dirs)){
            foreach($dirs as $val){
                $language_file = $language_path . "/$val" . '/system.inc';
                if (file_exists($language_file)){
                    include $language_file;
                    if (isset($_COS_LANG_MODULE)){         
                        $str = serialize($_COS_LANG_MODULE);
                        $values = array(
                            'module_name' => $module,
                            'language' => $val,
                            'translation' => $str);
                        $this->insert('language', $values);
                    }
                } else {
                    $this->notice = "Notice: " . $language_file . " not found" . NEW_LINE;
                }
            }
        }      
        return true;
    }

    /**
     * get single sql file name from module, version, and action
     * @param  string   $module
     * @param  float    $version
     * @param  string   $action (up or down)
     * @return string   sql filename
     */
    public function getSqlFileName($module, $version, $action){
        $sql_file = _COS_PATH . '/' . _COS_MOD_DIR . "/$module/mysql/$action/$version.sql";
        return $sql_file;
    }

    /**
     * get a sql file string from module, version, action
     * @param   string   $module
     * @param   float    $version
     * @param   string   $action (up or down)
     * @return  string   $sql
     */
    public function getSqlFileString($module, $version, $action){
        $sql_file = $this->getSqlFileName($module, $version, $action);
        if (file_exists($sql_file)){
            $sql = file_get_contents($sql_file);
        }
        return $sql;
    }

    /**
     * get a sql file list from module, action
     * @param   string   $module
     * @param   string   $action (up or down)
     * @return  array    array with file list
     */
    public function getSqlFileList($module, $action){
        $sql_dir = _COS_PATH . "/" . _COS_MOD_DIR . "/$module/mysql/$action";
        $file_list = file::getFileList($sql_dir);
        if (is_array($file_list)){
            return $file_list;
        } else {
            return array();
        }   
    }

    /**
     * get sql file list ordered by floats
     * @param   string   $module
     * @param   string   $action
     * @param   float    specific version
     * @param   float    current version
     * @return  array    array sorted according to version
     */
    public function getSqlFileListOrdered($module, $action, $specific = null, $current = null){
        $ary = $this->getSqlFileList($module, $action);
        asort($ary);
        if (isset($specific)){
            $ary = array_reverse($ary);
            if ($specific == 1 ) {
                foreach ($ary as $key => $val){
                    $val = substr($val, 0, -4);
                    if ($val > $current) {
                        unset($ary[$key]);
                    }
                }
                return $ary;
            } else {
                foreach ($ary as $key => $val){
                    $val = substr($val, 0, -4);
                    if ($val < $specific ){
                        unset($ary[$key]);
                    }
                    if ($val > $current) {
                        unset($ary[$key]);
                    }
                }
                return $ary;
            }
        }

        return $ary;
    }

    /**
     * method for inserting values into module registry
     * adds a row to module register in database
     *
     * @return  boolean true on success false on failure
     */
    public function insertRegistry (){
          
        if (!isset($this->installInfo['menu_item'])) {
            $this->installInfo['menu_item'] = 0;
        }
          
        if (!isset($this->installInfo['RUN_LEVEL'])) {
            $this->installInfo['RUN_LEVEL'] = 0;
        }

        $values = array (
            'module_version' => $this->installInfo['VERSION'],
            'module_name' => $this->installInfo['NAME'],
            'menu_item' => $this->installInfo['menu_item'],
            'run_level' => $this->installInfo['RUN_LEVEL']);
        
        if (isset($this->installInfo['LOAD_ON'])){
            $values['load_on'] = $this->installInfo['LOAD_ON'];
        }

        if (isset($this->installInfo['IS_SHELL'])){
            $values['is_shell'] = $this->installInfo['IS_SHELL'];
        }

        if (isset($this->installInfo['PARENT'])){
            $values['parent'] = $this->installInfo['PARENT'];
        }
        try {
            $this->insert('modules', $values);
        } catch (PDOException $e) {
            $this->fatalError($e->getMessage());
        }
        return true;
    }

    /**
     * method for creating the modules main menu items
     * create new row to module register
     *
     * @return  boolean true on success false on failure
     */
    public function insertMenuItem(){
        $res = null;

        lang::loadModuleSystemLanguage($this->installInfo['NAME']);

        if (!empty($this->installInfo['MAIN_MENU_ITEM'])){
            $values = $this->installInfo['MAIN_MENU_ITEM'];
            $values['title'] = $values['title'];
            $res = $this->insert('menus', $values);
        }

        if (!empty($this->installInfo['MAIN_MENU_ITEMS'])){
            foreach ($this->installInfo['MAIN_MENU_ITEMS'] as $val){
                $val['title'] = $val['title'];
                $res = $this->insert('menus', $val);
            }
        }

        if (!empty($this->installInfo['SUB_MENU_ITEM'])){
            $values = $this->installInfo['SUB_MENU_ITEM'];
            $values['title'] = $values['title'];
            $res = $this->insert('menus', $values);
        }
        return $res;
    }

    /**
     * method for deling modules menu item when uninstalling
     *
     * @param   string  modulename to uninstall
     * @return boolean true or throws an error on failure
     */
    public function deleteMenuItem($module = null){

        if (!isset($module)){
            $module = $this->installInfo['NAME'];
        }
        try {
            $result = $this->delete('menus', 'module_name', $module);
        } catch (PDOException $e) {
            $this->fatalError($e->getMessage());
        }
        return $result;
    }

    /**
     * method for updating version of module in module registry
     *
     * @param float $new_version the version to upgrade to
     * @param int $id the id of the module to be upgraded
     * @return boolean true or throws an error on failure
     */
    public function updateRegistry ($new_version, $id){
        $values = array (
            'module_version' => $new_version);

        try {
            $result = $this->update('modules', $values, $id);
        } catch (PDOException $e) {
            $this->fatalError($e->getMessage());
        }
        return true;
    }

    /**
     * method for delting a module from registry
     *
     * @return boolean true or throws an error on failure
     */
    public function deleteRegistry (){
        try {
            $result = $this->delete('modules', 'module_name', $this->installInfo['NAME']);
        } catch (PDOException $e) {
            $this->fatalError($e->getMessage());
        }
        return true;
    }
    
    /**
     * checks and creates ini file if not exists. 
     * @return boolean $res true on success and false on failure. 
     */
    public function createIniFile () {

        $module = $this->installInfo['NAME'];
        
        $module_path = _COS_PATH . '/' . _COS_MOD_DIR;
        
        $ini_file = "$module_path/$module/$module.ini";
        $ini_file_php = "$module_path/$module/$module.ini.php";
        $ini_file_dist = "$module_path/$module/$module.ini-dist";
        $ini_file_dist_php = "$module_path/$module/$module.ini.php-dist";

        if (!file_exists($ini_file)){
            if (!copy($ini_file_dist, $ini_file)){
                $this->error = "Error: Could not copy $ini_file to $ini_file_dist" . NEW_LINE;
                $this->error.= "Make sure your module has an ini-dist file: $ini_file_dist";
                return false;
            }
        }
        
        // create php ini file if a php.ini-dist file exists
        if (!file_exists($ini_file_php)){
            if (file_exists($ini_file_dist_php)){
                copy($ini_file_dist_php, $ini_file_php);
            }
        }

        return true;
        
    }

    /**
     * create SQL on module install
     * There is not much error checking, because we can not commit and
     * rollback on table creation (at leat on mysql which we most likely 
     * are using. 
     */
    public function createSQL () {
        
        
        $updates = $this->getSqlFileListOrdered($this->installInfo['NAME'], 'up');

        // perform sql upgrade. We upgrade only to the version nmber
        // set in module file install.inc. 
        if (!empty($updates)){            
            foreach ($updates as $key => $val){
                $version = substr($val, 0, -4);
                if ($this->installInfo['VERSION'] >= $version ) {
                    $sql =  $this->getSqlFileString(
                                $this->installInfo['NAME'],
                                $version,
                                'up');
                    
                    // all sql statements are executed one after one. 
                    // in your sql file any statement is seperated with \n\n
                    $sql_ary = explode ("\n\n", $sql);
                    
                    foreach ($sql_ary as $sql_key => $sql_val){
                        $result = self::$dbh->exec($sql_val);
                        if ($result === false) {
                            die("error");
                        }
                    }
                }
            }
        }
        return true;
    }
    
    /**
     * method for installing a module.
     * checks if module already is installed, if not we install it.
     *
     * @return  boolean $res true on success or false on failure
     */
    public function install (){

        $ret = $this->isInstalled();
        if ($ret){
            $info = $this->getModuleInfo();
            $this->error = "Error: module '" . $this->installInfo['NAME'] . "' version '$info[module_version]'";
            $this->error.= " already exists in module registry!";
            return false;
        }

        $res = $this->createIniFile ();
        if (!$res) {
            $this->confirm.= "module '" . $this->installInfo['NAME'] . "' does not have an ini file";        
        }

        $res = $this->createSQL () ;
        if (!$res) return false;
        
        if (isset($this->installInfo['VERSIONS'])) {
            // run anonymous functions for each version
            foreach ($this->installInfo['VERSIONS'] as $val) {          
                $this->installInfo['INSTALL']($val);
                if ($val == $this->installInfo['VERSION']) { 
                    break;
                }
            }
        } 
        
      
        
        
        // insert into registry. Set menu item and insert language.
        $this->insertRegistry();
        
        
        
        $this->insertLanguage();
        $this->insertMenuItem();
        $this->insertRoutes();

        
        
        $this->confirm = "module '" . $this->installInfo['NAME'] . "' ";
        $this->confirm.= "version '"  . $this->installInfo['VERSION'] . "' ";
        $this->confirm.= "installed";
        return true;  
    }


    /**
     * method for uninstalling a module
     *
     * @return boolean   true on success or false on failure
     */
    public function uninstall(){
        // checks if module exists in registry
        $specific = 0;

        if (!$this->isInstalled()){
            
            $this->error = "module '" . $this->installInfo['NAME'];
            $this->error .= "' does not exists in module registry!";
            return false;
        }

        $row = $this->getModuleInfo();
        $current_version = $row['module_version'];
        $downgrades = $this->getSqlFileListOrdered(
                $this->installInfo['NAME'], 'down',
                1, $current_version);

        $this->deleteRegistry();
        $this->deleteMenuItem();
        $this->deleteRoutes();
        $this->delete('language', 'module_name', $this->installInfo['NAME']);
        
        if(isset($this->installInfo['UNINSTALL'])) {
            $this->installInfo['UNINSTALL']($this->installInfo['VERSION']);
        }
        

        if (!empty($downgrades)) {
            foreach ($downgrades as $key => $val){
                $version = substr($val, 0, -4);
                if ($version <= $specific) continue;
                    $sql =  $this->getSqlFileString(
                    $this->installInfo['NAME'],
                    $version,
                        'down');
                    if (isset($sql)) {
                        $sql_ary = explode ("\n\n", $sql);
                        foreach ($sql_ary as $sql_key => $sql_val){
                            $result = self::$dbh->query($sql_val);
                        }
                        
                    $commit = true;
                }
            }
        }
        
        $commit = 1;

        // set a confirm message
        if (isset($commit)){
            $this->confirm = "module '" . $this->installInfo['NAME'] . "' ";
            $this->confirm.= "version '" . $this->installInfo['VERSION'] . "' ";
            $this->confirm.= "uninstalled";
            return true;
        } else {
            return false;
        }
    }


    /**
     * method for upgrading a module
     *
     * @param   float   if $specific_version isset, then only upgrade to this version
     * @return  int     0 if no upgrade $i >= 1 if upgrades were made
     *
     *
     *          current version is fetched from database
     *          new version is found in list of mysql/up files
     *
     *          if (current version is less than any database version
     *          it means we upgrade. We also upgrade modules table in database
     *          so we know at which version we are.
     *
     *          Therefore: Current version is the version found in the database.
     *          If we have downloadéd a more recent version of module then specified
     *          in modules table we will examine list of up files. And upgrade to
     *          latest.
     *
     *          Current version should be backed up when downloaded.
     *          This will always happen if you use  the following script
     *          scripts/commands/module.php
     * 
     *          Configuration if we have downloaded a new version and unpacked
     *          it there may be new configuration. Therefore remember to look 
     *          at new config.ini-dist
     * 
     *          You will see if there is any new configuration which needs 
     *          to be set.
     *
     *
     */
    public function upgrade ($specific = null){
        
        if (!moduleloader::isInstalledModule($this->installInfo['NAME'])) {
            cos_cli_print("Notice: Can not upgrade. You will need to install module first");
            return;
        }
        
        if (!isset($specific)){
            $specific = $this->installInfo['VERSION'] ;
        }
        
        // get current module version from registry
        $row = $this->getModuleInfo();
        $current_version = $row['module_version'];

        
        if ($current_version == $specific) {
            $this->confirm = "Module '" . $this->installInfo['NAME'] ."' version is $specific and registry has same version. No upgrade to perform";
            return true;
        }
        
          // echo ;
        
        // get a list of sql updates to perform or an empty array if no sql
        // updates exists
        $updates = $this->getSqlFileListOrdered($this->installInfo['NAME'], 'up');

        // perform sql upgrade
        if (!empty($updates)){
            foreach ($updates as $key => $val){
                $possible_versions = '';
                $version = substr($val, 0, -4);
                if ($version == $specific){
                    $version_exists = true;
                } else {
                    $possible_versions.="$version ";
                }
            }
            
            
            
            if (!isset($version_exists)){
                $this->error = 'module SQL ' . $this->installInfo['NAME'] . " ";
                $this->error.= 'does not have such a version. Possible version are: ';
                $this->error.= $possible_versions;
            }
            
            // perform SQL updates found in .sql files
            foreach ($updates as $key => $val){
                $version = substr($val, 0, -4);
                if ($current_version < $version ) {
                    $sql =  $this->getSqlFileString(
                                $this->installInfo['NAME'],
                                $version,
                                'up');
                    $sql_ary = explode ("\n\n", $sql);
                    foreach ($sql_ary as  $sql_val) {
                        if (empty($sql_val)) continue;
                        try {
                            self::$dbh->query($sql_val);
                        } catch (Exception $e) {
                            echo 'Caught exception: ',  $e->getMessage(), "\n";
                            echo "SQL = $sql_val\n";
                            echo "version of sql: $version";
                            die;
                        }
                    }
                }
            }   
        }

        // perform SQL upgrades - 2. method
        if (isset($this->installInfo['VERSIONS'])) {
            $perform_next = 0;
            foreach ($this->installInfo['VERSIONS'] as $val) {
                if ($perform_next) {
                    $this->installInfo['INSTALL']($val);
                    continue;
                }
                    
                if ($val == $current_version) { 
                    $perform_next = 1;
                    continue;
                }        
            }
        } 
       
        
        // update registry
        $this->updateRegistry($specific, $row['id']);
        if ( $specific > $current_version ){
            $this->confirm = "module: '" . $this->installInfo['NAME'] . "' ";
            $this->confirm.= "version: '" . $specific . " Installed. " . "' ";
            $this->confirm.= "Upgraded from $current_version";
            return true;
        } else {
            $this->confirm = "module: " . $this->installInfo['NAME'] . " Nothing to upgrade. module version is still $current_version";
            return true;
        }
    }
}

