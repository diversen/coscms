<?php

/**
 * File containing profile functions for shell mode
 *
 * @package     shell
 */

/**
 * @ignore
 */
include_once "coslib/profile.php";
include_once "coslib/shell/module.inc";

/**
 * wrapper function for loading a profile
 */
function load_profile($options) {
    $pro = new profile();
    $profiles = file::getFileList('profiles', array('dir_only' => true));
    if (!in_array($options['profile'], $profiles)){
        cos_cli_abort('No such profile');
    }
    if (isset($options['config_only'])){
        $pro->loadConfigIni($options['profile']);
    } else {
        $pro->loadProfile($options['profile']);
    }
}

/**
 * 
 * @param type $options 
 */
function upgrade_from_profile ($options){
    // use profile object
    $pro = new profile();
    $pro->setProfileInfo($options['profile']);

    // install modules
    foreach ($pro->profileModules as $key => $val){
             
        
        
        $val['repo'] = $val['public_clone_url'];
        $val['version'] = $val['module_version'];
        if (isset(config::$vars['profile_use_master'])) {
            $val['version'] = 'master';
            //$val['module_version'] = 'master';
        }
               
        $val['module'] = $val['module_name'];
        
        
        $module = new moduleinstaller();
        $module->setInstallInfo($val);

        if ($module->isInstalled($val['module_name'])){
            cos_git_upgrade($val, $val['version'], 'module');
        } else {
            cos_git_install($val, 'module');
        }
    }

    // install templates
    foreach ($pro->profileTemplates as $key => $val){

        $val['repo'] = $val['public_clone_url'];
        $val['version'] = $val['module_version'];

        if (isset(config::$vars['profile_use_master'])) {
            $val['version'] = 'master';
        }

        // no db operations. Just clone version.
        cos_git_install($val, 'template');
    }
}


/**
 * 
 * @param type $options 
 */
function upgrade_from_profile_web ($options){
    // use profile object
    $pro = new profile();
    $pro->setProfileInfo($options['profile']);

    // install modules
    foreach ($pro->profileModules as $key => $val){

        $val['version'] = $val['module_version'];
        $val['module'] = $val['module_name'];
        
        $module = new moduleinstaller();
        $res = $module->setInstallInfo($val);
        //if (!$res) continue;

        if ($module->isInstalled($val['module_name'])){
            upgrade_module($val);
        } else {
            install_module($val);
        }
    }

    // install templates
    foreach ($pro->profileTemplates as $key => $val){
        // no need to do anything
        // we use web install and sources 
        // are downloaded.
        
    }
}


function install_from_profile ($options){

    $pro = new profile();
    $pro->setProfileInfo($options['profile']);

    $final = true;
    foreach ($pro->profileModules as $val){
        $val['module'] = $val['module_name'];
        $val['version'] = $val['module_version'];

        if ($val['module'] == 'siteclone') {
            continue;
        }
        
        $module = new moduleinstaller($val);
        if ($module->isInstalled($val['module_name'])){
            upgrade_module($val);
        } else {
            $ret = install_module_silent($val);
            if (!$ret) $final = false;
        }
    }
    
    // set template
    $pro->setProfileTemplate();
    
    // reload language
    $reload = new moduleinstaller();
    $reload->reloadCosLanguages();
    return $final;
}

function cos_purge_from_profile ($options) {
    $pro = new profile();
    $pro->setProfileInfo($options['profile']);

    $mods = moduleloader::getAllModules();
    
    //print_r($mods); die;
    foreach ($mods as $module) {
        if (!$pro->isModuleInProfile($module['module_name'])) {
            purge_module($options = array ('module' => $module['module_name']));
        }
    } 

    $temps = layout::getAllTemplates();
    foreach ($temps as $template) {
        if (!$pro->isTemplateInProfile($template)) {
            purge_template($options = array ('template' => $template));         
        }
    }
}

/**
 * function for updating a modules .ini file with new settings
 * from updated ini-dist file.
 *  
 * @param array     $options 
 */
