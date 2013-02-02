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
function menu_install_menu($options){
    

    // check if module exists in modules dir
    $module_path = config::getModulePath($options['module']);
    if (!file_exists($module_path)){
        cos_cli_print("module $options[module] does not exists in modules dir. ");
    }

    $mod = new moduleinstaller($options);
    $res = $mod->insertMenuItem();
    if ($res) {
        cos_cli_print("Main menu item for $options[module] inserted");
    }  
}

function menu_install_all (){

    $mods = moduleinstaller::getModules();
    foreach ($mods as $key => $val){
        $options = array('module' => $val['module_name']);
        //$mod = new moduleInstaller($options);
        //$mod->insertMenuItem();
        menu_install_menu($options);
    }
    return 1;
}

function menu_uninstall_all (){

    $mods = moduleinstaller::getModules();
    foreach ($mods as $key => $val){
        $options = array('module' => $val['module_name']);
        menu_uninstall_menu($options);
    }
    return 1;
}


/**
 * function for upgrading a module
 *
 * @param  array   options the module to be upgraded
 */
function menu_uninstall_menu($options){
    // check if module exists in modules dir
    $module_path = _COS_MOD_PATH . '/' . $options['module'];
    if (!file_exists($module_path)){
        cos_cli_print("module $options[module] does not exists in modules dir. ");
    }

    $menu = new moduleinstaller($options);
    $res = $menu->deleteMenuItem($options['module']);
    if ($res) {
        cos_cli_print("Main menu item for $options[module] deleted");
    }

}

mainCli::setCommand('menu', array(
    'description' => 'Install or uninstall menu items',
));

// create commandline parser
mainCli::setOption('menu_install_menu', array(
    'long_name'   => '--install-menu',
    'description' => 'Install specified modules main menu item(s)',
    'action'      => 'StoreTrue'
));

// create commandline parser
mainCli::setOption('menu_install_all', array(
    'long_name'   => '--install-all',
    'description' => 'Install all module main menu items',
    'action'      => 'StoreTrue'
));

// create commandline parser
mainCli::setOption('menu_uninstall_all', array(
    'long_name'   => '--uninstall-all',
    'description' => 'Uninstall all module main menu items',
    'action'      => 'StoreTrue'
));

// create commandline parser
mainCli::setOption('menu_uninstall_menu', array(
    'long_name'   => '--uninstall-menu',
    'description' => 'uninstall specified modules main menu item(s)',
    'action'      => 'StoreTrue'
));


mainCli::setArgument(
    'module',
    array('description'=> 'Module',
        'optional' => true,
));

