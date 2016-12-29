#!/usr/bin/env php
<?php

include_once "vendor/autoload.php";

use diversen\autoloader\modules;
use diversen\conf;
use diversen\minimalCli;
use diversen\db\connect;
use diversen\cli\helpers;

$m = new modules();
$m->autoloadRegister();

$path = dirname(__FILE__);
conf::setMainIni('base_path', $path);

$cliHelp = new diversen\cli\helpers();
$cliHelp->bootCli();

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

$res = $cliHelp->dbConExists();
if ($res) {
    $module_commands = $cliHelp->getModuleCommands();
    $commands = array_merge($commands, $module_commands);
}

$cli->commands = $commands;
$cli->runMain();
