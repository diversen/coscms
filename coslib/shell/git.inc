<?php

include_once "coslib/git.php";
include_once "coslib/moduleinstaller.php";

/**
 * funtion for installing a module from a git repo name
 * @param array     $options array ('repo' => 'git::repo')
 * @param string    $type (module, profile or template)
 */
function cos_git_install ($options, $type = 'module'){
    $module_name = git_module_name_from_repo($options['repo']);
    if (!$module_name){
        cos_cli_abort('Install command need a valid repo name');
    }

    $options['module'] = $module_name;

    $ret = cos_git_clone ($options, $type);
    if ($type == 'module'){
        //die;
        $str = install_module($options, true);
        cos_cli_print($str);
        return;
    }
    
    if ($type == 'template') {
        $str = install_template($options, true);
        cos_cli_print($str);
        return;
    }
}

/**
 * function for getting path to a repo.
 * @param string     $module_name
 * @param string     $type (module, profile, template)
 * @return string    $repo_path the locale path to the repo.
 */

function cos_get_repo_path ($module_name, $type = 'module'){
    // set repo_dir according to module type.
    if($type == 'template'){
        $repo_dir = _COS_PATH . "/htdocs/templates/$module_name";
    } else if ($type == 'profile'){
        $repo_dir = _COS_PATH . "/profiles/$module_name";
    } else {
        $repo_dir = _COS_MOD_PATH . "/$module_name";
    }
    return $repo_dir;
}

/**
 * function for cloning a template repo
 * @param array $options
 */
function cos_git_clone_template($options){
    
    cos_git_clone($options, 'template');
    
    $template = git_module_name_from_repo($options['repo']);
    $options['template'] = $template;
    $str = install_template($options, true);
    cos_cli_print($str);
    return;
}

/**
 * function for cloning a profile repo
 * @param array $options
 */
function cos_git_clone_profile($options){
    cos_git_clone($options, 'profile');
}



/**
 * function used for cloning a repo
 * @param array $options
 * @param string $type
 */

function cos_git_clone($options, $type){
    
    // get latest repo tag
    $latest = git_get_latest_remote_tag($options['repo']);
    
    // if version is set we will use this version.
    // or we will use latest tag.
    if (isset($options['version'])){
        $checkout = $options['version'];
    } else {
        $checkout = $latest;
    }

    // we abort if there is no tags.
    if (!$latest){
        $checkout = 'master';
    }
    
    // check if profile use master or if master is set
    if (isset(config::$vars['git_use_master']) || isset($options['master'])){
        $checkout = 'master';
    }

    // set dir according to module type. Template, profile or module.
    if ($type == 'template'){
        $clone_path = _COS_HTDOCS . "/templates";
    } else if ($type == 'profile'){
        $clone_path = _COS_PATH . "/profiles";
    } else {
        $clone_path = _COS_MOD_PATH;
    }

    // create path if it does not exists
    if (!file_exists($clone_path)){
        mkdir($clone_path);
    }

    // get module name
    $module_name = git_module_name_from_repo($options['repo']);
    $module_path = "$clone_path/$module_name";

    // if dir exists we check if it is a git repo
    // or just a directory
    $ret = null;
    if (file_exists($module_path)){
        $repo_dir = $clone_path . "/$module_name";

        // check if path is a git repo
        $git_folder = $repo_dir . "/.git";
        if (file_exists($git_folder)){
            // repo exists. We pull changes and set version
            $git_command = "cd $repo_dir && git checkout master && git pull && git checkout $checkout";
        } else {
            // no git repo - empty dir we presume.
            $git_command = "cd $clone_path && git clone $options[repo] && cd $module_name && git checkout $checkout";
        }
        $ret = cos_exec($git_command);
    } else {
        $git_command = "cd $clone_path && git clone $options[repo] && cd $module_name && git checkout $checkout";
        $ret = cos_exec($git_command);
    }

    // evaluate actions
    if ($ret){
        cos_cli_abort("$git_command failed");
    }    
}

/**
 * cli call function is --master is set then master will be used instead of
 * normal tag
 *
 * @param array $options
 */
function cos_git_use_master ($options){
    config::$vars['git_use_master'] = 1;
}

