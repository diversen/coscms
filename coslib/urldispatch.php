<?php

/**
 * simple url dispatch class
 * @package urldispatch 
 */

/**
 * simple class for dispatching url patterns to a controller file or class
 * call. 
 * @package urldispatch 
 */
class urldispatch {
    
    /**
     * var holding pathInfo
     * @var array $pathInfo
     */
    public static $pathInfo = array();
    
    /**
     * parses pathinfo with parse_url
     */
    public function parse () {       
        self::$pathInfo = parse_url($_SERVER['REQUEST_URI']);
    }
   
    /**
     * method for calling function or static method
     * @param type $call
     * @param type $matches
     * @return boolean $res if no function or method is found return false. 
     */
    public static function call ($call, $matches) {
        $ary = explode('::', $call);
        if (count($ary) == 1) {
            if (function_exists($call)) {
                $call($matches);
                return true;
            } else {
                return false;
            }   
        }
        
        if (count($ary == 2)) {
            $class = $ary[0]; $method = $ary[1];
            if (!method_exists($class, $method)) {
                return false;
            }
            $class::$method($matches);
            return true;
        }    
        return false;
    }
    
    /**
     * returns false if no matches are found. Return true
     * if a match is found and called. 
     * @param array $routes array of routes
     * @return boolean $res true on success and false on failure.  
     */
    public static function match ($routes) {
        self::parse();        
        $matches = array();
        foreach ($routes as $pattern => $call) {           
            if (preg_match($pattern, self::$pathInfo['path'] , $matches)) {
                $res = self::call($call, $matches);
                return $res;
            } else {
                // no pattern match
                return false;
            }
        }
    }
    
    /**
     * returns false if no matches are found. Return true
     * if a match is found and called. 
     * @param array $routes array of routes
     * @return boolean $res true on success and false on failure.  
     */
    public static function includeFile ($routes) {
        self::parse();        
        $matches = array();
        foreach ($routes as $pattern => $call) { 
            //echo $call;
            if (preg_match($pattern, self::$pathInfo['path'] , $matches)) {

                $res = self::call($call, $matches);

                return $res;
            } else {
                // no pattern match
                return false;
            }
        }
    }
    
    /**
     * sets db routes
     */
    public static function setDbRoutes () {
        $routes = dbQ::setSelect('system_route')->fetch();
        foreach ($routes as $route) {
            config::$vars['coscms_main']['routes'][$route['route']] = unserialize($route['value']);  
        }
    }
    
    public static function getDbRoutes () {
        return config::$vars['coscms_main']['routes'];
    }
    
    public static function getMatchRoutes () {
        self::parse();        
        $matches = array();
        $routes = self::getDbRoutes();

        foreach ($routes as $pattern => $call) { 
            
            if (preg_match($pattern, self::$pathInfo['path'] , $matches)) {

                return $call;
            
            } 
        }
        return false;
    }
}
