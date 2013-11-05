<?php

/**
 * Main shell script which parses all functions put in commands
 *
 * @package     shell
 */
/**
 * set include path
 * @ignore
 */

include_once "coslib/coslibSetup.php";
include_once "coslib/head.php";


include_once "coslib/shell/common.inc";

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

        
    /**
     * exit code
     * @var int
     */
    public static function exitInt($code) {
        exit($code);
    }
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
        
        // Adding an main option for setting domain
        self::$parser->addOption(
            'verbose',
            array(
                'short_name'  => '-v',
                'long_name'   => '--verbose',
                'description' => 'Produce extra output',
                'action'      => 'StoreTrue',
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
        if (isset($options['description'])) {
            $options['description'] = strings_ext::removeNewlines($options['description']);
        }
        
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

            $verbose = $result->options['verbose'];
            config::$vars['verbose'] = $verbose;

            // check domain
            $domain = $result->options['domain'];
            config::$vars['domain'] = $domain;
         
            
            if ($domain != 'default' || empty($domain)) {
                $domain_ini = _COS_PATH . "/config/multi/$domain/config.ini";
                if (!file_exists($domain_ini)) {
                    cos_cli_abort("No such domain - no configuration found: It should be placed here $domain_ini");
                } else {
                    
                    // if a not standard domain is given - we now need to load
                    // the config file again -  e.gi n order to tell system which database
                    // we want to use. 
                    
                    // we also loose all sub module ini settings
                    // Then db enabled modules ini settings will only work
                    // on 'default' site. 
                    config::loadMainCli();
                    
                }
            }
            
            if (is_object($result) && isset($result->command_name)){
                if (isset($result->command->options)){
                    foreach ($result->command->options as $key => $val){
                        // command option if set run call back
                        if ($val == 1){                             
                            if (function_exists($key)){                    
                                $ret = $key($result->command->args);
                            } else {
                                cos_cli_abort("No such function $key");
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
        
        // load language
        $lang_all = config::getMainIni('language_all');
        if ($lang_all) {
            lang::loadTemplateAllLanguage();
        } else {
            lang::init();
        } 
        
        $mod_loader = new moduleloader();
        $modules = moduleloader::getAllModules();
              
        foreach ($modules as $val){     
            if (isset($val['is_shell']) && $val['is_shell'] == 1){
                moduleloader::includeModule($val['module_name']);
                
                $path =  _COS_PATH . "/" . _COS_MOD_DIR.  "/$val[module_name]/$val[module_name].inc";
                
                if (file_exists($path)) {
                    include_once $path;
                }

                $ini = _COS_PATH . "/" . _COS_MOD_DIR  . "/$val[module_name]/$val[module_name].ini";
                self::$ini[$val['module_name']] = config::getIniFileArray($ini);                
            }
        }
        
        
        //db::$dbh = null;
    }
    
    /**
     * loads all base modules
     * base modules are placed in coslib/shell
     */
    public static function loadBaseModules () {
        
        $command_path = _COS_PATH . '/coslib/shell';
 
        $base_list = file::getFileList($command_path, array ('search' => '.inc'));
        foreach ($base_list as $val){
            include_once $command_path . "/$val";
        }
        
        $locale_path = _COS_PATH . '/coslib/shell/locale';     
        $locale_list = file::getFileList($locale_path, array ('search' => '.inc'));
        
        foreach ($locale_list as $val){
            include_once $val;
        }
    }
}
