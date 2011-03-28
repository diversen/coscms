<?php

/**
 * File containing profile functions for shell mode
 *
 * @package     shell
 */

/**
 * @ignore
 */
include_once "lib/profile.php";


/**
 * wrapper function for loading a profile
 */
function load_profile($options) {
    $pro = new profile();
    $profiles = get_file_list('profiles', array('dir_only' => true));
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
function create_profile($options) {
    $pro = new profile();
    $pro->createProfile($options['profile']);
}


function upgrade_from_profile ($options){
    // use profile object
    $pro = new profile();
    $pro->setProfileInfo($options['profile']);

    // install modules
    foreach ($pro->profileModules as $key => $val){
        
        
        $val['repo'] = $val['public_clone_url'];
        $val['version'] = $val['module_version'];
        $val['Module'] = $val['module_name'];

        $module = new moduleInstaller($options);

        if ($module->isInstalled($val['module_name'])){
            _git_upgrade($val, $val['module_version']);
        } else {
            git_install($val, $val['module_version']);
        }
    }

    // install templates
    foreach ($pro->profileTemplates as $key => $val){
        // check if template is installed.
        //$template = new templateInstaller($template);
        $options['repo'] = $val['public_clone_url'];
        $options['version'] = $val['module_version'];

        // no db operations. Just clone version.
        git_install($val, $val['module_version'], 'template');
    }
}

/**
 * wrapper function for creating a profile
 */
function recreate_profile($options) {
    $pro = new profile();
    $pro->recreateProfile($options['profile']);
}

function use_master ($options){
    $pro = new profile();
    $pro->setMaster();
}

mainCli::setCommand('profile', array(
    'description' => 'load or create a profile from current settings',
));

mainCli::setOption('load_profile', array(
    'long_name'   => '--load',
    'description' => 'Will load a profile',
    'action'      => 'StoreTrue'
));

mainCli::setOption('use_master', array(
    'long_name'   => '--master',
    'description' => 'Will create profile with master repos for all modules and templates',
    'action'      => 'StoreTrue'
));

mainCli::setOption('reload_profile', array(
    'long_name'   => '--reload',
    'description' => 'Same as loading a profile, but config/config.ini will not be loaded',
    'action'      => 'StoreTrue'
));

mainCli::setOption('create_profile', array(
    'long_name'   => '--create',
    'description' => 'Will create profile with specified name',
    'action'      => 'StoreTrue'
));

mainCli::setOption('recreate_profile', array(
    'long_name'   => '--recreate',
    'description' => 'Will recreate profile with specified name. Same as create, but new config/config.ini-dist will not be created',
    'action'      => 'StoreTrue'
));

mainCli::setOption('upgrade_from_profile', array(
    'long_name'   => '--up-all',
    'description' => 'Will upgrade from profile',
    'action'      => 'StoreTrue'
));


mainCli::setArgument('profile',
    array('description'=> 'specify the profile to create or install',
          'optional' => false));
