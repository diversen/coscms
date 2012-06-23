<?php

/**
 * File containing module functions for shell mode
 * (install, update, delete modules)
 *
 * @package     shell
 */


/**
 * function for upgrading a module
 *
 * @param  array   options the module to be upgraded
 */
function install_menu($options){
    

    // check if module exists in modules dir
    $module_path = config::getModulePath($options['module']);
    if (!file_exists($module_path)){
        cos_cli_print("module $options[module] does not exists in modules dir. ");
        //cos_cli_abort();
    }

    $mod = new moduleInstaller($options);
    $res = $mod->insertMenuItem();
    if ($res) {
        cos_cli_print("Main menu item for $options[module] inserted");
    }  
}

function menu_install_all (){

    $mods = moduleInstaller::getModules();
    foreach ($mods as $key => $val){
        $options = array('module' => $val['module_name']);
        //$mod = new moduleInstaller($options);
        //$mod->insertMenuItem();
        install_menu($options);
    }
    return 1;
}

function menu_uninstall_all (){

    $mods = moduleInstaller::getModules();
    foreach ($mods as $key => $val){
        $options = array('module' => $val['module_name']);
        //$mod = new moduleInstaller($options);
        //$mod->deleteMenuItem();
        uninstall_menu($options);
    }
    return 1;
}


/**
 * function for upgrading a module
 *
 * @param  array   options the module to be upgraded
 */
function uninstall_menu($options){
    // check if module exists in modules dir
    $module_path = _COS_PATH . '/modules/' . $options['module'];
    if (!file_exists($module_path)){
        cos_cli_print("module $options[module] does not exists in modules dir. ");
        //cos_cli_abort();
    }

    $menu = new moduleInstaller($options);
    $res = $menu->deleteMenuItem($options['module']);
    if ($res) {
        cos_cli_print("Main menu item for $options[module] deleted");
    }

}

mainCli::setCommand('menu', array(
    'description' => 'Commands for doing operations on menu items',
));

// create commandline parser
mainCli::setOption('install_menu', array(
    'long_name'   => '--install-menu',
    'description' => 'Will install specified modules main menu item',
    'action'      => 'StoreTrue'
));

// create commandline parser
mainCli::setOption('install_all', array(
    'long_name'   => '--install-all',
    'description' => 'Will install all module main menu items',
    'action'      => 'StoreTrue'
));

// create commandline parser
mainCli::setOption('uninstall_all', array(
    'long_name'   => '--uninstall-all',
    'description' => 'Will uninstall all module main menu items',
    'action'      => 'StoreTrue'
));

// create commandline parser
mainCli::setOption('uninstall_menu', array(
    'long_name'   => '--uninstall-menu',
    'description' => 'Will install specified modules main menu item',
    'action'      => 'StoreTrue'
));


mainCli::setArgument(
    'module',
    array('description'=> 'Specify the module to install or upgrade or package',
        'optional' => true,
));

