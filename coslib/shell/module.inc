<?php

use diversen\moduleinstaller;
/**
 * File containing module functions for shell mode
 * (install, update, delete modules)
 *
 * @package     shell
 */

/**
 *
 * @param  string  module (directory with module) to be installed
 */
function install_module($options, $return_output = null){

    $in = new moduleinstaller();
    $proceed = $in->setInstallInfo($options);
    if ($proceed === false) {
        //cos_cli_print($in->error);
        return false;
    } else {
        $str = '';
        $ret = $in->install();
        if (!$ret) {
            $str.=$in->error;
        } else {
            $str.=$in->confirm;
        }

        if ($return_output) {
            return $str;
        } else { 
            cos_cli_print($str);
        }
    }
}

function install_module_silent ($options) {
    //$str = "Proceeding with install of module '$options[module]'\n";
    $install = new moduleinstaller();
    $install->setInstallInfo($options);
    return $install->install();
}

/**
 * function for upgrading all modules
 */
function upgrade_all(){
    $upgrade = new moduleinstaller();
    //$upgrade->upgradeAll();
    $modules = $upgrade->getModules();

    foreach($modules as $val){
        // testing if this is working
        $options = array ('module' => $val['module_name']);
        $upgrade = new moduleinstaller($options);
        $upgrade->upgrade();

        //update_ini_file($options);
        cos_cli_print($upgrade->confirm);
    }
}

/**
 * function for uninstalling a module
 * run 'down' sql files until all module sql is removed.
 *
 * @param   array  options
 */
function uninstall_module($options){
    $un = new moduleinstaller();
    $proceed = $un->setInstallInfo($options);
    
    if ($proceed === false) {
        cos_cli_print($un->error);
    } else {
        $ret = $un->uninstall();
        if ($ret) {
            cos_cli_print($un->confirm);
        } else {
            cos_cli_print($un->error);
        }
    }
}

/**
 * function for purging a module (compleate removal)
 *
 * @param   array  options
 */
function purge_module($options){
    // check if module is set
    if ( strlen($options['module']) == 0 ){
        cos_cli_print("No such module: $options[module]");
        cos_cli_abort();
    }

    // check if module exists
    $module_path = _COS_MOD_PATH . '/' . $options['module'];
    if (!file_exists($module_path)){
        cos_cli_print("module already purged: No such module path: $module_path");
        cos_cli_abort();
    }

    // it exists. Uninstall
    uninstall_module($options);

    // remove
    $command = "rm -rf $module_path";
    cos_exec($command);
}


/**
 * function for upgrading a module
 *
 * @param  array   options for the module to be upgraded
 */
function upgrade_module($options){
    

    // module exists.
    $upgrade = new moduleinstaller($options);
    $proceed = $upgrade->setInstallInfo($options);
    if ($proceed === false) {
        cos_cli_print("No such module '$options[module]' exists in modules dir.");
        cos_cli_print("This means that module exists in modules table. Try uninstall");
        return;
    }
    
    $ret = $upgrade->upgrade($options['version']);
    if (!$ret) {
        echo $upgrade->error . NEW_LINE;
    } else {
        echo $upgrade->confirm . NEW_LINE;
    }
}



/**
 * function for forcing confirm_readline to automagically answer 'Y' to all
 * questions raised by scripts
 */
function force_confirm_readline(){
    cos_confirm_readline(null, 1);
}

/**
 * function for updating a modules .ini file with new settings
 * from updated ini-dist file.
 *  
 * @param array     $options 
 */
function update_ini_file ($options){
    if (!isset($options['module'])) {
        echo "Specify module\n";
        exit(1);
    }
    
    $ini_file_path = _COS_MOD_PATH . "/$options[module]/$options[module].ini";
    $ini_dist_path = $ini_file_path . "-dist";

    $ini_file = conf::getIniFileArray($ini_file_path);
    $ini_dist = conf::getIniFileArray($ini_dist_path);

    $new_settings = array ();
    foreach ($ini_dist as $key => $val){
        if (!isset($ini_file[$key])){
            $ini_file[$key] = $val;
            
            // used for displaying which settings were updated.
            $new_settings[$key] = $val;
        }
    }

    // write it to ini file
    $content = conf::arrayToIniFile($ini_file);

    file_put_contents($ini_file_path, $content);

    // install profile.
    if (empty($new_settings)){
        //cos_cli_print("No new ini file settings for module $options[module]");
    } else {
        $new_settings_str = conf::arrayToIniFile($new_settings);
        cos_cli_print("New ini file written with updated settings: $ini_file_path");
        cos_cli_print("These are the new ini settings for module $options[module]:");
        cos_cli_print(trim($new_settings_str));
    }
}

function module_ini_all_up ($options = array ()) {

    $m = new moduleinstaller();
    $modules = $m->getModules();

    foreach($modules as $val){

        $options = array ('module' => $val['module_name']);
        update_ini_file($options);
    }

}

/**
 * will list lal modules in db table modules
 */
function module_list_all () {
    $ml = new moduleloader();
    $modules = $ml->getAllModules();
    print_r($modules);
}

if (conf::isCli()){
self::setCommand('module', array(
    'description' => 'Locale module management'
));

// create commandline parser
self::setOption('install_module', array(
    'long_name'   => '--mod-in',
    'description' => 'Will install specified module',
    'action'      => 'StoreTrue'
));

self::setOption('uninstall_module', array(
    'long_name'   => '--mod-down',
    'description' => 'Will uninstall specified module',
    'action'      => 'StoreTrue'
));

self::setOption('upgrade_module', array(
    'long_name'   => '--mod-up',
    'description' => 'Will upgrade specified module to latest version',
    'action'      => 'StoreTrue'
));

self::setOption('purge_module', array(
    'long_name'   => '--purge',
    'description' => 'Will purge (uninstall and remove files) specified module',
    'action'      => 'StoreTrue'
));


self::setOption('upgrade_all', array(
    'long_name'   => '--all-up',
    'description' => 'Will check all repos for later versions, both template and modules. Then checkout and upgrade sql if any new tags were found',
    'action'      => 'StoreTrue',

));

self::setOption('update_ini_file', array(
    'long_name'   => '--ini-up',
    'description' => 'Will update specified modules ini settings from ini-dist to ini',
    'action'      => 'StoreTrue'
));

self::setOption('module_ini_all_up', array(
    'long_name'   => '--ini-all-up',
    'description' => 'Will update all ini settings for installed modules',
    'action'      => 'StoreTrue'
));

self::setOption('module_list_all', array(
    'long_name'   => '--list',
    'description' => 'Will list all modules present in DB',
    'action'      => 'StoreTrue'
));

self::setArgument(
    'module',
    array('description'=> 'Specify the module to install or upgrade or package',
        'optional' => true,
));

self::setArgument(
    'version',
    array('description'=> 'Specify the version to upgrade or downgrade to',
        'optional' => true,
));
}