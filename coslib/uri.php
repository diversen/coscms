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
    
    /**
     * fragements in a uri string
     *
     * @var array fragments
     */
    public static $fragments = array();


   /**
    * object holding an instance of the uri class. singleton
    *
    * @var object $instance
    */
    private static $instance = null;

    /**
     * array for holding info about the uri string
     *
     * @var array $info for holding info
     */
    public static $info = array();


    /**
     * method for returning an URI instance
     * @return object $uri an instance of the uri class
     *
     */
    public static function getInstance() {
         if(is_null(self::$instance)){
             self::$instance = new uri;
             self::setInfo();
         }
         return self::$instance;
    }

    /**
     * info about the url is set when creating the object. 
     * This is private as we want users to only use the getInstance
     * method and only create the object once.  
     */
    private function __construct() {
        self::setInfo();        
    }

    /**
     * method for setting all info we need when loading a module
     * info is set when we construct the object first time. 
     * info is placed in self::$info
     */
    public static function setInfo($path = null) {
        static $info_isset = null;
        if ($info_isset) { 
            return;
        }
        
        $frags = self::getRequestUriAry($path);

        self::$info['frags'] = $frags;
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

    /**
     * method for getting the base path as an array
     * @return array $fragments the URL base parts an array
     */
    public static function getRequestUriAry($path){
        self::splitRequestAry($path);
        $fragments =  explode('/', self::$info['path']);
        foreach ($fragments as $key => $value) {
            if (strlen($value) == 0) {
                unset($fragments[$key]);
            }
        }

        $fragments = array_values($fragments);
        return $fragments;
    }

    /**
     * splits the url into query and path parts
     * place the values in self::$info
     * @param string $path the path to split
     * @return void 
     */
    public static function splitRequestAry ($path) {
        if (!$path) {
            $path = $_SERVER['REQUEST_URI'];
        }

        $parsed = @parse_url($path);
        if ($parsed === false) {
            // malformed url
            self::splitRequestAryFallback($path);
            moduleLoader::setStatus(404);
        }
        if (!empty($parsed['query'])) { 
            self::$info['query'] = $parsed['query'];
        } else {
            self::$info['query'] = '';
        }
        self::$info['path'] = $parsed['path'];
    }
    
    /**
     * parse a malformed url path in a primitive way
     * @param string $path 
     */
    public static function splitRequestAryFallback ($path) {
        $uri = explode('?', $path);
        if (isset($uri[1])) { 
            self::$info['query'] = $uri[1];
        } else {
            self::$info['query'] = '';
        } 
        //return $uri;
        self::$info['path'] = $uri[0];
    // }
    } 

    /**
     * Method for getting fragments that makes up the controller
     * We will only use char values, if an int is set we break the loop
     * This is how we recognice a controller.
     * It is the part of the url string before any numeric values!
     *
     * @param array     $fragements fragments to examine
     * @return array    $fragments controller fragements 
     *                  e.g. array ('account', 'login')
     */
    public static function getControllerPathAry($fragments){
        $num_frags = count($fragments);
        $base_fragments = array();
        for ($i = 0; $i < $num_frags; $i++ ){
            // if we have an int we break
            if (self::getPositiveInt($fragments[$i])){
                break;
            }
            $base_fragments[$i] = $fragments[$i];    
        }
        return $base_fragments;
    }
    
    /**
     * gets a max int or zero from an int and a max int. 
     * @param int $val the var to get max int from
     * @param int $max max int to return
     * @return int $val
     */
    public static function getPositiveInt ($val) {
        
        $val = filter_var($val, FILTER_VALIDATE_INT, array(
            'options' => array('min_range' => 0)
        ));
        if (!$val){
            return false;
        }
        return true;
    }

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

    /**
     * method for getting the controllers fragement
     *
     * @param   array  the base fragements
     * @return  string last fragment of base_fragments
     */
    public static function getControllerFrag($base_fragments){
        return array_pop($base_fragments);
    }

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

    /**
     * method for getting number of fragements
     * 
     * @return int  number of fragements 
     */

    public function numFragments(){
        $frags = count(self::$fragments);
        return $frags;
    }

    /**
     * method for getting all fragments as array
     * @return array    of fragements
     */
    public function getAllFragments(){
        return self::$fragments;

    }

    /**
     * method for getting all info about an URI instance
     *
     * @return array    getting all info collected in constructor
     */
    public function getInfo(){
        return self::$info;
    }

    /**
     * method for preventing cloning
     */
    private function __clone() {
    }
}