function upgrade_config_ini_file ($options){
    
    $ini_file_path = _COS_PATH . "/config/config.ini";
    $ini_dist_path = _COS_PATH . "/profiles/$options[profile]/config.ini-dist";

    $ini_file = config::getIniFileArray($ini_file_path, true);
    $ini_dist = config::getIniFileArray($ini_dist_path, true);
    
    $ary = array_merge($ini_dist, $ini_file);
    $ary_diff = array_diff($ary, $ini_file);

    $content = config::arrayToIniFile($ary);
    file_put_contents($ini_file_path, $content);

    if (empty($ary_diff)){
        cos_cli_print("No new ini file settings for config.ini");
    } else {
        $new_settings_str = config::arrayToIniFile($ary_diff);
        cos_cli_print("New ini file written to: $ini_file_path");
        cos_cli_print("These are the new ini settings for config.ini");
        cos_cli_print(trim($new_settings_str));
    }
}

/**
 * wrapper function for reloading a profile
 * does the same as loading a profile, but keeps config/config.ini
 */
function reload_profile($options) {
    $pro = new profile();
    $pro->reloadProfile($options['profile']);

}



/**
 * wrapper function for creating a profile
 */
function recreate_profile($options) {
    $pro = new profile();
    $pro->recreateProfile($options['profile']);
}

/**
 * sets a flag that indicates that we uses master when 
 * making profiles
 * @param array $options
 */
function profile_use_master ($options){
    config::$vars['profile_use_master'] = 1;
    $pro = new profile();
    $pro->setMaster();
}

/**
 * sets a flag indicating that we dont hide common secrets
 * when building a profile
 */
function profile_dont_hide_secrets ($options) {
    $pro = new profile();
    $pro->setNoHideSecrets(); 
}

/**
 * wrapper function for creating a profile
 */
function create_profile($options) {
    $pro = new profile();
    $pro->createProfile($options['profile']);
}


/**
 * only add commands if we are in CLI mode
 */
if (cos_is_cli()){  
    
    mainCli::setCommand('profile', array(
        'description' => 'Profile commands. Profiles are configuration for complete sites. You can build profiles for distribution, install from profiles, reload configuration and more.',
    ));

    mainCli::setOption('profile_dont_hide_secrets', array(
        'long_name'   => '--no-hide-secrets',
        'description' => 'Set this an common secrets will not be hidden when building profile',
        'action'      => 'StoreTrue'
    ));

    mainCli::setOption('load_profile', array(
        'long_name'   => '--load',
        'description' => 'Will load a profile. This means that any ini file from a profile 
    will overwrite current ini files',
        'action'      => 'StoreTrue'
    ));

    mainCli::setOption('reload_profile', array(
        'long_name'   => '--reload',
        'description' => 'Same as loading a profile, but config/config.ini will not be loaded',
        'action'      => 'StoreTrue'
    ));

    mainCli::setOption('profile_use_master', array(
        'long_name'   => '--master',
        'description' => 'Use master - else we will use specified versions of modules 
            and templates found in profile.inc',
        'action'      => 'StoreTrue'
    ));

    mainCli::setOption('create_profile', array(
        'long_name'   => '--create',
        'description' => 'Will create profile with specified name which will be placed in /profiles/{profile}',
        'action'      => 'StoreTrue'
    ));

    mainCli::setOption('recreate_profile', array(
        'long_name'   => '--recreate',
        'description' => 'Will recreate profile with specified name. Same as create, but new config/config.ini-dist will not be created',
        'action'      => 'StoreTrue'
    ));

    mainCli::setOption('upgrade_from_profile', array(
        'long_name'   => '--all-up',
        'description' => 'Will upgrade from specified profile. If new module or templates exists they will be installed. ',
        'action'      => 'StoreTrue'
    ));

    mainCli::setOption('install_from_profile', array(
        'long_name'   => '--all-in',
        'description' => 'Will install all modules from specified profile',
        'action'      => 'StoreTrue'
    ));
    
    mainCli::setOption('cos_purge_from_profile', array(
        'long_name'   => '--purge',
        'description' => 'Will remove modules and templates no logner specified in profile',
        'action'      => 'StoreTrue'
    ));

    mainCli::setOption('upgrade_config_ini_file', array(
        'long_name'   => '--config-up',
        'description' => 'Will upgrade config.ini from profile with any new settings found in profile/{profile}/config.ini-dist',
        'action'      => 'StoreTrue'
    ));



    mainCli::setArgument('profile',
        array('description'=> 'specify the profile to create or install',
              'optional' => false));

}
