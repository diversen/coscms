<?php

/**
 * Main shell script which parses all functions put in commands
 *
 * @package     shell
 */

include_once "coslib/head.php";
include_once 'Console/CommandLine.php';
include_once "coslib/shell_base/common.inc";



/**
 * class shell is a wrapper function around PEAR::commandLine
 *
 * @package     shell
 */
class mainCli {

    /**
     * var holding commands
     * @var array $commands
     */
    static $commands = array();

    /**
     * var holding parser
     * @var object $parser
     */
    static $parser;

    /**
     * var holding command
     * @var string  $command
     */
    static $command;

    /**
     * var holding ini settings for modules
     * @var array $ini
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
        self::$parser->description = <<<EOF
                    _ _       _     
  ___ ___  ___  ___| (_)  ___| |__  
 / __/ _ \/ __|/ __| | | / __| '_ \ 
| (_| (_) \__ \ (__| | |_\__ \ | | |
 \___\___/|___/\___|_|_(_)___/_| |_|

Modulized Command line program

EOF;
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
     * method for running the commandline parser
     * @param  array    $options array ('disable_base_modules' => true, 
     *                                  'disable_db_modules => true) 
     * 
     *                  If we only use the coslib as a lib we may 
     *                  disable loading of base or db modules
     *                              
     * @return  int     0 on success any other int is failure
     */
    static function run($options = array ()){
        try {
            $ret = 0;
            
            // load config file
            // Note: First time loaded we only load it order to load any
            // base modules which may be set
           
            config::loadMainCli();
            
            $htdocs_path = config::getMainIni('htdocs_path');
    
            // default
            if (!$htdocs_path) {
                define('_COS_HTDOCS', _COS_PATH . '/htdocs');
            }

            if ($htdocs_path == '_COS_PATH') {
                define('_COS_HTDOCS', _COS_PATH);
            }
            
            // load all modules
            if (!isset($options['disable_base_modules'])) {
                mainCli::loadBaseModules();
            }
            if (!isset($options['disable_db_modules'])) {
                mainCli::loadDbModules();
            }
            
                     
            
            $result = self::$parser->parse();

            // we parse the command line given. 
            // Note: Now we examine the domain, to if the -d switch is given
            // this is done in order to find out if we operate on another 
            // database than the default. E.g.: multi domains. 
            
            $domain = $result->options['domain'];
            config::$vars['domain'] = $domain;
            
            if ($domain != 'default' || empty($domain)) {
                $domain_ini = _COS_PATH . "/config/multi/$domain/config.ini";
                if (!file_exists($domain_ini)) {
                    echo $domain_ini;
                    cos_cli_abort('No such domain - no configuration found');
                }
            }
            
            
            // if a not standard domain is given - we now need to load
            // the config file again - in order to tell system which database
            // we want to use. E.g. such a database may have been set in 
            // config/multi/example.com/config.ini
            // Then we know we operate on the correct database. 
            
            config::loadMainCli();
           
            // and connect
            $db = new db();
            $ret = @$db->connect(array('dont_die' => 1));
            
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

    /**
     * loads all modules in database
     */
    public static function loadDbModules (){        
        $db = new db();
        $ret = @$db->connect(array('dont_die' => 1));
      
        if ($ret == 'NO_DB_CONN'){
            // if no db conn we exists before loading any more modules.
            return;
        }

        $rows = $db->selectQuery("SHOW TABLES");

        if (empty($rows)){
            cos_cli_print('No tables exists. We can not load all modules');
            return;
        }
        
        $mod_loader = new moduleloader();

        $modules = moduleloader::getAllModules();

        foreach ($modules as $val){
            if (isset($val['is_shell']) && $val['is_shell'] == 1){
                //$command_path = _COS_PATH . "/modules/$val[module_name]";
                $path =  _COS_PATH . "/modules/$val[module_name]/$val[module_name].inc";
                
                if (file_exists($path)) {
                    include_once $path;
                }

                $ini = _COS_PATH . "/modules/$val[module_name]/$val[module_name].ini";
                self::$ini[$val['module_name']] = config::getIniFileArray($ini);
            }
        }
        
        //db::$dbh = null;
    }
    
    /**
     * loads all base modules
     * base modules are placed in coslib/shell_base
     */
    public static function loadBaseModules () {
        
        $options = array ('search' => '.inc');
        // TODO: Test
        //$coslib_path = file::getFirstCoslibPath();
        //$command_path = $coslib_path . '/shell_base';
        $command_path = _COS_PATH . '/coslib/shell_base';
        $file_list = file::getFileList($command_path, $options);
        

        foreach ($file_list as $val){
            $path =  $command_path . "/$val";
            include_once $path;
        }
        
        $command_path = _COS_PATH . '/coslib/shell_base/locale';
        $file_list = file::getFileList($command_path, $options);

        foreach ($file_list as $val){
            $path =  $command_path . "/$val";
            include_once $path;
        }
    }
}
