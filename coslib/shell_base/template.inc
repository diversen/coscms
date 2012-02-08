<?php

/**
 * File containing template functions for shell mode
 *
 * @package     shell
 */

/**
 * @ignore
 */
include_once "coslib/moduleInstaller.php";
include_once "coslib/profile.php";

/**
 * wrapper function for settings template
 * change template in db to load specified template.
 * @param   array   options
 */
function set_template($options){
    $template = $options['template'];
    $pro = new profile();
    $pro->setProfileTemplate($template);
}


/**
 * function for getting info about a template.
 * @param   string   module_name to get install info about
 */
function get_template_info($template_name){
    $template_dir = _COS_PATH . "/htdocs/templates/$template_name";
    $install_file = "$template_dir/install.inc";
    if (!file_exists($install_file)){
        die("No install file '$install_file' found in: '$template_dir'\n");
    }
    include_once $install_file;
    $info = $_INSTALL;
    return $info;
}

function install_template ($options, $return_val = null) {
    
    if (!isset($options['template'])) {
        $template = $options['module_name'];
    } else {
        $template = $options['template'];
    }
    
    //$template_path = _COS_PATH . "/htdocs/templates/$template";

    $str = "Proceeding with install of template $template";
    $install = new templateInstaller();
    $install->setInstallInfo($options);
    $ret = $install->install();
    if (!$ret) {
        $str.=$install->error;
    } else {
        $str.=$install->confirm;
    }
    
    if ($return_val) {
        return $str;
    } else {
        cos_cli_print($str);
    }
}

/**
 * function for purgeing a template
 *
 * @param   array  options
 */
function purge_template($options){
    //uninstall_module($options);
    if ( strlen($options['template']) == 0 ){
        cos_cli_print("No such template: $options[template]");
        cos_cli_abort();
    }
    $template_path = _COS_PATH . '/htdocs/templates/' . $options['template'];
    if (!file_exists($template_path)){
        cos_cli_print("Template already purged: No such template path: $template_path");
        cos_cli_abort();
    }
    $command = "rm -rf $template_path";
    cos_exec($command);
}

mainCli::setCommand('template', array(
    'description' => 'set a template from CLI',
));

mainCli::setOption('set_template', array(
    'long_name'   => '--set-template',
    'description' => 'Will set new template',
    'action'      => 'StoreTrue'
));

mainCli::setOption('purge_template', array(
    'long_name'   => '--purge',
    'description' => 'Will purge (remove files) specified template',
    'action'      => 'StoreTrue'
));

// create commandline parser
mainCli::setOption('install_template', array(
    'long_name'   => '--temp-in',
    'description' => 'Will install specified template',
    'action'      => 'StoreTrue'
));

mainCli::setArgument(
    'template',
    array('description'=> 'specify the template to set or purge',
          'optional' => true));

mainCli::setArgument(
    'version',
    array('description'=> 'Specify the version to upgrade or downgrade to',
        'optional' => true,
));
