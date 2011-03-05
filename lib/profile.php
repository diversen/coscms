<?php

/**
 * *
 * File which contains class for installing modules
 * @package    coslib
 */

/**
 * @ignore
 */
include_once "moduleInstaller.php";

/**
 * class for installing a profile or creating one.
 *
 * install: install the profile
 * create: creates a profile based on current site
 *
 * @package    coslib
 */
class profile  {

    /**
     *
     * @var array holding errors
     */
    public $error = array();

    /**
     *
     * @var string holding confirm message
     */
    public $confirm = array();

    /**
     * @var array   holding modules for profile
     */
    public $profileModules = array();
    
    /**
     * @var string  holding profiles template as string
     */
    public $profileTemplate;

    /**
     * @var string  holding profiles template as string
     */
    public $profileTemplates;
    
    /**
     * @var boolean  if true we use home if false we don't.
     */
    public $profileUseHome;

    /**
     * we use a var for db
     *
     * @var object  holding db. 
     */
    public $db = null;

    public static $master = null;

    /**
     * constructor which will take the module to install, upgrade or delete
     * as param
     *
     * @param string name of module to do operations on
     */
    function __construct(){
        
    }

    function setMaster (){
        self::$master = 1;
    }

    /**
     * method for getting all installed modules
     * which we will base our profile on.
     *
     * @return array    assoc array of all modules
     */
    public static function getModules(){
        $db = new db();
        $db->connect();
        $modules = $db->selectAll('modules');

        include_once "moduleInstaller.php";
        foreach ($modules as $key => $val){
            $options['Module'] = $val['module_name'];
            $mi = new moduleInstaller($options);

            $modules[$key]['public_clone_url'] = $mi->installInfo['PUBLIC_CLONE_URL'];
            $modules[$key]['private_clone_url'] = $mi->installInfo['PRIVATE_CLONE_URL'];
            if (self::$master){
                $modules[$key]['module_version'] = 'master';
            }

        }
        return $modules;
    }

    /**
     * method for creating a profile
     */
    public function createProfile($profile){
        // create all files
        $this->createProfileFiles($profile);
        // create install script
        $this->createProfileScript($profile);
        // copy config.ini
        $this->createConfigIni($profile);
    }

    /**
     * method for recreating a profile
     * just means that we recreate all except config.ini
     */
    public function recreateProfile($profile){
        // create all files
        $this->createProfileFiles($profile);
        // create install script
        $this->createProfileScript($profile);
    }

    /**
     * method for getting all templates located in htdocs/template
     * used for settings current templates in profiles/profile/profile.inc file
     */
    public static function getAllTemplates (){
        $dir = _COS_PATH . "/htdocs/templates";
        $templates = get_file_list($dir, array('dir_only' => true));

        foreach ($templates as $key => $val){
            $install = $dir . "/$val/install.inc";
            if (file_exists($install)){
                include $install;
                $templates[$key] = array ();
                $templates[$key]['public_clone_url'] = $_INSTALL['PUBLIC_CLONE_URL'];
                $templates[$key]['private_clone_url'] = $_INSTALL['PRIVATE_CLONE_URL'];
                if (!self::$master){
                    $templates[$key]['module_version'] = "$_INSTALL[VERSION]";
                } else {
                    $templates[$key]['module_version'] = "master";
                }
                $templates[$key]['module_name'] = $val;
            } else {
                unset($templates[$key]);
            }

        }
        return $templates;
    }

    /**
     * method for creating a profile script
     */
    public function createProfileScript($profile){
        $modules = $this->getModules();
        $module_str = var_export($modules, true);
        
        $templates = $this->getAllTemplates();
        $template_str = var_export($templates, true);
        
        $profile_str = '<?php ' . "\n\n";
        $profile_str.= '$_PROFILE_MODULES = ' . $module_str . ";";
        $profile_str.= "\n\n";
        $profile_str.= '$_PROFILE_TEMPLATES = ' . $template_str . ";";
        $profile_str.= "\n\n";
        $profile_str.= '$_PROFILE_TEMPLATE = ' . "'" . $this->getProfileTemplate() . "'" . ';';
        $profile_str.= "\n\n";
        $profile_str.= '$_PROFILE_USE_HOME = ' . $this->getProfileUseHome() . ';';
        $profile_str.= "\n\n";
        $file = _COS_PATH . "/profiles/$profile/profile.inc";
        if (!file_put_contents($file, $profile_str)){
            print "Could not write to $file";
        }
    }

    /**
     * method getting a profiles template
     *
     * @return  string  name of profiles template extracted from database settings.
     */
    private function getProfileTemplate (){
        $db = new db();
        $db->connect();
        $row = $db->selectOne('settings', 'id', 1);
        return $row['template'];
    }

    /**
     * method for determine weather we use a home url or not in main menu
     * this just means do we have a link in main menu which says "home"
     *
     * return   int     1 on yes and 0 on no
     */
    private function getProfileUseHome (){
        $db = new db();
        $db->connect();
        $row = $db->selectOne('menus', 'url', '/');
        if (!empty($row)){
            return 1;
        } else {
            return 0;
        }
    }

