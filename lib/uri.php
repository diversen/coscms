<?php

/**
 * File contains contains class for getting information about the uri string
 * Note: many of the methods are taken from the website. See link. Great site
 * with good tutorials about php programming.
 *
 * @link   http://www.phpro.org
 * @package    coslib
 */

/**
 * Class contains method for getting info about the uri string
 *
 * @package    coslib
 */
class uri {
    
    // {{{ public static $fragments
    /**
     * fragements in a uri string
     *
     * @var array fragments
     */
    public static $fragments = array();

    // }}}
    // {{{ public static $instance = null

   /**
    * object holding an instance of the uri class (singleton pattern)
    *
    * @var object $instance
    */
    private static $instance = null;
    
    // }}}
    // {{{ public static $info = null
    /**
     * array for holding info about the uri string
     *
     * @var <array> $info for holding info
     */
    public static $info = array();

    // }}}
    // {{{ public static function getInstance()

    /**
     * method for returning an URI instance
     * @return object uri instance
     *
     */
    public static function getInstance() {
         if(is_null(self::$instance)){
             self::$instance = new uri;
         }
         return self::$instance;
    }

    // }}}
    // {{{ __construct
    /**
     * constructor which sets all info needed for getting all info
     * about the uri string
     *
     */
    private function __construct() {
        self::setInfo();        
    }

    // }}}
    public static function setInfo() {
        static $info_isset = null;
        if ($info_isset) { 
            return;
        }
        
        $frags = self::getRequestUriAry();

        self::$info['frags'] = self::getRequestUriAry();
        self::$fragments = $frags;

        $controller_frags = self::getControllerPathAry($frags);
        self::$info['controller_frags'] = $controller_frags;
        self::$info['controller'] = self::getControllerFrag($controller_frags);
        self::$info['module_frag'] = self::getModuleFrag($controller_frags);
        self::$info['controller_path_str'] = self::getControllerPathStr($controller_frags);
        self::$info['module_name'] = self::getModuleName($controller_frags);
        self::$info['module_base'] = self::getModuleBase($controller_frags);
        self::$info['module_base_name'] = self::getModuleBaseName($controller_frags);
        $info_isset = true;
    }
    
    // {{{ public static function getRequestUriAry()

    /**
     * method for getting the request uri
     *
     * @return array all fragments in uri as an array
     */
    public static function getRequestUriAry(){
        $uri = self::splitRequestAry();
        //print_r($uri[1]);
        $fragments =  explode('/', $uri[0]);
        // clean url for empty values or null values
        foreach ($fragments as $key => $value) {
            if (is_null($value) || empty($value)) {
                unset($fragments[$key]);
            }
        }

        // set fragments
        $fragments = array_values($fragments);
        return $fragments;
    }

    // }}}
    public static function splitRequestAry () {
        $uri = explode('?', $_SERVER['REQUEST_URI']);
        if (isset($uri[1])) { 
            self::$info['get_part'] = $uri[1];
        } else {
            self::$info['get_part'] = '';
        } 
        return $uri;
    }
    // {{{ public static function getControllerPathAry($fragments)

    /**
     * Method for getting fragments that makes up the controller
     * We will only use char values, if an int is set we break the loop
     * This is how we recognice a controller.
     * It is the part of the url string before any numeric values!
     *
     * @todo a little weird that we tell what is a controller with an int!
     * @param array     fragements to examine
     * @return array    base fragments that makes up the controller.
     */
    public static function getControllerPathAry($fragments){
        $num_frags = count($fragments);
        $base_fragments = array();
        for ($i = 0; $i < $num_frags; $i++ ){
            // if we have an int we break
            if (get_zero_or_positive($fragments[$i])){
                break;
            }
            $base_fragments[$i] = $fragments[$i];    
        }
        return $base_fragments;
    }
    // }}}
    // {{{ public static function getModuleBase($fragments)

    /**
     * method for getting the base module if any
     *
     * @param   array   fragments from which we find the base module
     * @return  string  path to top level module.
     */
    public static function getModuleBase($fragments){
        if (empty($fragments)) {
            return '';
        }
        return '/' . $fragments[0];
    }

/**
     * method for getting the base module if any
     *
     * @param   array   fragments from which we find the base module
     * @return  string  path to top level module.
     */
    public static function getModuleBaseName($fragments){
        if (empty($fragments)) {
            return '';
        }
        return $fragments[0];
    }

    

    // }}}
    // {{{ public static function getControllerFrag($base_fragment)

    /**
     * method for getting the controllers fragement
     *
     * @param   array  the base fragements
     * @return  string last fragment of base_fragments
     */
    public static function getControllerFrag($base_fragments){
        return array_pop($base_fragments);
    }

    // }}}
    // {{{ getModuleFrag($base_fragment)
    /**
     * method for getting the module fragement
     *
     * @param   array   base_fragments fragments that makes up controller
     * @return  string  last fragment of base_fragments
     */
    public static function getModuleFrag($base_fragments){
        array_pop($base_fragments);
        return array_pop($base_fragments);
    }

    // }}}
    // {{{ public static function getControllerPathStr($base_fragments)

    /**
     * method for getting controllers path string
     *
     * @param   array   base_fragments
     * @return  string  base_str controller as a path string
     */
    public static function getControllerPathStr ($base_fragments){
        array_pop($base_fragments);
        $num_base_frags = count($base_fragments);
        $base_str = '';
        for ($i = 0; $i < $num_base_frags; $i++){
            $base_str.= '/' . $base_fragments[$i];
        }
        return $base_str;
    }

    // }}}
    // {{{ public static function getControllerPathStr($base_fragments)

    /**
     * method for getting controllers path string
     *
     * @param   array   base_fragments
     * @return  string  base_str controller as a path string
     */
    public static function getModuleName ($base_fragments){
        array_pop($base_fragments);
        $num_base_frags = count($base_fragments);
        $base_str = '';
        for ($i = 0; $i < $num_base_frags; $i++){
            $base_str.= $base_fragments[$i] . "/";
        }
        return rtrim($base_str, '/');
    }
    // {{{ public function fragement($key)

    /**
     * method for getting a exact uri fragment
     *
     * @param   string          The uri key
     * @return  string|false    return string or false if no such key
     */
    public static function fragment($key) {
        if(array_key_exists($key, self::$fragments)){
            return self::$fragments[$key];
        }
        return false;
    }

    // }}}
    // {{{ function numFragements()
    /**
     * method for getting number of fragements
     * 
     * @return int  number of fragements 
     */

    public function numFragments(){
        $frags = count(self::$fragments);
        return $frags;
    }

    // }}}
    // {{{ getAllFragements()
    /**
     * method for getting all fragments as array
     * @return array    of fragements
     */
    public function getAllFragments(){
        return self::$fragments;

    }

    // }}}
    // {{{ public function getInfo()
    /**
     * method for getting all info about an URI instance
     *
     * @return array    getting all info collected in constructor
     */
    public function getInfo(){
        return self::$info;
    }

    // }}}
    // {{{ private function __clone()
    /**
     * method for preventing cloning
     */
    private function __clone() {
    }
}