/**
 * get latest tag for modules and templates and
 * upgrade according to latest tag
 * @param   array   options from cli env
 */
function cos_git_upgrade_all ($options){

    $modules = profile::getModules();
    foreach ($modules as $key => $val){
        if (isset(config::$vars['git_use_master'])){
            $tag = 'master';
        } else {
            $tag = git_get_latest_remote_tag($val['public_clone_url'], true);
        }

        if ( ($tag == 'master') OR  ($tag != $val['module_version'])) { 
            $status = "Will upgrade $val[module_name]";
            cos_cli_print_status('UPGRADE', 'g', $status);
            cos_git_upgrade ($val, $tag, 'module');
        }
    }
    
    $templates = profile::getAllTemplates();

    foreach ($templates as $key => $val){
        if (isset(config::$vars['git_use_master'])){
            $tag = 'master';
        } else {
            $tag = git_get_latest_remote_tag($val['public_clone_url'], true);
        }

        if ( ($tag == 'master') OR  ($tag > $val['module_version'])) {
            cos_git_upgrade ($val, $tag, 'template');
        }
    }
}




/**
 * function for adding and commiting all modules and templates
 * @param   array   options from cli env
 */
function cos_git_commit_all ($options){

    $modules = profile::getModules();
    foreach ($modules as $key => $val){
        cos_git_commit($val, 'module');

    }

    $templates = profile::getAllTemplates();
    foreach ($templates as $key => $val){
        cos_git_commit($val, 'template');
    }
}

/**
 * function for tagging all modules and templates
 * @param   array   options from cli env
 */
function cos_git_tag_all ($options){

    $version = cos_readline('Enter tag version to use ');
    $modules = profile::getModules();
    foreach ($modules as $key => $val){

        $tags = git_get_local_tags($val['module_name'], 'module');
        if (in_array($version, $tags)) {
            cos_cli_print_status('NOTICE', 'y', "Tag already exists local for module '$val[module_name]'.");
        }
        
        $val['new_version'] = $version;
        cos_git_tag($val, 'module');
    }

    $templates = profile::getAllTemplates();
    foreach ($templates as $key => $val){
        
        $tags = git_get_local_tags($val['module_name'], 'template');
        if (in_array($version, $tags)) {
            cos_cli_print_status('NOTICE', 'y', "Tag already exists local for template '$val[module_name]'");
        }
        
        $val['new_version'] = $version;
        cos_git_tag($val, 'template');
    }
}

/**
 * function for tagging a module or all modules
 * @param array $val
 * @param string $typ (template or module)
 * @return type 
 */
function cos_git_tag ($val, $type = 'module'){
    $repo_path = cos_get_repo_path($val['module_name'], $type);

    if (!cos_git_is_repo ($repo_path)){
        cos_cli_print("$repo_path is not a git repo");
        return;
    }

    if (empty($val['private_clone_url'])) {
        cos_cli_print("No private clone url is set in install.inc of $val[module_name]");
        return;
    }

    if (!cos_confirm_readline("You are about to tag module: $val[module_name]. Continue?")){
        return;
    }
    
    $git_command = "cd $repo_path && git add . && git commit -m \"$val[new_version]\" && git push $val[private_clone_url]";
    proc_close(proc_open($git_command, array(0 => STDIN, 1 => STDOUT, 2 => STDERR), $pipes));
    
    $git_command = "cd $repo_path && git tag -a \"$val[new_version]\" -m \"$val[new_version]\"";
    proc_close(proc_open($git_command, array(0 => STDIN, 1 => STDOUT, 2 => STDERR), $pipes));

    $git_command = "cd $repo_path && git push --tags $val[private_clone_url]";
    passthru($git_command);

    echo "\n---\n";
}

/**
 * tags all files
 * @param type $options
 */
function cos_git_tag_all_files ($options){
    $version = cos_readline('Enter tag version to use ');

    $modules = profile::getModules();
    foreach ($modules as $key => $val){
        $val['module'] = $val['module_name'];
        $val['new_version'] = $version;
        cos_git_tag_install_file($val, 'module');
    }

    $templates = profile::getAllTemplates();
    foreach ($templates as $key => $val){
        $val['module'] = $val['module_name'];
        $val['new_version'] = $version;
        cos_git_tag_install_file($val, 'template');
    }
}

