<?php

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
 * @package    moduleinstaller
 */

class templateinstaller extends moduleinstaller {
    /**
     * holding array of info for the install
     * this is loaded from install.inc file and will read
     * the $_INSTALL var
     * @var array $installInfo
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
        
        $template_dir = _COS_HTDOCS  . "/templates/$template_name";
        $ini_file = $template_dir . "/$template_name.ini";
        $ini_file_dist = $template_dir . "/$template_name.ini-dist";

        if (isset($options['profile'])){
            $ini_file_dist = _COS_PATH . "/profiles/$options[profile]/$template_name.ini-dist";
        }

        if (!file_exists($ini_file)){
            if (file_exists($ini_file_dist)){
                copy ($ini_file_dist, $ini_file);
                config::$vars['coscms_main']['template'] = config::getIniFileArray($ini_file);
            } 
        } else {
            config::$vars['coscms_main']['template'] = config::getIniFileArray($ini_file);
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
    
    /**
     * installs a template
     * @return boolean $res 
     */
    public function install () {

        // create ini files for template
        $template = $this->installInfo['NAME'];
        $ini_file = _COS_HTDOCS . "/templates/$template/$template.ini";
        $ini_file_php = _COS_HTDOCS . "/templates/$template/$template.php.ini";
        $ini_file_dist = _COS_HTDOCS . "/templates/$template/$template.ini-dist";
        $ini_file_dist_php = _COS_HTDOCS . "/templates/$template/$template.php.ini-dist";

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