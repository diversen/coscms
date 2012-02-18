<?php

/**
 * File containing function for chown and chmod files to sane settings
 *
 * @package     shell
 */

// {{{ chmod_files()
/**
 * function for changing owner and group to correct and safe settings.
 *
 * we read which user the web server is running under by fetching
 * the whoami script from the server. the owner will be the user running the
 * script. Public upload dir /htdocs/files will then be set to 770 with correct
 * user and group
 *
 * @return int  value from exec command
 */
function chmod_files(){
    $url = 'http://' . config::$vars['coscms_main']['server_name'] . '/whoami.php';
    $handle = fopen($url, "r");
    if ($handle) {
        while (!feof($handle)) {
            $group = fgets($handle, 4096);
        }
    } else {
        die ("Could not open the url: $url\nGuess you need to change server name in config/config.ini");
    }
    fclose($handle);

    // current owner
    $owner = exec('whoami');

    $files_path = _COS_PATH . '/htdocs/files ';
    $files_path.= _COS_PATH . '/logs/coscms.log ';
    $files_path.= _COS_PATH . '/config/multi';
    $command = "sudo chown -R $owner:$group $files_path";
    cos_exec($command);
    $command = "sudo chmod -R 770 $files_path";
    cos_exec($command);
}

// }}}
// {{{ chmod_files_to_owner()
/**
 * function for changing all files to be onwed by user.
 *
 * Change file to owned by owner
 * Public files /htdocs/files will then be set to 777
 *
 * @return int  value from exec command
 */
function chmod_files_to_owner(){
    // current owner
    $owner = exec('whoami');
    $files_path = _COS_PATH . '/htdocs/files ';
    $command = "sudo chown -R $owner:$owner $files_path";
    cos_exec($command);
    $command = "sudo chmod -R 770 $files_path";
    cos_exec($command);
}

// }}}
// {{{ rm_files()
/**
 * function for removing all files in htdocs/files/*, htdocs/logo/*
 * when doing an install
 *
 * @return int  value from exec command
 */
function rm_files(){
    $files_path = _COS_PATH . '/htdocs/files/* ';
    $command = "sudo rm -Rf $files_path";
    cos_exec($command);
}
// }}}
// {{{ function create_files()
/**
 * function for removing all files in htdocs/files/*, htdocs/logo/*
 * when doing an install
 *
 * @return int  value from exec command
 */
function create_files(){
    $files_path = _COS_PATH . '/logs/coscms.log';
    if (!file_exists($files_path)){
        $command = "touch $files_path";
        cos_exec($command);
    }

    $files_path = _COS_PATH . '/htdocs/files';
    if (!file_exists($files_path)){
        $command = "mkdir $files_path";
        cos_exec($command);
    }
    
    $domain = config::getDomain();
    $files_path = _COS_PATH . "/htdocs/files/$domain";
    
    if (!file_exists($files_path)){
        $command = "mkdir $files_path";
        cos_exec($command);
    }
    
}

// }}}

mainCli::setCommand('file', array(
    'description' => 'Basic files commands',
));

mainCli::setOption('chmod_files', array(
    'long_name'   => '--chmod-files',
    'description' => 'Will try to chmod and chown of htdocs/files',
    'action'      => 'StoreTrue'
));

mainCli::setOption('chmod_files_to_owner', array(
    'long_name'   => '--chmod-files-owner',
    'description' => 'Will try to chmod and chown of htdocs/files to current user',
    'action'      => 'StoreTrue'
));

mainCli::setOption('rm_files', array(
    'long_name'   => '--rm-files',
    'description' => 'Will remove files in htdocs/files and in htdocs/logo',
    'action'      => 'StoreTrue'
));

mainCli::setOption('create_files', array(
    'long_name'   => '--create-files',
    'description' => 'Will create log file: log/coscms.log',
    'action'      => 'StoreTrue'
));

