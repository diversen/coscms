#!/usr/bin/php
<?php

/**
 * Main shell script which parses all functions put in commands
 *
 * @package     shell
 */



/**
 * @ignore
 */
define('_COS_PATH', realpath(dirname($_SERVER['SCRIPT_FILENAME']) . '/..'));
define('_COS_CLI', 1);



/**
 * @package shell
 */
class register {
    public static $vars = array();
}

register::$vars['coscms_base'] = _COS_PATH;

include_once 'Console/CommandLine.php';
include_once "Console/Color.php";
include_once "lib/uri.php";
include_once "lib/lang.php";
include_once "lib/db.php";
include_once "lib/moduleloader.php";
include_once "lib/moduleInstaller.php";
include_once "lib/common.php";
include_once "lib/shell_base/common.inc";
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
     *
     * @var array   used for holding ini settings for shell modules.
     */
    public static $ini = array();

    // {{{ init ()
    /**
     * constructor
     * static function for initing command parser
     * creates parser and sets version and description
     */
    static function init (){
        
        self::$parser = new Console_CommandLine();
        self::$parser->description = 'Command line program for installing cos cms and reading databases';
        self::$parser->version = '0.0.1';

        // Adding an main option for setting domain
        self::$parser->addOption(
            'domain',
            array(
                'short_name'  => '-d',
                'long_name'   => '--domain',
                'description' => 'Domain to use if using multi hosts. If not set we will use default domain',
                'action'      => 'StoreString',
                'default'     => 'default',
            )
        );        
    }
    // }}}
    // {{{ function setCommand($command, $options)
    /**
     * method for setting a command
     *
     * @param string command
     * @param array options
     */
    static function setCommand ($command, $options){
        self::$command = self::$parser->addCommand($command, $options);
    }

    // }}}
    // {{{ setOptions ($command, $options)
    /**
     * method for setting an option
     *
     * @param string    command
     * @param array     options
     */

    static function setOption ($command, $options){
        self::$command->addOption($command, $options);
    }
    // }}}
    // {{{ function setArgument($argument, $options){
    /**
     * method for setting an argument
     *
     * @param string argument
     * @param array  options
     */
    static function setArgument($argument, $options){
        self::$command->addArgument($argument, $options);
    }
    // }}}
    // {{{ function run ()
    /**
     * method for running the parser
     *
     * @return  int     0 on success any other int is failure
     */
    static function run(){
        try {
            $ret = 0;
            
            // include head - will set same include path as web env
            include_once "lib/head.php";
            
            // load config file
            // Note: First time loaded we only load it order to load any
            // base modules which may be set
            load_config_file();
            
            // load all modules
            mainCli::loadBaseModules();
            mainCli::loadDbModules();
                      
            
            $result = self::$parser->parse();

            // we parse the command line given. 
            // Note: Now we examine the domain, to if the -d switch is given
            // this is done in order to find out if we operate on another 
            // database than the default. E.g.: multi domains. 
            
            $domain = $result->options['domain'];
            register::$vars['domain'] = $domain;

            // if a not standard domain is given - we now need to load
            // the config file again - in order to tell system which database
            // we want to use. E.g. such a database may have been set in 
            // config/multi/example.com/config.ini
            // Then we know we operate on the correct database. 
            
            load_config_file();
           
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
                                } else {
                                    cos_cli_abort("No such function $key");
                                }
                            
                            // No args - we leave empty we leave command argument
                            // empty
                            } else {
                                if (function_exists($key)){
                                    $ret = $key();
                                } else {
                                    cos_cli_abort("No such function $key");
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
    // }}}
    // {{{ loadCliModules ()
    public static function loadDbModules (){
        // check if a connection exists.

        
        $db = new db();
        $ret = @$db->connect(array('dont_die' => 1));
      
        if ($ret == 'NO_DB_CONN'){

            // if no db conn we exists before loading any more modules.
            cos_cli_print("Notice: No db exists!");
            return;
        }

        $rows = $db->selectQuery("SHOW TABLES");

        if (empty($rows)){
            cos_cli_print('No tables exists. We can not load all modules');
            return;
        }

        $modules = moduleLoader::getAllModules();

        foreach ($modules as $key => $val){
            if (isset($val['is_shell']) && $val['is_shell'] == 1){
                $command_path = _COS_PATH . "/modules/$val[module_name]";
                $path =  _COS_PATH . "/modules/$val[module_name]/$val[module_name].inc";
                
                if (file_exists($path)) {
                    include_once $path;
                }

                $ini = _COS_PATH . "/modules/$val[module_name]/$val[module_name].ini";
                self::$ini[$val['module_name']] = parse_ini_file($ini);
            }
        }
    }
    
    static function loadBaseModules () {
        $command_path = _COS_PATH . "/lib/shell_base";
        $file_list = get_file_list($command_path);
        foreach ($file_list as $key => $val){
            $path =  _COS_PATH . "/lib/shell_base/$val";
            include_once $path;
        }
    }
}

mainCli::init();
mainCli::run();