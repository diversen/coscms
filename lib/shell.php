#!/usr/bin/php
<?php

/**
 * Main shell script which parses all functions put in commands
 *
 * @package     shell
 */

define('_COS_PATH', realpath(dirname($_SERVER['SCRIPT_FILENAME']) . '/..'));
define('_COS_CLI', 1);

class register {
    public static $vars = array();
}

register::$vars['coscms_base'] = _COS_PATH;

include_once "scripts/shell_base/common.inc";
include_once "lib/head.php";
include_once "lib/uri.php";
include_once "lib/lang.php";
include_once "lib/common.php";
include_once "lib/db.php";
include_once "lib/moduleloader.php";
include_once "lib/moduleInstaller.php";
include_once 'Console/CommandLine.php';

// {{{ class mainCli (for parsing command line scripts).
/**
 * class shell is a wrapper function around PEAR::commandLine
 *
 * @package     shell
 */

class mainCli {

    /**
     *
     * @var array   holding commands
     */
    static $commands = array();

    /**
     *
     * @var object  holding parser
     */
    static $parser;

    /**
     *
     * @var string  holding command
     */
    static $command;

    /**
     * constructor
     * static function for initing command parser
     * creates parser and sets version and description
     */
    static function init (){
        
        self::$parser = new Console_CommandLine();
        self::$parser->description = 'Command line program for installing cos cms and reading databases';
        self::$parser->version = '0.0.1';
    }

    /**
     * method for setting a command
     *
     * @param string command
     * @param array options
     */
    static function setCommand ($command, $options){
        self::$command = self::$parser->addCommand($command, $options);
    }

    /**
     * method for setting an option
     *
     * @param string    command
     * @param array     options
     */

    static function setOption ($command, $options){
        self::$command->addOption($command, $options);
    }

    /**
     * method for setting an argument
     *
     * @param string argument
     * @param array  options
     */
    static function setArgument($argument, $options){
        self::$command->addArgument($argument, $options);
    }

    /**
     * method for running the parser
     *
     * @todo    check what the parser returns
     * @return  int     0 on success any other int is failure
     */
    static function run(){
        try {
            $ret = 0;
            $result = self::$parser->parse();
            if (is_object($result) && isset($result->command_name)){
                if (isset($result->command->options)){
                    foreach ($result->command->options as $key => $val){
                        // command option if set run call back
                        if ($val == 1){
                            // bring argument to command if set.
                            // only call function if it exists.
                            if (!empty($result->command->args)) {
                                if (function_exists($key)){
                                    $ret = $key($result->command->args);
                                }
                            } else {
                                if (function_exists($key)){
                                    $ret = $key();
                                }
                            }
                        } else {
                            $no_sub = 1;
                        }
                    }
                    return $ret;
                } else {
                    $no_base = 1;
                }
            }

            if (isset($no_sub)){
                cos_cli_print('No sub commands given use -h or --help for help');
            }
            if (isset($no_base)){
                cos_cli_print('No base commands given use -h or --help for help');
            }


        } catch (Exception $e) {
            self::$parser->displayError($e->getMessage());
        }        
    }

    public static $ini = array();

    public static function loadCliModules (){
        $modules = moduleLoader::getAllModules();
        foreach ($modules as $key => $val){
            if ($val['is_shell'] == 1){
                // include all base commands from scripts/commands folder
                $command_path = _COS_PATH . "/modules/$val[module_name]";
                //$file_list = get_file_list($command_path);
                //foreach ($file_list as $key => $val){
                $path =  _COS_PATH . "/modules/$val[module_name]/$val[module_name].inc";
                include_once $path;

                $ini = _COS_PATH . "/modules/$val[module_name]/$val[module_name].ini";
                self::$ini[$val['module_name']] = parse_ini_file($ini);
            }
        }
    }
}

// }}}

mainCli::init();

// include all base commands from scripts/commands folder
$command_path = _COS_PATH . "/scripts/shell_base";
$file_list = get_file_list($command_path);
foreach ($file_list as $key => $val){
    $path =  _COS_PATH . "/scripts/shell_base/$val";
    include_once $path;
}

//mainCli::loadCliModules();

// after adding all commands found we run main program.
mainCli::run();
