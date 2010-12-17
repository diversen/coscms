<?php



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

/**
 * following function are sligtly modified from:
 * https://github.com/troelskn/pearhub
 *
 * @param   string  a git url url
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


function remote_tags ($options){
    $tags = get_tags($options['repo']);
    print_r($tags);
    $latest = latest_tag();
    print_r($latest);
}

/**
 * function for cloning a git repo
 * note: No sql will be installed. You will need to run
 * command ./coscli.sh module --install module_name
 *
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
        install_module($options);
    }
}


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
}

/**
 * function for cloning a profile repo
 * @param array $options
 */
function git_clone_profile($options){
    git_clone($options, 'profile');
}





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
        cos_cli_abort("No tags for repo: $options[repo]");
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

function git_use_master ($options){
    register::$vars['git_use_master'] = 1;
}

/**
 * get latest tag for all module and templates and upgrade according to latest tag
 */
function git_upgrade_all ($options){

    $modules = profile::getModules();
    foreach ($modules as $key => $val){
        print_r($val);
        if (isset(register::$vars['git_use_master'])){
            $tag = 'master';
        } else {
            echo $tag = latest_tag($val['public_clone_url'], true);
            sleep(3);
        }

        if ( ($tag == 'master') OR  ($tag > $val['module_version'])) {
            _git_upgrade ($val, $tag, 'module');
        }
    }
    $templates = profile::getAllTemplates();
}

/**
 *
 * function for upgrading a module repo.
 * @private
 * @param array $options
 */
function _git_upgrade ($val, $tag, $type = 'module'){
    $repo_path = get_repo_path($val['module_name'], $type);
    $git_command = "cd $repo_path && git pull $val[public_clone_url] master && git checkout $tag";
    $ret = cos_system($git_command);
    if ($type == 'module'){
        // sorry - but it is called with a diffrent name in the upgrade_module
        // function ...
        $val['Module'] = $val['module_name'];

        // when version is set to null we upgrade to latest possible
        $val['Version'] = null;
        upgrade_module($val);
    }
}





mainCli::setCommand('git', array(
    'description' => 'Commands for using git',
));

mainCli::setOption('git_use_master', array(
    'long_name'   => '--master',
    'description' => 'Will create profile with master repos for all modules and templates',
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
    array('description'=> 'Optional: Specify the version to checkout e.g. master or 1.11',
        'optional' => true,
));
