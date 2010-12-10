<?php

/**
 * File containing profile functions for shell mode
 *
 * @package     shell
 */

/**
 * @ignore
 */
include_once "profile.php";


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


mainCli::setArgument('profile',
    array('description'=> 'specify the profile to create or install',
          'optional' => false));
