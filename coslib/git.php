<?php

/**
 * file contians functions used with git.
 * @package git
 */

/**
 * get module name from repo name
 * @param   string    $repo
 * @return  string    module name
 */
function git_module_name_from_repo ($repo){
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
 * get tags local
 * @return type 
 */
function git_coscms_tags_local (){
    $command = "git tag -l";
    $ret = exec($command, $output);
    return cos_parse_shell_output($output);
}

/**
 * following function are sligtly modified from:
 * https://github.com/troelskn/pearhub
 *
 * @param   string  $url a git url
 * @param   mixed   $clear set this and tags will not be cached in static var
 * @return  array   $ary array of remote tags
 */
function git_get_remote_repo_tags($url = null, $clear = null) {
    static $tags = null;

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
function git_get_latest_tag($repo, $clear = null) {
    $tags = git_get_remote_repo_tags($repo, $clear);
    if (count($tags) > 0) {
        sort($tags);
        return $tags[count($tags) - 1];
    }
    return null;
}

/** 
 * gets contents of .git/config file as array
 * @param string $repo_path path to repo. 
 * @return array $ary contents of config as array 
 */
function git_get_local_config ($repo_path) {
    $conf = parse_ini_file($repo_path . "/.git/config");
    print_r($conf);
}
