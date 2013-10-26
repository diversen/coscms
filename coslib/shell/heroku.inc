<?php

function heroku_get_setting ($setting) {
    exec("heroku config:get $setting", $ary, $ret);
    if ($ret) {
        echo "We could not get cleardb url\n";
        return false;
    }
    return $ary[0];
}

function heroku_set_cleardb_conf () {
    $url_str = heroku_get_setting('CLEARDB_DATABASE_URL');
    $url = parse_url($url_str);
    if ($url) {
        
        $config_file = heroku_get_config_filename();
        config::$vars['coscms_main'] = config::getIniFileArray($config_file, true);
        
        // assemble configuration info
        $database = $str = substr($url['path'], 1);
        config::$vars['coscms_main']['url'] = "mysql:dbname=$database;host=$url[host];charset=utf8";
        config::$vars['coscms_main']['username'] = $url['user'];
        config::$vars['coscms_main']['password'] = $url['pass'];
        
        $content = config::arrayToIniFile(config::$vars['coscms_main'], false);
        $path = _COS_PATH . "/config/config.ini";
        file_put_contents($path, $content);
    }
}

function heroku_get_config_filename () {
    $config_file = _COS_PATH . "/config/config.ini";
    if (!file_exists($config_file)) {
        $config_file = $config_file . "-dist";
    }
    return $config_file;
}

function heroku_set_sendgrid_conf () {
    $user = heroku_get_setting('SENDGRID_USERNAME');
    $pass = heroku_get_setting('SENDGRID_PASSWORD');
    if ($user && $pass) {
        
        $config_file = heroku_get_config_filename();
        config::$vars['coscms_main'] = config::getIniFileArray($config_file, true);
        $from_text = cos_readline('Enter which from text should be seen in his inbx, e.g. CosCMS (not the email)');
        $reply = cos_readline('Enter which email users should reply to (an email):');
        
        config::$vars['coscms_main']['site_email'] = "$from_text <$user>"; 
        config::$vars['coscms_main']['site_email_reply'] = "$from_text <$reply>"; 
        config::$vars['coscms_main']['smtp_params_host'] = "smtp.sendgrid.net";
        config::$vars['coscms_main']['smtp_params_sender'] = $user;
        config::$vars['coscms_main']['smtp_params_username'] = $user;
        config::$vars['coscms_main']['smtp_params_password'] = $pass;
        config::$vars['coscms_main']['smtp_params_auth'] = "true";
        config::$vars['coscms_main']['smtp_params_port'] = 587;
        $content = config::arrayToIniFile(config::$vars['coscms_main'], false);
        $path = _COS_PATH . "/config/config.ini";
        file_put_contents($path, $content);
    }
}

function heroku_set_servername () {
    //echo dirname(__FILE__);
    $cur_dir = explode('\\', getcwd());
    echo $cur_dir[count($cur_dir)-1];
}



mainCli::setCommand('heroku', array(
    'description' => 'Commands for heroku',
));

mainCli::setOption('heroku_set_servername', array(
    'long_name'   => '--set-servername',
    'description' => 'Sets correct servername in config/config.ini',
    'action'      => 'StoreTrue'
));


mainCli::setOption('heroku_set_cleardb_conf', array(
    'long_name'   => '--set-clear-db-config',
    'description' => 'When you have set up cleardb you can set the config/config.ini file with correct url, user. password',
    'action'      => 'StoreTrue'
));

mainCli::setOption('heroku_set_sendgrid_conf', array(
    'long_name'   => '--set-sendgrid-config',
    'description' => 'When you have set up sendgrid you can set the config/config.ini file with correct user, password, email, and reply to',
    'action'      => 'StoreTrue'
));

