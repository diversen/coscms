<?php

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

    // set repo_dir according to module type.
    if($type == 'template'){
        $repo_dir = _COS_PATH . "/htdocs/templates/$module_name";
    } else if ($type == 'profile'){
        $repo_dir = _COS_PATH . "/profiles/$module_name";
    } else {
        $repo_dir = _COS_PATH . "/modules/$module_name";
    }

    $ret = git_clone ($options, $type);
    if ($type == 'module'){
        install_module($options);
    }
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

/**
 * function for upgrading a module repo.
 * @param array $options
 */
function git_upgrade ($options){
    $options['Module'] = get_module_name_from_repo($options['repo']);
    if (!$options['Module']){
        // test if the 'repo' is a module name
        // and fetch repo info from install.inc
        $module_path = _COS_PATH . '/modules/' . $options['repo'];
        if (file_exists($module_path)){
            // most likely a repo in a path
            // module exists, compare with remote server
            $options['Module'] = $options['repo'];
            $upgrade = new moduleInstaller($options);

            if (!isset($upgrade->installInfo['PUBLIC_CLONE_URL'])){
                cos_cli_abort("\$_INSTALL['PUBLIC_CLONE_URL'] needs to be set in install.inc in order to upgrade from a module name " );
            }
            
            $options['repo'] = $upgrade->installInfo['PUBLIC_CLONE_URL'];

        } else {
            cos_cli_abort($options['repo'] . " is not a module in module_path or a git repo");
        }
    }

    upgrade_module ($options);

}

function git_upgrade_all (){

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
function get_tags($url = null) {
    static $tags;
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
function latest_tag($repo) {
    $tags = get_tags($repo);
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

mainCli::setCommand('git', array(
    'description' => 'Commands for using git',
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

mainCli::setOption('remote_tags', array(
    'long_name'   => '--remote-tags',
    'description' => 'Will list remote tags',
    'action'      => 'StoreTrue'
));

mainCli::setArgument(
    'repo',
    array('description'=> 'Specify the git repo to be used',
        'optional' => false,
));

mainCli::setArgument(
    'version',
    array('description'=> 'Optional: Specify the version to checkout e.g. master or 1.11',
        'optional' => true,
));
