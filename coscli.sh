#!/usr/bin/env php
<?php

include_once "vendor/autoload.php";

use diversen\autoloader\modules;
use diversen\conf;
use diversen\file;
use diversen\intl;
use diversen\lang;
use diversen\log;
use diversen\minimalCli;
use diversen\db\connect;
use diversen\moduleloader;
use diversen\db;
use diversen\db\q;
use diversen\db\admin;
use diversen\cli\common;

$m = new modules();
$m->autoloadRegister();

$path = dirname(__FILE__);
conf::setMainIni('base_path', $path);

bootCli();



$cli = new minimalCli();
$cli->header = 'CosCMS Commandline Tool';

// Add commands
$commands = [];
$commands['apache2'] =          new \diversen\commands\apache2Command();
$commands['backup'] =           new \diversen\commands\backup();
$commands['build'] =            new \diversen\commands\build();
$commands['cache'] =            new \diversen\commands\cache();
$commands['cron'] =             new \diversen\commands\cron();
$commands['db'] =               new \diversen\commands\dbCommand();
$commands['dev'] =              new \diversen\commands\dev();
$commands['git'] =              new \diversen\commands\gitCommand();
$commands['structure'] =        new \diversen\commands\structure();
$commands['file'] =             new \diversen\commands\fileSystem();
$commands['install'] =          new \diversen\commands\install();
$commands['module'] =           new \diversen\commands\module();
$commands['template'] =         new \diversen\commands\template();
$commands['translate'] =        new \diversen\commands\translateCommand();
$commands['g-translate'] =      new \diversen\commands\googleTranslate();
$commands['profile'] =          new \diversen\commands\profileCommand();
$commands['prompt-install'] =   new \diversen\commands\promptInstall();
$commands['useradd'] =          new \diversen\commands\useradd();
$commands['upgrade'] =          new \diversen\commands\upgrade();



$res = dbConn();
if ($res) {
    // echo "OK";
    $module_commands = getModuleCommands();
    // print_r($module_commands);die;
    $commands = array_merge($commands, $module_commands);
}


$cli->commands = $commands;
$cli->runMain();

//mainCli::init();
//$ret = mainCli::run();
//exit($ret);


function bootCLi() {
    // Autoload modules
    $m = new modules();
    $m->autoloadRegister();

    // Define all essential paths. 
    // base_path has been enabled, and based on this we 
    // set htdocs_path, modules_path, files_dir
    conf::defineCommon();

    // Set include paths - based on config.ini
    // enable modules_path base_path as include_dirs
    conf::setIncludePath();

    // Load config file 
    conf::load();

    // set public file folder in file
    file::$basePath = conf::getFullFilesPath();

    // Set log level - based on config.ini

    $log_file = conf::pathBase() . '/logs/system.log';
    log::setErrorLogFile($log_file);
    if (conf::getMainIni('debug')) {
        log::enableDebug();
    }

    // Set locales
    intl::setLocale();

    // Set default timezone
    intl::setTimezone();

    // Enable translation
    $l = new lang();

    // Load all language files
    $base = conf::pathBase();
    $l->setDirsInsideDir("$base/modules/");
    $l->setDirsInsideDir("$base/htdocs/templates/");
    $l->setSingleDir("$base/vendor/diversen/simple-php-classes");
    $l->loadLanguage(conf::getMainIni('lang'));
}

function dbConn() {
    $db_conn = array(
        'url' => conf::getMainIni('url'),
        'username' => conf::getMainIni('username'),
        'password' => conf::getMainIni('password'),
        'db_init' => conf::getMainIni('db_init'),
        'dont_die' => 1
    );

    $ret = connect::connect($db_conn);
    if ($ret == 'NO_DB_CONN') {
        return false;
    }
    return true;
}

function tablesExists () {
            $db = new db();
        $ret = $db->connect(array('dont_die' => 1));
        if ($ret == 'NO_DB_CONN'){
            return false;
        }

        $info = admin::getDbInfo(conf::getMainIni('url'));
        if (!$info) {
            common::echoMessage('No databse url in config.ini');
        }
        if ($info['scheme'] == 'mysql' || $info['scheme'] == 'mysqli') {
            $rows = $db->selectQuery("SHOW TABLES");
            if (empty($rows)){
                return false;
            }
            return true;
        }

        if ($info['scheme'] == 'sqlite')  {
            $sql = "SELECT name FROM sqlite_master WHERE type='table' AND name='modules'";
            $rows = $db->selectQuery($sql);

            if (empty($rows)){
                return false;
            }
            return true;
        }

}

function getModuleCommands (){

    if (!tablesExists()) {
        common::echoMessage('No tables exists. We can not load modules in modules/ dir');
        return;
    }

    $ml = new moduleloader();

    // select all db settings and merge them with ini file settings
    $db_settings = [];
    if (admin::tableExists('settings')) {
        $db_settings = q::select('settings')->filter('id =', 1)->fetchSingle();
    }
    // merge db settings with config/config.ini settings
    // db settings override ini file settings
    conf::$vars['coscms_main'] = array_merge(conf::$vars['coscms_main'], $db_settings);

    $commands = [];
    $modules = moduleloader::getAllModules();
    
    foreach ($modules as $val) {
        // moduleloader::setModuleIniSettings($val['module_name']);
        $path = conf::pathModules() . "/$val[module_name]/command.php";
        if (file_exists($path)) {

            $class = '\modules\\' . "$val[module_name]\command";
            $obj = new $class();
            // die;
            $help = $obj->getHelp();
            $name = $help['name'];
            
            
            $commands[$name] = $obj;
        }
    }

    // Override any setting with configdb setting if module exists
    if (moduleloader::moduleExists('configdb')) {
        $c = new \modules\configdb\module();
        $c->overrideAll();
    }
    return $commands;
}
