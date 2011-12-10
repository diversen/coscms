<?php

/**
 * @package shell
 */

/**
 *
 * @param   string    $repo
 * @return  string    module name
 */
function get_module_name_from_repo ($repo){
    $url = parse_url($repo);
    $parts = explode('/', $url['path']);

    if (count($parts) == 1) {
        return false;
    }
    $last = array_pop($parts);
    $module_name = substr($last, 0, -4);
    return $module_name;
}

function get_coscms_tags_local (){
    $command = "git tag -l";
    $ret = exec($command, $output);
    return cos_parse_shell_output($output);
}

/**
 * following function are sligtly modified from:
 * https://github.com/troelskn/pearhub
 *
 * @param   string  a git url url
 * @param   mixed   set this and tags will not be cached in static var
 * @return  array   array of remote tags
 */
function get_tags($url = null, $clear = null) {
    static $tags;

    // clear tags if operation will be used more than once.
    if ($clear){
        $tags = null;
    }
    
    if ($tags == null) {
        $tags = array();
        $output = array();
        $ret = 0;

        $command = "git ls-remote --tags $url";
        exec($command.' 2>&1', $output, $ret);

        foreach ($output as $line) {
            trim($line);
            if (preg_match('~^[0-9a-f]{40}\s+refs/tags/(([a-zA-Z_-]+)?([0-9]+)(\.([0-9]+))?(\.([0-9]+))?([A-Za-z]+[0-9A-Za-z-]*)?)$~', $line, $reg)) {
                $tags[] = $reg[1];
            }
        }
    }
    return $tags;
}

/**
 * following function are sligtly modified from:
 * https://github.com/troelskn/pearhub
 *
 * @param   string  a git url url
 * @param   mixed   set clear and tags will not be cached in static var
 * @return  array   array of remote tags
 */
function latest_tag($repo, $clear = null) {
    $tags = get_tags($repo, $clear);
    if (count($tags) > 0) {
        sort($tags);
        return $tags[count($tags) - 1];
    }
    return null;
}

/**
 * function for showing git tags (just for testing)
 * @param array $options
 */
function remote_tags ($options){
    $tags = get_tags($options['repo']);
    print_r($tags);
    $latest = latest_tag();
    print_r($latest);
}

/**
 * funtion for installing a module
 * @param array     $options
 * @param string    $type (module, profile or template)
 */
