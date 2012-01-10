<?php


/**
 * *
 * File which contains class for installing modules
 *
 * @package    coslib
 */
if (!defined('_COS_CLI')){
    $new_line = "<br />";
} else {
    $new_line = "\n";
}

/**
 * define new line if not cli
 */
define('NEW_LINE', $new_line);
include_once "coslib/db.php";

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
 * @package    coslib
 */
class moduleInstaller extends db {

    /**
     *
     * @var array holding array of info for the install
     *            this is loaded from install.inc file and will read
     *            the $_INSTALL var
     */
    public $installInfo = null;

    /**
     *
     * @var array holding errors
     */
    public $error;

    /**
     *
     * @var string holding confirm message
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
            $this->setInstallInfo($options);
        }
    }

    /**
     * reads install info from modules/module_name/install.inc
     *
     * @param   array $options
     */
    public function setInstallInfo($options){

        $module_name = $options['Module'];
        $module_dir = _COS_PATH . "/modules/$module_name";
        $ini_file = $module_dir . "/$module_name.ini";
        $ini_file_dist = $module_dir . "/$module_name.ini-dist";

        if (isset($options['profile'])){
            $ini_file_dist = _COS_PATH . "/profiles/$options[profile]/$module_name.ini-dist";
        }

        if (!file_exists($ini_file)){
            if (file_exists($ini_file_dist)){
                copy ($ini_file_dist, $ini_file);
                clearstatcache();
                register::$vars['coscms_main']['module'] = parse_ini_file_ext($ini_file);
            } 
        } else {
            register::$vars['coscms_main']['module'] = parse_ini_file_ext($ini_file);
        }
        //moduleLoader::setModuleIniSettings($module_name);
        if (file_exists($module_dir)){
            $install_file = "$module_dir/install.inc";
            if (!file_exists($install_file)){
                cos_cli_print("Notice: No install file '$install_file' found in: '$module_dir'\n");
            }

            include $install_file;
            $this->installInfo = $_INSTALL;
            
            // use directory name as name of module
            $this->installInfo['NAME'] = $module_name;
            if (empty($this->installInfo['MAIN_MENU_ITEM'])){
                $this->installInfo['menu_item'] = 0;
            } else {
                $this->installInfo['menu_item'] = 1;
            }

            if (empty($this->installInfo['RUN_LEVEL'])){
                $this->installInfo['RUN_LEVEL'] = 0;
            }          
        } else {
            cos_cli_print ("Notice: No module dir: $module_dir\n");
        }
    }

