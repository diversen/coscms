<?php

/**
 * File containing template functions for shell mode
 *
 * @package     shell
 */

/**
 * @ignore
 */
include_once "moduleInstaller.php";
include_once "profile.php";

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
 * function for packing a template
 * @param   array   options
 */
function package_template($options){
    $package = new templateInstaller($options['template']);
    if (empty($package->installInfo['VERSION'])){
        die ('No info about version of template');
    }

    $package_name = $options['template'] . "-" . $package->installInfo['VERSION'] . ".tar.gz";
    $package_name = _COS_PATH . '/tmp/' . $package_name;
    $package_path = _COS_PATH . "/htdocs/templates/$options[template]";
    $tgz_com = "tar cfz $package_name $package_path";
    cos_exec($tgz_com);
}

/**
 * function for unpacking a module from tmp dir
 *
 * @param   string  the full path to the module to unpackage
 */
function unpackage_template($file){
    //$package_name = _COS_PATH . '/tmp/' . $package_name;
    //$package_path = _COS_PATH . "/htdocs/templates/$options[template]";
    $tgz_com = "tar xfz $file";
    return (cos_exec($tgz_com));
}

/**
     *
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

/**
 *
 * @param  array    options
 */
function install_template($options){
    $template = $options['template'];    
    $template_path = _COS_PATH . "/htdocs/templates/$template";
    if (!file_exists($template_path)){
        cos_cli_print("Template '$template' does not exists in template dir. ");
        cos_cli_print("We will search remote server to fetch named template ");
        $tmp_file = cli_server_download_template($options);
        if ($tmp_file){
            unpackage_template($tmp_file);
            cos_cli_print("Template $template placed in $template_path");
        } else {
            cos_cli_print("Template $options[template] could not be downloaded");
            cos_cli_abort();
        }
    } else {
        cos_cli_print("Template $options[template] already in template dir");
    }
}

/**
 *
 * @param array $options
 */
function upgrade_template($options){
    $options['Module'] = $options['template'];
    $info = get_template_info($options['template']);
    $remote_info = unserialize(cli_server_get_module_info($options, 2));
    if ( (float)$remote_info[0]['version'] > (float)$info['VERSION'] ){
        cos_cli_print("Remote version greater than locale");
        $ret = confirm_readline('Proceed with Upgrade: Current version will be destroyed. ');
        if ($ret){
            $tmp_file = cli_server_download_template($options);
            if ($tmp_file){
                unpackage_template($tmp_file);
                cos_cli_print("Template $template placed in $template_path");
            } else {
                cos_cli_print("Module $options[template] could not be downloaded");
                cos_cli_abort();
            }
        }
    } else {
        cos_cli_print("Locale version: $info[VERSION]. Remote version " . $remote_info[0]['version']);
        cos_cli_print('Nothing to upgrade!');
    }
}

/**
 * function for downloding a template. Call module function for the same
 *
 * @param array  options
 */
function cli_server_download_template($options){
    $options['Module'] = $options['template'];
    $tmp_file = cli_server_download_module($options, $type = 2);
    return $tmp_file;
}

/**
 * function for printing info about a module from remote server
 *
 * @param   array   options
 * @param   int     type of module (1 => module 2 => template 3 => profile
 */
function cli_server_print_template_info($options, $type = 2){
    $options['Module'] = $options['template'];
    cli_server_print_module_info($options, $type);    
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

mainCli::setOption('cli_server_download_template', array(
    'long_name'   => '--download',
    'description' => 'Will get latest release of a template',
    'action'      => 'StoreTrue'
));

mainCli::setOption('cli_server_print_template_info', array(
    'long_name'   => '--get-info',
    'description' => 'Will get remote info about a template',
    'action'      => 'StoreTrue'
));

mainCli::setOption('package_template', array(
    'long_name'   => '--pack-temp',
    'description' => 'Will package specified template for release',
    'action'      => 'StoreTrue'
));

mainCli::setOption('purge_template', array(
    'long_name'   => '--purge',
    'description' => 'Will purge (remove files) specified template',
    'action'      => 'StoreTrue'
));

mainCli::setOption('upgrade_template', array(
    'long_name'   => '--upgrade',
    'description' => 'Will upgrade specified template',
    'action'      => 'StoreTrue'
));

// create commandline parser
mainCli::setOption('install_template', array(
    'long_name'   => '--install',
    'description' => 'Will install specified template',
    'action'      => 'StoreTrue'
));


mainCli::setArgument('template',
    array('description'=> 'specify the template to load, pack download or upgrade',
          'optional' => true));