/**
 * change all install.inc files with a new version
 * @param array $val
 * @param string $type
 * @return void
 */
function cos_git_tag_install_file ($val, $type = 'module'){
    $repo_path = cos_get_repo_path($val['module_name'], $type);

    if (!cos_git_is_repo ($repo_path)){
        cos_cli_print("$repo_path is not a git repo");
        return;
    }

    $install_file = $repo_path . "/install.inc";
    $handle = @fopen($install_file, "r");
    $str = '';
    if ($handle) {
        while (($buffer = fgets($handle, 4096)) !== false) {
            if (strstr($buffer, "\$_INSTALL['VERSION']")) {
                $str.= "\$_INSTALL['VERSION'] = $val[new_version];\n";
            } else {
                $str.= $buffer;
            }
        }
        if (!feof($handle)) {
            echo "Error: unexpected fgets() fail\n";
        }
        fclose($handle);
    }
    file_put_contents($install_file, $str);
    cos_cli_print("Tagged file $install_file with version $val[new_version]");
}

/**
 * function for upgrading a module, template or profile according to latest tag
 * or master
 *
 * @param array     with module options
 * @param string    tag with wersion or 'master'
 * @param string    module, templatee or profile.
 */
function cos_git_commit ($val, $type = 'module'){
    $repo_path = cos_get_repo_path($val['module_name'], $type);

    if (!cos_git_is_repo ($repo_path)){
        cos_cli_print("$repo_path is not a git repo");
        return;
    }

    if (empty($val['private_clone_url'])) {
        cos_cli_print("No private clone url is set in install.inc of $val[module_name]");
        return;
    }
    
    if (!cos_confirm_readline("You are about to commit module: $val[module_name]. Continue?")){
        return;
    }


    cos_cli_print_status('COMMIT', 'g', "Module: '$val[module_name]'");
    
    $git_add = "cd $repo_path && git add . ";
    cos_exec($git_add);

    $git_command = "cd $repo_path && git commit ";
    proc_close(proc_open($git_command, array(0 => STDIN, 1 => STDOUT, 2 => STDERR), $pipes));

    $git_push = "cd $repo_path && git push $val[private_clone_url]";
    //passthru($git_command);
    cos_exec($git_push);
    echo PHP_EOL;
}

/**
 * function for upgrading a module, template or profile according to latest tag
 * or master
 *
 * @param array     with module options
 * @param string    tag with wersion or 'master'
 * @param string    module, templatee or profile. 
 */
function cos_git_upgrade ($val, $tag = 'master', $type = 'module'){

    if (!isset($val['module_name'])) {
        $val['module_name'] = git_module_name_from_repo($val['repo']);
    }
    
    $repo_path = cos_get_repo_path($val['module_name'], $type);
    
    if (!cos_git_is_repo ($repo_path)){
        cos_cli_print("$repo_path is not a git repo - will not upgrade");
        return;
    }

    $git_command = "cd $repo_path && ";
    $git_command.= "git checkout master && ";
    $git_command.= "git pull && git fetch --tags && ";
    $git_command.= "git checkout $tag";
    
    cos_exec($git_command);
    
    if ($type == 'module'){
        // sorry - but it is called with a different name in the upgrade_module
        // function ...
        $val['module'] = $val['module_name'];

        // upgrade to latest set in $_INSTALL['VERSION']
        $val['version'] = null;
        upgrade_module($val);
    }
    
    if ($type == 'template'){
        // TODO: What to do on template upgrade. 
        //$val['module'] = $val['module_name'];

        // when version is set to null we upgrade to latest possible
        //$val['version'] = null;
        //upgrade_module($val);
    }

    // no need to upgrade template, as there is no sql. 
    // print "\n---\n";
}

function cos_git_is_repo($path){
    $repo = $path . "/.git";
    if (!file_exists($repo)){
        return false;
    }
    return true;
}

function cos_git_no_questions (){
    cos_confirm_readline(null, 1);
}

/**
 * function for showing git tags (just for testing)
 * @param array $options
 */
