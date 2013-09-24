<?php

/**
 * File containing install functions for shell mode
 *
 * @package     shell
 */


/**
 * function for installing coscms from a profile
 */
function cos_install($options = false) {

    // we need a profile specified
    if (!isset($options['profile'])){
        cos_cli_abort('You need to specifiy a profile');
    }

    // create files - logs/ - files/
    cos_create_files();

    // load default base sql.
    load_db_default();

    // use profile object
    $pro = new profile();
    $pro->setProfileInfo($options['profile']);

    // first we set if home link to http://example.com/ will be added to menus.
    $pro->setProfileUseHome();

    // install all the profile modules
    foreach ($pro->profileModules as $key => $val){
        
        // check if master is specified. Else use profile version
        if (config::getMainIni('git_use_master')){
            $tag = 'master';
        } else {
            $tag = $val['module_version'];
        }
        
        $options['repo'] = $val['public_clone_url'];
        $options['version'] = $tag; //$val['module_version'];
        $options['module'] = $val['module_name'];
        
        // check out and install
        cos_git_install($options, 'module');
    }
    
    // install templates
    foreach ($pro->profileTemplates as $key => $val){
        
        if (config::getMainIni('git_use_master')){
            $tag = 'master';
        } else {
            $tag = $val['module_version'];
        }
        
        $options['repo'] = $val['public_clone_url'];
        $options['version'] = $tag; //$val['module_version'];
        $options['template'] = $val['module_name'];
        
        // check out and install
        cos_git_install($options, 'template');
    }

    // load all profile ini files
    $pro->loadProfileFiles($options['profile']);

    // set template
    $pro->setProfileTemplate();
    
    // insert all base translations
    $md = new moduleinstaller();
    $md->reloadCosLanguages();

}

/**
 * @deprecated not used anywhere
 * wrapper function for reseting install to default one
 * all data will be lost
 */

function cos_reset_install (){
    cos_rm_files();
    drop_db_default();
    create_db();
    load_db_default();
    cos_create_files();
    cos_chmod_files();
    cos_install();
}

/**
 * wrapper function for reloading all languages
 * change language settings in config/config.ini to load another language. 
 */
function cos_reload_language(){
    $reload = new moduleinstaller();
    $reload->reloadCosLanguages();
    $reload->reloadLanguages();
}

/**
 * wrapper function for reloading all menus
 */
function cos_menu_lang_reload(){
    cos_reload_language();
    cos_menu_uninstall_all();
    cos_menu_install_all();
}

/**
 * wrapper function for reloading all languages
 */
function cos_config_reload(){
    $reload = new moduleinstaller();
    $reload->reloadConfig();
}

/**
 * cli call function is --master is set then master will be used instead of
 * normal tag
 *
 * @param array $options
 */
function cos_install_use_master ($options){
    config::setMainIni('git_use_master', 1);
}

function cos_check_root ($options = null) {
    cos_needs_root();
}


mainCli::setCommand('install', array(
    'description' => 'install CosCMS from a specified profile [prompt-install is recommended for normal install]]',
));

mainCli::setOption('cos_install_use_master', array(
    'long_name'   => '--master',
    'short_name'   => '-m',
    'description' => 'Will use master. Use if you have all module sources, then the master will be installed',
    'action'      => 'StoreTrue'
));

mainCli::setOption('cos_install', array(
    'long_name'   => '--install',
    'description' => 'Will try and install system',
    'action'      => 'StoreTrue'
));

mainCli::setOption('cos_reload_language', array(
    'long_name'   => '--lang-reload',
    'description' => 'Reinstall system language files according to language set in config/config.ini',
    'action'      => 'StoreTrue'
));

mainCli::setOption('cos_check_root', array(
    'long_name'   => '--check-root',
    'description' => 'Dummy command (used when installing system from shell). Checks if user is super user (root)',
    'action'      => 'StoreTrue'
));

mainCli::setOption('cos_menu_lang_reload', array(
    'long_name'   => '--menu-lang-reload',
    'description' => 'Reloads system menu translation according to config/config.ini, and reload all custom module menus. Useful if you have made changes to system menu items. ',
    'action'      => 'StoreTrue'
));

mainCli::setOption('cos_config_reload', array(
    'long_name'   => '--config-reload',
    'description' => 'Reloads the module table. E.g. you have made a CLI part of a module',
    'action'      => 'StoreTrue'
));

mainCli::setArgument('profile',
    array('description'=> 'specify the profile to install',
          'optional' => true));