    /**
     * method for setting a profiles template
     * @return  boolean   result from database update 
     *                    (true on success and false on failure)
     */
    public function setProfileTemplate ($template = null){
        $db = new db();
        $db->connect();
        if (isset($template)){
            $this->profileTemplate = $template;
        }

        $ini_file = _COS_PATH . "/htdocs/templates/$this->profileTemplate/$this->profileTemplate.ini";
        $ini_file_dist = $ini_file . "-dist";

        if (file_exists($ini_file_dist)){
            copy($ini_file_dist, $ini_file);
        }
        $values = array('template' => $this->profileTemplate);
        return $db->update('settings', $values, 1);        
    }

    /**
     * method for settinge weather we use a home url or not in main menu 
     */
    public function setProfileUseHome(){
        $db = new db();
        $db->connect();
        $sql = "INSERT INTO `menus` VALUES (1, 'home', '/', '', '', 0, 0);";
        if ($this->profileUseHome){
            $res = $db->rawQuery($sql);
        }
    }

    /**
     * method for creating a profiles configuration files
     *
     * @param   string  name of profile to be created
     */
    private function createProfileFiles($profile){
        $modules = $this->getModules();
        $profile_dir = _COS_PATH . "/profiles/$profile";
        if (mkdir($profile_dir)){
            $this->confirm[] = "Created dir $profile_dir"; 
        } else {
            // non fatal - we move on
            $this->error[] = "Could not create dir $profile_dir";
        }
        clearstatcache();
        foreach ($modules as $key => $val){
            //print_r($val);
            $module_ini_file = _COS_PATH . "/modules/$val[module_name]/$val[module_name].ini";
            $source = _COS_PATH . "/modules/$val[module_name]/$val[module_name].ini";
            $dest = $profile_dir . "/$val[module_name].ini-dist";
            if (copy($source, $dest)){
                $this->confirm[] = "Copy $module_ini_file to $profile_dir";
            } else {
                $this->error[] = "Could not copy $module_ini_file to $profile_dir";
            }

            // if php ini file exists copy that to.
            $source = _COS_PATH . "/modules/$val[module_name]/$val[module_name].php.ini";
            $dest = $profile_dir . "/$val[module_name].php.ini-dist";

            if (file_exists($source)){
                copy($source, $dest);
            }

        }

    }

    /**
     * method for creating a main config.ini file
     * @param   string   name of the profile
     */
    private function createConfigIni($profile){
        $profile_dir = _COS_PATH . "/profiles/$profile";
        $source = _COS_PATH . "/config/config.ini";
        $dest = $profile_dir . "/config.ini-dist";
        if (copy($source, $dest)){
            $this->confirm[] = "Copy $source to $dest";
        } else {
            $this->confirm[] = "Could not Copy $source to $dest";
        }
    }

    /**
     * method for setting info about profile
     *
     * @param string    profile name
     */
    public function setProfileInfo($profile){
        $profile_dir = _COS_PATH . "/profiles/$profile";
        include $profile_dir . "/profile.inc";
        $this->profileModules = $_PROFILE_MODULES;
        $this->profileTemplates = $_PROFILE_TEMPLATES;
        $this->profileTemplate = $_PROFILE_TEMPLATE;
        $this->profileUseHome = $_PROFILE_USE_HOME;
    }

    /**
     *  method for loading a profile
     * @param string     profile
     */
    public function loadProfile($profile){
        $this->setProfileInfo($profile);
        $this->loadProfileFiles($profile);
        $this->loadConfigIni($profile);
    }

    /**
     * method for reloading a profile. Reloads all ini file except config.ini
     * @param   string   profile
     */
    public function reloadProfile($profile){
        $this->setProfileInfo($profile);
        // install all ini files except config.ini
        $this->loadProfileFiles($profile);
    }
    
    /**
     * method for loading a profiles configuration files, which means all
     * modules configuration files.
     *
     * @param   string    name of profile to be installed
     */
    public function loadProfileFiles($profile){
        $profile_dir = _COS_PATH . "/profiles/$profile";
        foreach ($this->profileModules as $key => $val){
            $source = $profile_dir . "/$val[module_name].ini-dist";
            $dest = _COS_PATH . "/modules/$val[module_name]/$val[module_name].ini";
    
            if (copy($source, $dest)){
                $this->confirm[] = "Copy $source to $dest";
            } else {
                $this->error[] = "Could not copy $source to $dest";
            }

            // if php ini file exists copy that to.
            $dest = _COS_PATH . "/modules/$val[module_name]/$val[module_name].php.ini";
            $source = $profile_dir . "/$val[module_name].php.ini-dist";

            if (file_exists($source)){
                copy($source, $dest);
            }
        }    
    }

    /**
     * method for loading main configuration file for a profile
     *
     * @param string name of profile to be installed
     */
    public function loadConfigIni($profile){
        // copy config,ini
        $profile_dir = _COS_PATH . "/profiles/$profile";
        $dest = _COS_PATH . "/config/config.ini";
        $source = $profile_dir . "/config.ini-dist";
        if (copy($source, $dest)){
            $this->confirm[] = "Copy $source to $dest";
        } else {
            $this->error[] = "Could not Copy $source to $dest";
        }
    }
}