function cos_git_echo_remote_tags ($options){
    if (empty($options['repo']))  {
        cos_cli_abort('You need to specify a repo');
    }
    $tags = git_get_remote_tags($options['repo']);

    if (empty($tags)) {
        cos_cli_abort('No tags');
    }
    $latest = git_get_latest_remote_tag($options['repo']);
    cos_cli_print("Latest is: $latest");
}

function cos_git_coscms_pull ($version = null) {
    $command = 'git pull';
    passthru($command);
}



mainCli::setCommand('git', array(
    'description' => 'Commands for using git. Can be used for installing and uninstalling modules from git repos (remote)',
));

mainCli::setOption('cos_git_no_questions', array(
    'long_name'   => '--silence',
    'short_name'   => '-s',
    'description' => 'Will ask [y] to all questions raised',
    'action'      => 'StoreTrue'
));

mainCli::setOption('cos_git_use_master', array(
    'long_name'   => '--master',
    'short_name'   => '-m',
    'description' => 'Will use master. Valid for all-up',
    'action'      => 'StoreTrue'
));

mainCli::setOption('cos_git_install', array(
    'long_name'   => '--mod-in',
    'description' => 'Will clone specified remote url with latest version',
    'action'      => 'StoreTrue'
));

mainCli::setOption('cos_git_clone_template', array(
    'long_name'   => '--temp-in',
    'description' => 'Will install remote clone url with latest version',
    'action'      => 'StoreTrue'
));

mainCli::setOption('cos_git_upgrade_template', array(
    'long_name'   => '--temp-up',
    'description' => 'Will check latest remote version and compare with locale version. If remote is higher it will be checked out',
    'action'      => 'StoreTrue'
));

mainCli::setOption('cos_git_clone_profile', array(
    'long_name'   => '--pro-in',
    'description' => 'Will clone remote profile with latest version',
    'action'      => 'StoreTrue'
));

mainCli::setOption('cos_git_upgrade', array(
    'long_name'   => '--mod-up',
    'description' => 'Will check latest remote version and compare with locale version. If remote is higher it will be checked out and system will be upgraded',
    'action'      => 'StoreTrue'
));

mainCli::setOption('cos_git_upgrade_all', array(
    'long_name'   => '--all-up',
    'description' => 'Will check latest remote versions of modules, templates and profiles, and compare with locale version. If remote is higher it will be checked out and system will be upgraded',
    'action'      => 'StoreTrue'
));

mainCli::setOption('cos_git_commit_all', array(
    'long_name'   => '--all-commit',
    'description' => 'Will try and commit all modules and templates in one try.',
    'action'      => 'StoreTrue'
));

mainCli::setOption('cos_git_tag_all', array(
    'long_name'   => '--all-tag',
    'description' => 'Will tag and push tags for all modules and templates in one try.',
    'action'      => 'StoreTrue'
));

mainCli::setOption('cos_git_tag_all_files', array(
    'long_name'   => '--all-tag-files',
    'description' => 'Will tag all install files with new version.',
    'action'      => 'StoreTrue'
));

/*
 * How to specify an options like this --test=test
mainCli::setOption('cos_master',    array(
       'long_name'   => '--message',
       'description' => 'Commit message (used with all* commands)',
       'action'      => 'StoreString',
       'default'     => 'test',
       'help_name'   => 'commit message') 
    
);*/

mainCli::setOption('cos_git_echo_remote_tags', array(
    'long_name'   => '--echo-remote-tags',
    'description' => 'Will list remote tags',
    'action'      => 'StoreTrue'
));

mainCli::setOption('cos_git_coscms_pull', array(
    'long_name'   => '--pull-base',
    'description' => 'Will pull latest master of CosCMS',
    'action'      => 'StoreTrue'
));

mainCli::setOption('git_test', array(
    'long_name'   => '--test',
    'description' => 'test',
    'action'      => 'StoreTrue',
        'action'      => 'StoreTrue'
));

function git_test ($options) {
    print_r($options);
}

mainCli::setArgument(
    'repo',
    array('description'=> 'Specify the git repo to be used',
        'optional' => true,
));

mainCli::setArgument(
    'version',
    array('description'=> 'Specify the version to checkout e.g. master or 1.11',
        'optional' => true,
));
