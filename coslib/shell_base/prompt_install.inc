<?php



/**
 * File containing documentation functions for shell mode
 *
 * @package     shell
 */

/**
 * function for doing a prompt install from shell mode
 * is a wrapper around other shell functions.
 */
function prompt_install(){
    if (defined('NO_CONFIG_FILE')){
        cos_cli_print("No config file (config/config.ini) is loaded");
    }

    cos_cli_print('The following tags can be used:');

    $tags = '';
    $tags.= git_coscms_tags_local ();
    $tags.= "master";

    cos_cli_print ($tags);
    $tag = cos_readline("Enter tag (version) to use:");

    cos_exec("git checkout $tag");

    $profiles = file::getFileList('profiles', array('dir_only' => true));
    cos_cli_print("List of profiles: ");
    foreach ($profiles as $val){
        cos_cli_print("\t".$val);
    }

    // select profile and load it
    $profile = cos_readline('Enter profile, and hit return: ');
    load_profile(array('profile' => $profile, 'config_only' => true));
    cos_cli_print("Main config file (config/config.ini) for $profile is loaded");

    // load profile.
    config::$vars['coscms_main'] = config::getIniFileArray(_COS_PATH . '/config/config.ini', true);
    
    // get configuration info
    $host = cos_readline('Enter mysql host, and hit return: ');
    $database = cos_readline('Enter database name, and hit return: ');
    $username = cos_readline('Enter database user, and hit return: ');
    $password = cos_readline('Enter database users password, and hit return: ');
    $server_name = cos_readline('Enter server host name (e.g. www.coscms.org), and hit return: ');
    //$site_email = cos_readline('Enter email (site wide) to send messages from, and hit return: ');
    
    // questions for creating a super user
    //$super_user = cos_readline('Enter superuser username, and hit return: ');
    //$super_password = cos_readline('Enter superuser password, and hit return: ');
    //$super_email = cos_readline('Enter superuser email, and hit return: ');

    // assemble configuration info
    config::$vars['coscms_main']['url'] = "mysql:dbname=$database;host=$host";
    config::$vars['coscms_main']['username'] = $username;
    config::$vars['coscms_main']['password'] = $password;
    config::$vars['coscms_main']['server_name'] = $server_name;
    //config::$vars['coscms_main']['site_email'] = $server_name;

    // write it to ini file
    $content = config::arrayToIniFile(config::$vars['coscms_main']);
    $path = _COS_PATH . "/config/config.ini";
    file_put_contents($path, $content);

    // install profile.
    $confirm_mes = "Configuration rewritten (config/config.ini). Many more options can be set here, so check it out at some point.";
    $confirm_mes.= "Will now install system ... ";
    
    cos_cli_print($confirm_mes);

    $options = array();
    $options['profile'] = $profile;
    if ($tag == 'master'){
        $options['master'] = true;
    }

    install($options);

    // we can then add user when database is created
    //include_module ("account/create", array('load_language' => true));
    //$account = new accountCreate(false);
    /*
    $values = array(
                'username'=> $super_user,
                'password' => md5($super_password),
                'email' => $super_email,
                'verified' => 1,
                'admin' => 1,
                'super' => 1);
    $account->createSystemUser($values);
    cos_cli_print("If no critical errors, system should be installed.");*/
    useradd_add();
    $login = "http://$server_name/account/login/index";
    cos_cli_print("You are now able to log in: At $login");
}

function get_password(){
    $site_password = cos_readline('Enter system user password, and hit return: ');
    $site_password2 = cos_readline('Retype system user password, and hit return: ');
    if ($site_password == $site_password2){
        return $site_password;
    } else {
        get_password();
    }
}



mainCli::setCommand('prompt-install', array(
    'description' => 'Commands for doing a prompt install with questions asked and answered',
));


mainCli::setOption('prompt_install', array(
    'long_name'   => '--install',
    'description' => 'Will prompt user for install info',
    'action'      => 'StoreTrue'
));