    /**
     * method for checking if a module is installed or not
     * checking is just done by looking into the modules table of database
     *
     * @return  boolean true or false
     */
    public function isInstalled($module = null){
        // test if a module with $this->installInfo['MODULE_NAME']
        // already is installed.
        if (isset($this->installInfo)){
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
     *
     * @return array    assoc array of all modules
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
     * @return  array   assoc row with language installed
     */
    public function getLanguagesInstalled(){
        $query = 'select distinct language from language';
        $langs = $this->selectQuery($query);
    }

    /**
     * method for upgrading all modules.
     * Not used for now. 
     */
    public function upgradeAll(){
        $modules = $this->getModules();
        foreach($modules as $key => $val){
            // testing if this is working
            $upgrade = new moduleInstaller($val['module_name']);
            $upgrade->upgrade();
        }
    }

    /**
     * method for inserting a language into the system language table
     * system language is messages which is needed outside of the module scope,
     * e.g. menu items.
     *
     * @return  boolean false if no language file exists. Else return true.
     */
    public function insertLanguage($module = null){
        $language_path =
            _COS_PATH .
            '/modules/' .
            $this->installInfo['NAME'] .
            '/lang';

        $dirs = file::getFileList($language_path);
        if ($dirs == false){
            print "Notice: No language dir in: $language_path " . NEW_LINE;
        }

        $this->delete('language', 'module_name', $this->installInfo['NAME']);
        if (is_array($dirs)){
            foreach($dirs as $key => $val){
                $language_file = $language_path . "/$val" . '/system.inc';
                if (file_exists($language_file)){
                    include $language_file;
                    if (isset($_COS_LANG_MODULE)){         
                        $str = serialize($_COS_LANG_MODULE);
                        $values = array(
                            'module_name' => $this->installInfo['NAME'],
                            'language' => $val,
                            'translation' => $str);
                        $this->insert('language', $values);
                    }
                } else {
                    echo "Notice: " . $language_file . " not found" . NEW_LINE;
                }
            }
        }

        

        return true;
    }

    /**
     *
     * @param  string   $module
     * @param  float    $version
     * @param  string   $action (up or down)
     * @return string   sql filename
     */
    public function getSqlFileName($module, $version, $action){
        $sql_file = _COS_PATH . "/modules/$module/mysql/$action/$version.sql";
        return $sql_file;
    }

    /**
     *
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
     *
     * @param   string   $module
     * @param   string   $action (up or down)
     * @return  array    array with file list
     */
    public function getSqlFileList($module, $action){
        $sql_dir = _COS_PATH . "/modules/$module/mysql/$action";
        $file_list = file::getFileList($sql_dir);
        if (is_array($file_list)){
            return $file_list;
        } else {
            return array();
        }   
    }

    /**
     *
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
    private function insertRegistry (){
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
            $result = $this->insert('modules', $values);
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
            //$values['title'] = lang::translate($values['title']);
            $values['title'] = $values['title'];

            $res = $this->insert('menus', $values);
        }

        if (!empty($this->installInfo['MAIN_MENU_ITEMS'])){
            foreach ($this->installInfo['MAIN_MENU_ITEMS'] as $key => $val){
                $val['title'] = $val['title'];
                $res = $this->insert('menus', $val);
            }
        }

        if (!empty($this->installInfo['SUB_MENU_ITEM'])){
            $values = $this->installInfo['SUB_MENU_ITEM'];
            $values['title'] = $values['title'];
            //$values['parent'] = $this->installInfo['PARENT_MENU_ITEM'];
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
     * @param <float> $new_version the version to upgrade to
     * @param <int> $id the id of the module to be upgraded
     * @return boolean true or throws an error on failure
     */
    private function updateRegistry ($new_version, $id){
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
    private function deleteRegistry (){
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
        $ini_file = _COS_PATH . "/modules/$module/$module.ini";
        $ini_file_php = _COS_PATH . "/modules/$module/$module.php.ini";
        $ini_file_dist = _COS_PATH . "/modules/$module/$module.ini-dist";
        $ini_file_dist_php = _COS_PATH . "/modules/$module/$module.php.ini-dist";

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
     * rollback on table creation. 
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
     * @param   string  name of module to install
     * @return  boolean true on success or false on failure
     */
    public function install (){

        $ret = $this->isInstalled();
        if ($ret){
            $info = $this->getModuleInfo();
            $this->error = "Error: Module '" . $this->installInfo['NAME'] . "' version '$info[module_version]'";
            $this->error.= " already exists in module registry!";
            return false;
        }

        $res = $this->createIniFile ();
        if (!$res) return false;

        $res = $this->createSQL () ;
        if (!$res) return false;
        
        // insert into registry. Set menu item and insert language.
        $this->insertRegistry();
        $this->insertLanguage();
        $this->insertMenuItem();

        $this->confirm = "Module '" . $this->installInfo['NAME'] . "' ";
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
            $this->error = "Module '" . $this->installInfo['NAME'];
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
        $this->delete('language', 'module_name', $this->installInfo['NAME']);

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
            $this->confirm = "Module '" . $this->installInfo['NAME'] . "' ";
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
     *          NOTE that the update of registry is taking place before the
     *          actual upgrade of the modules sql. ERROR CODE:
     *
     *          Error!: SQLSTATE[HY000]: General error: 2014
     *          Cannot execute queries while other unbuffered queries are active.
     *          Consider using PDOStatement::fetchAll().
     *          Alternatively, if your code is only ever going to run against mysql,
     *          you may enable query buffering by setting the
     *          PDO::MYSQL_ATTR_USE_BUFFERED_QUERY attribute.
     *
     *          current version is fetched from database
     *          new version is found in list of mysql/up files
     *
     *          if (current version is less than any database version
     *          it means we upgrade. We also upgrade modules table in database
     *          so we know at which version we are.
     *
     *          Therefore: Current Version is the version found in the database.
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
     *          @see    scripts/commands/module.php
     *
     *
     */
    public function upgrade ($specific = null){
        if (!isset($specific)){
            $specific = $this->installInfo['VERSION'] ;
        }
        
        // get current module version from registry
        $row = $this->getModuleInfo();
        $current_version = $row['module_version'];

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
                $this->error = 'Module SQL ' . $this->installInfo['NAME'] . " ";
                $this->error.= 'does not have such a version. Possible version are: ';
                $this->error.= $possible_versions;
                //return false;
            }
            
            // perform SQL updates
            foreach ($updates as $key => $val){
                $version = substr($val, 0, -4);
                if ($current_version < $version ) {
                    //try {
                        $sql =  $this->getSqlFileString(
                                    $this->installInfo['NAME'],
                                    $version,
                                    'up');
                        $sql_ary = explode ("\n\n", $sql);
                        foreach ($sql_ary as $sql_key => $sql_val){
                            $result = self::$dbh->query($sql_val);
                        }
                    //} catch (PDOException $e) {
                      //  $this->fatalError($e->getMessage());
                    //}
                }
                // only upgrade to specified version
                if ($version == $specific) break;
            }
        }


        // update registry
        $this->updateRegistry($specific, $row['id']);
        if ( $specific > $current_version ){
            //self::$dbh->commit();
            $this->confirm = "Module: '" . $this->installInfo['NAME'] . "' ";
            $this->confirm.= "Version: '" . $specific . " Installed. " . "' ";
            $this->confirm.= "Upgraded from $current_version";
            return true;
        } else {
            $this->confirm = "Module: " . $this->installInfo['NAME'] . " Nothing to upgrade. Module version is still $current_version";
            return true;
        }
    }


}
/**
 * class for installing a templates or upgrading it.
 * base actions are:
 *
 *
 * update: checks module version from install.inc
 * perform any needed updates e.g. from version
 * 1.04 to 1.07
 *
 * If more upgrades exist. Upgrade all one after one.
 * 
 * @package    coslib
 */

class templateInstaller extends moduleInstaller {
    /**
     *
     * @var array holding array of info for the install
     *            this is loaded from install.inc file and will read
     *            the $_INSTALL var
     */
    public $installInfo = null;

    /**
     * constructor which will take the template to install, upgrade or delete
     * as param
     *
     * @param string name of template to do operations on
     */
    function __construct($options = null){
        if (isset($options)){
            $this->setInstallInfo($options);
        }
    }

/**
     * reads install info from modules/module_name/install.inc
     *
     * @param   array $options
     */
    public function setInstallInfo($options){
        if (isset($options['module_name'])) {
            $template_name = $options['module_name'];
        } else {
            $template_name = $options['template'];
        }
        
        $template_dir = _COS_PATH . "/htdocs/templates/$template_name";
        $ini_file = $template_dir . "/$template_name.ini";
        $ini_file_dist = $template_dir . "/$template_name.ini-dist";

        if (isset($options['profile'])){
            $ini_file_dist = _COS_PATH . "/profiles/$options[profile]/$template_name.ini-dist";
        }

        if (!file_exists($ini_file)){
            if (file_exists($ini_file_dist)){
                copy ($ini_file_dist, $ini_file);
                register::$vars['coscms_main']['template'] = parse_ini_file_ext($ini_file);
            } 
        } else {
            register::$vars['coscms_main']['template'] = parse_ini_file_ext($ini_file);
        }

        if (file_exists($template_dir)){
            $install_file = "$template_dir/install.inc";
            if (!file_exists($install_file)){
                cos_cli_print("Notice: No install file '$install_file' found in: '$template_dir'\n");
            }

            include $install_file;
            $this->installInfo = $_INSTALL;
            
            // use directory name as name of module
            $this->installInfo['NAME'] = $template_name;
            if (empty($this->installInfo['MAIN_MENU_ITEM'])){
                $this->installInfo['menu_item'] = 0;
            } else {
                $this->installInfo['menu_item'] = 1;
            }

            if (empty($this->installInfo['RUN_LEVEL'])){
                $this->installInfo['RUN_LEVEL'] = 0;
            }

            
            
        } else {
            cos_cli_print ("Notice: No module dir: $module_dir\n");
        }
    }
    
    public function install () {

        // create ini files for template
        $template = $this->installInfo['NAME'];
        $ini_file = _COS_PATH . "/htdocs/templates/$template/$template.ini";
        $ini_file_php = _COS_PATH . "/htdocs/templates/$template/$template.php.ini";
        $ini_file_dist = _COS_PATH . "/htdocs/templates/$template/$template.ini-dist";
        $ini_file_dist_php = _COS_PATH . "/htdocs/templates/$template/$template.php.ini-dist";

        if (!file_exists($ini_file)){
            if (file_exists($ini_file_dist)){
                if (!copy($ini_file_dist, $ini_file)){
                    $this->error = "Error: Could not copy $ini_file to $ini_file_dist" . NEW_LINE;
                    $this->error.= "Make sure your module has an ini-dist file: $ini_file_dist";
                    return false;
                }
            } 
        }
        
        // create php ini file if a php.ini-dist file exists
        if (!file_exists($ini_file_php)){
            if (file_exists($ini_file_dist_php)){
                copy($ini_file_dist_php, $ini_file_php);
            }
        }
        
        $this->confirm = "Template '" . $this->installInfo['NAME'] . "' installed" . NEW_LINE;
        $this->confirm.= "Make sure your module has an ini-dist file: $ini_file_dist";
                    
    }
}