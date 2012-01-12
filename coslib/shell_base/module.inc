<?php

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

    $str = "Proceeding with install of module '$options[Module]'\n";
    $install = new moduleInstaller();
    $install->setInstallInfo($options);
    $ret = $install->install();
    if (!$ret) {
        $str.=$install->error;
    } else {
        $str.=$install->confirm;
    }
    
    if ($return_output) {
        return $str;
    } else { 
        cos_cli_print($str);
    }
}

/**
 * function for upgrading all modules
 */
function upgrade_all(){
    $upgrade = new moduleInstaller();
    //$upgrade->upgradeAll();
    $modules = $upgrade->getModules();

    foreach($modules as $key => $val){
        // testing if this is working
        $options = array ('Module' => $val['module_name']);
        $upgrade = new moduleInstaller($options);
        $upgrade->upgrade();

        update_ini_file($options);
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
    $uninstall = new moduleInstaller($options);
    $ret = $uninstall->uninstall();
    if (!$ret) {
        cos_cli_print($uninstall->error);
    } else {
        cos_cli_print($uninstall->confirm);
    }
}

/**
 * function for purging a module (compleate removal)
 *
 * @param   array  options
 */
function purge_module($options){
    // check if module is set
    if ( strlen($options['Module']) == 0 ){
        cos_cli_print("No such module: $options[Module]");
        cos_cli_abort();
    }

    // check if module exists
    $module_path = _COS_PATH . '/modules/' . $options['Module'];
    if (!file_exists($module_path)){
        cos_cli_print("Module already purged: No such module path: $module_path");
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
    // check if module exists in modules dir
    $module_path = _COS_PATH . '/modules/' . $options['Module'];
    if (!file_exists($module_path)){
        cos_cli_print("Module $options[Module] does not exists in modules dir. Try to install first.");
        cos_cli_print("This means that module exists in modules table. Try uninstall");
        return;
    }

    // module exists, compare with remote server
    $upgrade = new moduleInstaller($options);
    $current_version = $upgrade->installInfo['VERSION'];
    
    // upgrade
    $ret = $upgrade->upgrade($options['Version']);
    if (!$ret) {
        print $upgrade->error . "\n";
    } else {
        print $upgrade->confirm . "\n";
    }
    
    update_ini_file($options);
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
    $ini_file_path = _COS_PATH . "/modules/$options[Module]/$options[Module].ini";
    $ini_dist_path = $ini_file_path . "-dist";

    $ini_file = config::getIniFileArray($ini_file_path);
    $ini_dist = config::getIniFileArray($ini_dist_path);

    $new_settings = array ();
    foreach ($ini_dist as $key => $val){
        if (!isset($ini_file[$key])){
            $ini_file[$key] = $val;
            
            // used for displaying which settings were updated.
            $new_settings[$key] = $val;
        }
    }

    // write it to ini file
    $content = array_to_ini_file($ini_file);

    file_put_contents($ini_file_path, $content);

    // install profile.
    if (empty($new_settings)){
        //cos_cli_print("No new ini file settings for module $options[Module]");
    } else {
        $new_settings_str = array_to_ini_file($new_settings);
        cos_cli_print("New ini file written with updated settings: $ini_file_path");
        cos_cli_print("These are the new ini settings for module $options[Module]:");
        cos_cli_print(trim($new_settings_str));
    }
}


mainCli::setCommand('module', array(
    'description' => 'Commands for installing and uninstalling modules (only locale)',
));

// create commandline parser
mainCli::setOption('install_module', array(
    'long_name'   => '--mod-in',
    'description' => 'Will install specified module',
    'action'      => 'StoreTrue'
));

mainCli::setOption('uninstall_module', array(
    'long_name'   => '--mod-down',
    'description' => 'Will uninstall specified module',
    'action'      => 'StoreTrue'
));

mainCli::setOption('upgrade_module', array(
    'long_name'   => '--mod-up',
    'description' => 'Will upgrade specified module to latest version',
    'action'      => 'StoreTrue'
));

mainCli::setOption('purge_module', array(
    'long_name'   => '--purge',
    'description' => 'Will purge (uninstall and remove files) specified module',
    'action'      => 'StoreTrue'
));


mainCli::setOption('upgrade_all', array(
    'long_name'   => '--all-up',
    'description' => 'Will check all repos for later versions, both template and modules. Then checkout and upgrade sql if any new tags were found',
    'action'      => 'StoreTrue',

));

mainCli::setOption('update_ini_file', array(
    'long_name'   => '--ini-up',
    'description' => 'Will add new ini settings from ini-dist to ini',
    'action'      => 'StoreTrue'
));

mainCli::setArgument(
    'Module',
    array('description'=> 'Specify the module to install or upgrade or package',
        'optional' => true,
));

mainCli::setArgument(
    'Version',
    array('description'=> 'Specify the version to upgrade or downgrade to',
        'optional' => true,
));