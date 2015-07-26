<?php

/**
 * get a ini setting from heroku
 * @param string $setting
 * @return string
 */
function heroku_get_setting ($setting) {
    exec("heroku config:get $setting", $ary, $ret);
    if ($ret) {
        echo "We could not get setting:$setting\n";
        return false;
    }
    return $ary[0];
}

/**
 * enable addons cleardb and sendgrid.
 */
function heroku_enable_addons () {
    $command = "heroku addons:add cleardb";
    proc_close(proc_open($command, array(0 => STDIN, 1 => STDOUT, 2 => STDERR), $pipes));    
    heroku_set_cleardb_conf();
    $command = "heroku addons:add sendgrid";
    proc_close(proc_open($command, array(0 => STDIN, 1 => STDOUT, 2 => STDERR), $pipes));
    heroku_set_sendgrid_conf();
}

/**
 * sets config.ini with correct database config
 */
function heroku_set_cleardb_conf () {
    $url_str = heroku_get_setting('CLEARDB_DATABASE_URL');
    $url = parse_url($url_str);
    if ($url) {        
        $config_file = heroku_get_config_filename();
        conf::$vars['coscms_main'] = conf::getIniFileArray($config_file, true);
        
        // assemble configuration info
        $database = $str = substr($url['path'], 1);
        conf::$vars['coscms_main']['url'] = "mysql:dbname=$database;host=$url[host];charset=utf8";
        conf::$vars['coscms_main']['username'] = $url['user'];
        conf::$vars['coscms_main']['password'] = $url['pass'];
        
        $content = conf::arrayToIniFile(conf::$vars['coscms_main'], false);
        $path = _COS_PATH . "/config/config.ini";
        file_put_contents($path, $content);
    }
}

/**
 * get name of config file
 * @return string
 */
function heroku_get_config_filename () {
    $config_file = _COS_PATH . "/config/config.ini";
    if (!file_exists($config_file)) {
        $config_file = _COS_PATH . "/config/config.ini-dist";
    }
    return $config_file;
}

/**
 * set csendgrid config in config.ini
 */
function heroku_set_sendgrid_conf () {
    $user = heroku_get_setting('SENDGRID_USERNAME');
    $pass = heroku_get_setting('SENDGRID_PASSWORD');
    if ($user && $pass) {
        
        $config_file = heroku_get_config_filename();
        conf::$vars['coscms_main'] = conf::getIniFileArray($config_file, true);
        $from_text = cos_readline('Enter which from text should be seen in his inbx, e.g. CosCMS (not the email)');
        $reply = cos_readline('Enter which email users should reply to (an email):');
        
        conf::$vars['coscms_main']['site_email'] = "$from_text <$user>"; 
        conf::$vars['coscms_main']['site_email_reply'] = "$from_text <$reply>"; 
        conf::$vars['coscms_main']['smtp_params_host'] = "smtp.sendgrid.net";
        conf::$vars['coscms_main']['smtp_params_sender'] = $user;
        conf::$vars['coscms_main']['smtp_params_username'] = $user;
        conf::$vars['coscms_main']['smtp_params_password'] = $pass;
        conf::$vars['coscms_main']['smtp_params_auth'] = "true";
        conf::$vars['coscms_main']['smtp_params_port'] = 587;
        $content = conf::arrayToIniFile(conf::$vars['coscms_main'], false);
        $path = _COS_PATH . "/config/config.ini";
        file_put_contents($path, $content);
    }
}

/**
 * prompt install command
 */
function heroku_prompt_install () {
    $res = cos_exec("which heroku");
    if ($res) {
        die('You wll need the heroku command. Download the heroku toolbelt');
    }
    echo "Enabling addons ... wait\n";
    heroku_enable_addons();
    cos_exec("cp misc/htaccess .htaccess");
    cos_exec("mkdir -p files/default");
    cos_exec("chmod -R 777 files");
    cos_exec("touch files/default/dummy.txt");
    load_db_default();
    cos_cli_print('Installing all modules. This may take a few minutes. Be patient'); 
    install_from_profile(array ('profile' => 'default'));
    useradd_add();    
}

self::setCommand('heroku', array(
    'description' => 'Commands for heroku',
));

self::setOption('heroku_prompt_install', array(
    'long_name'   => '--prompt-install',
    'description' => 'Prompt install. Writes config/config.ini, and setup sendgrid',
    'action'      => 'StoreTrue'
));

self::setOption('heroku_set_cleardb_conf', array(
    'long_name'   => '--set-clear-db-config',
    'description' => 'When you have set up cleardb you can set the config/config.ini file with correct url, user. password',
    'action'      => 'StoreTrue'
));

self::setOption('heroku_set_sendgrid_conf', array(
    'long_name'   => '--set-sendgrid-config',
    'description' => 'When you have set up sendgrid you can set the config/config.ini file with correct user, password, email, and reply to',
    'action'      => 'StoreTrue'
));
