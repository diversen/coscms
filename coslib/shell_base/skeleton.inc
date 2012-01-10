<?php

/**
 * File containing functions for creating a skeleton when writining a module
 *
 * @package     shell
 */

/**
 * function for creating a skeleton for a module. We are just
 * using shell command touch for creating the files.
 *
 * @param   array   e.g: <code>array('Module' => 'test')</code> This will create
 *                  the following folder /modules/test containing base files
 *                  used when writing a module
 * @return  int     result from shell operation 0 is success
 */
function create_module_skeleton($args){
    $module_name = $args['Module'];
    $ary = explode('/', $module_name);
    $module_path = _COS_PATH . '/modules/' . $module_name;
    
    cos_exec('mkdir ' . $module_path);

    if (count($ary) == 1) {
        // create dirs for sql. Only need in a base module
        $mysql_dir = $module_path . "/mysql";
        cos_exec('mkdir ' . $mysql_dir);
        $mysql_up = $mysql_dir . "/up";
        cos_exec('mkdir ' . $mysql_up);
        $mysql_down = $mysql_dir . "/down";
        cos_exec('mkdir ' . $mysql_down);

        // create dirs for language. Only need in base module
        $lang_dir = $module_path . "/lang";
        cos_exec('mkdir ' . $lang_dir);
        $lang_dir_en = $module_path . "/lang/en_GB";
        cos_exec('mkdir ' . $lang_dir_en);

        $views_dir = $module_path . "/views";
        cos_exec('mkdir ' . $views_dir);

        $files = $module_path . "/menu.inc ";
        $files.= $module_path . "/index.php ";
        $files.= $module_path . "/README.md ";
        $files.= $module_path . "/install.inc ";
        $files.= $module_path . "/$module_name.ini ";
        $files.= $module_path . "/$module_name.ini-dist ";
        $files.= $module_path . "/model.$module_name.inc ";
        $files.= $module_path . "/view.$module_name.inc ";
        $files.= $lang_dir_en . "/system.inc ";
        $files.= $lang_dir_en . "/language.inc ";
    } else {
        $files.= $module_path . "/model.".$ary[1].".inc ";
        $files.= $module_path . "/view.".$ary[1].".inc ";
    }
    cos_cli_print('Creating files: ');
    return (cos_exec('touch ' . $files));
}

/**
 * @TODO create function that puts initial content into
 *       install.inc and config.ini-dist
 */
function createProfileScript($profile){
    /*
    $modules = $this->getModules();
    $str = var_export($modules, true);
    $module_str = '<?php ' . "\n\n";
    $module_str.= '$_PROFILE_MODULES = ' . $str . ";";
    $module_str.= "\n\n";
    $module_str.= '$_PROFILE_TEMPLATE = ' . "'" . $this->getProfileTemplate() . "'" . ';';
    $module_str.= "\n\n";
    $module_str.= '$_PROFILE_USE_HOME = ' . $this->getProfileUseHome() . ';';
    $module_str.= "\n\n";
    $file = _COS_PATH . "/profiles/$profile/profile.inc";
    if (!file_put_contents($file, $module_str)){
        print "Could not write to $file";
    }*/
}

mainCli::setCommand('skeleton', array(
    'description' => 'Create skeleton for a module',
));

mainCli::setOption('create_module_skeleton', array(
    'long_name'   => '--create-module',
    'description' => 'creates module specified',
    'action'      => 'StoreTrue'
));

mainCli::setArgument('Module', array('description'=> 'Specify the module to create skeleton for'));


?>