<?php

function heroku_get_setting ($setting) {
    exec("heroku config:get $setting", $ary, $ret);
    if ($ret) {
        echo "We could not get cleardb url\n";
        return false;
    }
    return $ary[0];
}

function heroku_download_extensions () {
    $command = "wget https://github.com/wuputah/heroku-libraries/raw/master/php/mbstring/mbstring.so";
    exec($command, $ary, $ret);
    if ($ret) {
        echo "We could not download mbstring.so\n";
        return false;
    }
    
    $command = "wget https://github.com/alkhoo/heroku-cedar-php-extension/raw/master/ext/apc.so";
    exec($command, $ary, $ret);
    if ($ret) {
        echo "We could not download apc.so\n";
        return false;
    }
    
    $command = "wget https://github.com/alkhoo/heroku-cedar-php-extension/raw/master/ext/imagick.so";
    exec($command, $ary, $ret);
    if ($ret) {
        echo "We could not download imagick.so\n";
        return false;
    }
    
    $php_ini = <<<EOF
extension=/app/www/mbstring.so
extension=/app/www/imagick.so
apc.rfc1867 = 1
EOF;
    file_put_contents('php.ini', $php_ini);
}

function heroku_enable_addons () {
    $command = "heroku addons:add cleardb";
    heroku_set_cleardb_conf();
    $command = "heroku addons:add sendgrid";
    heroku_set_sendgrid_conf();
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
        $config_file = _COS_PATH . "/scripts/heroku.ini";
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

function heroku_install () {
        install_from_profile(array ('profile' => 'default'));
}

function heroku_prompt_install () {
    cos_exec('git clone git clone git://github.com/diversen/coscms-package.git');
    cos_exec("cp -rf coscms-package/* . ");
    heroku_enable_addons();
    heroku_download_extensions();
    cos_exec("cp misc/htaccess .htaccess");
    cos_exex("mkdir -r files/default");
    cos_exex("chmod -R 777 files");
    cos_exec("touch files/default/dummy.txt");
    load_db_default();
    cos_cli_print('Installing all modules. This may take minutes. Be patient'); 
    install_from_profile(array ('profile' => 'default'));
    cos_exec("./coscli.sh useradd --add");
    
}

mainCli::setCommand('heroku', array(
    'description' => 'Commands for heroku',
));

mainCli::setOption('heroku_set_servername', array(
    'long_name'   => '--set-servername',
    'description' => 'Sets correct servername in config/config.ini',
    'action'      => 'StoreTrue'
));

mainCli::setOption('heroku_prompt_install', array(
    'long_name'   => '--prompt-install',
    'description' => 'Sets correct servername in config/config.ini',
    'action'      => 'StoreTrue'
));

mainCli::setOption('heroku_install', array(
    'long_name'   => '--install',
    'description' => 'Install default profile',
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