function git_install ($options, $type = 'module'){
    $module_name = get_module_name_from_repo($options['repo']);
    if (!$module_name){
        cos_cli_abort('Install command need a valid repo name');
    }

    $options['Module'] = $module_name;

    $ret = git_clone ($options, $type);
    if ($type == 'module'){
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

function get_repo_path ($module_name, $type = 'module'){
    // set repo_dir according to module type.
    if($type == 'template'){
        $repo_dir = _COS_PATH . "/htdocs/templates/$module_name";
    } else if ($type == 'profile'){
        $repo_dir = _COS_PATH . "/profiles/$module_name";
    } else {
        $repo_dir = _COS_PATH . "/modules/$module_name";
    }
    return $repo_dir;
}

/**
 * function for cloning a template repo
 * @param array $options
 */
function git_clone_template($options){
    
    git_clone($options, 'template');
    
    $template = get_module_name_from_repo($options['repo']);
    $options['template'] = $template;
    $str = install_template($options, true);
    cos_cli_print($str);
    return;
}

/**
 * function for cloning a profile repo
 * @param array $options
 */
function git_clone_profile($options){
    git_clone($options, 'profile');
}



/**
 * function used for cloning a repo
 * @param array $options
 * @param string $type
 */

function git_clone($options, $type){
    // get latest repo tag
    $latest = latest_tag($options['repo']);
    
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
    
    if (isset(register::$vars['git_use_master'])){
        $checkout = 'master';
    }

    if (isset($options['master'])){
        $checkout = 'master';
    }

    // set dir according to module type. Template, profile or module.
    if ($type == 'template'){
        $clone_path = _COS_PATH . "/htdocs/templates";
    } else if ($type == 'profile'){
        $clone_path = _COS_PATH . "/profiles";
    } else {
        $clone_path = _COS_PATH . "/modules";
    }

    // create path if it does not exists
    if (!file_exists($clone_path)){
        mkdir($clone_path);
    }

    
    $module_name = get_module_name_from_repo($options['repo']);
    $module_path = "$clone_path/$module_name";

    // if dir exists we check if it is a git repo
    // or just a directory
    if (file_exists($module_path)){
        // Repo exists or
        // at least same dir name exists.
        // cos_cli_print("$clone_command failed.");
        $repo_dir = $clone_path . "/$module_name";

        // check if path is a git repo
        $git_folder = $repo_dir . "/.git";


        if (file_exists($git_folder)){
            // repo exists. We pull changes and set version
            $git_command = "cd $repo_dir && git checkout $checkout";
        } else {
            // no git repo - empty dir we presume.
            $git_command = "cd $clone_path && git clone $options[repo] && cd $module_name && git checkout $checkout";
        }
        $ret = cos_system($git_command);
    } else {
        $git_command = "cd $clone_path && git clone $options[repo] && cd $module_name && git checkout $checkout";
        $ret = cos_system($git_command);
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
function git_use_master ($options){
    register::$vars['git_use_master'] = 1;
}

/**
 * get latest tag for modules and templates and
 * upgrade according to latest tag
 * @param   array   options from cli env
 */
function git_upgrade_all ($options){

    $modules = profile::getModules();
    foreach ($modules as $key => $val){
        if (isset(register::$vars['git_use_master'])){
            $tag = 'master';
        } else {
            $tag = latest_tag($val['public_clone_url'], true);
        }

        if ( ($tag == 'master') OR  ($tag > $val['module_version'])) {
            
            cos_cli_print("Will upgrade $val[module_name]");
            git_upgrade ($val, $tag, 'module');
        }
    }
    
    $templates = profile::getAllTemplates();

    foreach ($templates as $key => $val){
        if (isset(register::$vars['git_use_master'])){
            $tag = 'master';
        } else {
            $tag = latest_tag($val['public_clone_url'], true);
        }

        if ( ($tag == 'master') OR  ($tag > $val['module_version'])) {
            git_upgrade ($val, $tag, 'template');
        }
    }
}




/**
 * function for adding and commiting all modules and templates
 * @param   array   options from cli env
 */
function git_commit_all ($options){

    $modules = profile::getModules();
    foreach ($modules as $key => $val){
        _git_commit($val, 'module');

    }

    $templates = profile::getAllTemplates();

    foreach ($templates as $key => $val){
        _git_commit($val, 'template');
    }
}

/**
 * function for adding and commiting all modules and templates
 * @param   array   options from cli env
 */
function git_tag_all ($options){

    $version = cos_readline('Enter tag version to use ');

    $modules = profile::getModules();
    foreach ($modules as $key => $val){
        $val['new_version'] = $version;
        _git_tag($val, 'module');

    }

    $templates = profile::getAllTemplates();

    foreach ($templates as $key => $val){
        $val['new_version'] = $version;
        _git_tag($val, 'template');
    }
}

/**
 * function for tagging a module or all modules
 * @param array $val
 * @param string $typ (template or module)
 * @return type 
 */
function _git_tag ($val, $type = 'module'){
    $repo_path = get_repo_path($val['module_name'], $type);

    if (!_git_is_repo ($repo_path)){
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

    $git_command = "cd $repo_path && git tag -a \"$val[new_version]\"";
    proc_close(proc_open($git_command, array(0 => STDIN, 1 => STDOUT, 2 => STDERR), $pipes));

    $git_command = "cd $repo_path && git push --tags $val[private_clone_url]";
    passthru($git_command);

    print "\n---\n";
}

function git_tag_all_files ($options){
    $version = cos_readline('Enter tag version to use ');

    $modules = profile::getModules();
    foreach ($modules as $key => $val){
        $val['Module'] = $val['module_name'];
        $val['new_version'] = $version;
        _git_tag_install_file($val, 'module');
    }

    $templates = profile::getAllTemplates();
    foreach ($templates as $key => $val){
        $val['Module'] = $val['module_name'];
        $val['new_version'] = $version;
        _git_tag_install_file($val, 'template');
    }
}

function _git_tag_install_file ($val, $type = 'module'){
    $repo_path = get_repo_path($val['module_name'], $type);

    if (!_git_is_repo ($repo_path)){
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
function _git_commit ($val, $type = 'module'){
    $repo_path = get_repo_path($val['module_name'], $type);

    if (!_git_is_repo ($repo_path)){
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

    
    cos_cli_print ("Will commit $val[module_name]");
    
    $git_command = "cd $repo_path && git add . ";
    passthru($git_command);

    $git_command = "cd $repo_path && git commit ";
    proc_close(proc_open($git_command, array(0 => STDIN, 1 => STDOUT, 2 => STDERR), $pipes));

    $git_command = "cd $repo_path && git push $val[private_clone_url]";
    passthru($git_command);
    print "\n";
}

/**
 * function for upgrading a module, template or profile according to latest tag
 * or master
 *
 * @param array     with module options
 * @param string    tag with wersion or 'master'
 * @param string    module, templatee or profile. 
 */
function git_upgrade ($val, $tag = 'master', $type = 'module'){
    
    //print_r($val);
    if (!isset($val['module_name'])) {
        $val['module_name'] = get_module_name_from_repo($val['repo']);
    }
    $repo_path = get_repo_path($val['module_name'], $type);
    
    if (!_git_is_repo ($repo_path)){
        cos_cli_print("$repo_path is not a git repo");
        return;
    }

    $git_command = "cd $repo_path && git checkout master && git pull && git fetch --tags && git checkout $tag";
    $ret = cos_system($git_command);
    
    if ($type == 'module'){
        // sorry - but it is called with a diffrent name in the upgrade_module
        // function ...
        $val['Module'] = $val['module_name'];

        // when version is set to null we upgrade to latest possible
        $val['Version'] = null;
        upgrade_module($val);
    }
    
    if ($type == 'template'){
        // TODO: What to do on template upgrade. 
        //$val['Module'] = $val['module_name'];

        // when version is set to null we upgrade to latest possible
        //$val['Version'] = null;
        //upgrade_module($val);
    }

    // no need to upgrade template, as there is no sql. 
    // print "\n---\n";
}

function _git_is_repo($path){
    $repo = $path . "/.git";
    if (!file_exists($repo)){
        return false;
    }
    return true;
}

function git_no_questions (){
    cos_confirm_readline(null, 1);
}



mainCli::setCommand('git', array(
    'description' => 'Commands for using git. Can be used for installing and uninstalling modules from git repos (remote)',
));

mainCli::setOption('git_no_questions', array(
    'long_name'   => '--silence',
    'short_name'   => '-s',
    'description' => 'Will ask [y] to all questions raised',
    'action'      => 'StoreTrue'
));

mainCli::setOption('git_use_master', array(
    'long_name'   => '--master',
    'short_name'   => '-m',
    'description' => 'Will use master. Valid for all-up',
    'action'      => 'StoreTrue'
));

mainCli::setOption('git_install', array(
    'long_name'   => '--mod-in',
    'description' => 'Will clone specified remote url with latest version',
    'action'      => 'StoreTrue'
));

mainCli::setOption('git_clone_template', array(
    'long_name'   => '--temp-in',
    'description' => 'Will install remote clone url with latest version',
    'action'      => 'StoreTrue'
));

mainCli::setOption('git_upgrade_template', array(
    'long_name'   => '--temp-up',
    'description' => 'Will check latest remote version and compare with locale version. If remote is higher it will be checked out',
    'action'      => 'StoreTrue'
));

mainCli::setOption('git_clone_profile', array(
    'long_name'   => '--pro-in',
    'description' => 'Will clone remote profile with latest version',
    'action'      => 'StoreTrue'
));

mainCli::setOption('git_upgrade', array(
    'long_name'   => '--mod-up',
    'description' => 'Will check latest remote version and compare with locale version. If remote is higher it will be checked out and system will be upgraded',
    'action'      => 'StoreTrue'
));

mainCli::setOption('git_upgrade_all', array(
    'long_name'   => '--all-up',
    'description' => 'Will check latest remote versions of modules, templates and profiles, and compare with locale version. If remote is higher it will be checked out and system will be upgraded',
    'action'      => 'StoreTrue'
));

mainCli::setOption('git_commit_all', array(
    'long_name'   => '--all-commit',
    'description' => 'Will try and commit all modules and templates in one try.',
    'action'      => 'StoreTrue'
));

mainCli::setOption('git_tag_all', array(
    'long_name'   => '--all-tag',
    'description' => 'Will tag and push tags for all modules and templates in one try.',
    'action'      => 'StoreTrue'
));

mainCli::setOption('git_tag_all_files', array(
    'long_name'   => '--all-tag-files',
    'description' => 'Will tag and push tags for all modules and templates in one try.',
    'action'      => 'StoreTrue'
));

mainCli::setOption('remote_tags', array(
    'long_name'   => '--remote-tags',
    'description' => 'Will list remote tags',
    'action'      => 'StoreTrue'
));



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
