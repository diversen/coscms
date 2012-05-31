<?php

/**
 * simple dispatch class
 * @package urldispatch 
 */

/**
 * simple class for dispatching url patterns to a controller file or class
 * call. 
 * @package urldispatch 
 */
class urldispatch {
    
    public static $pathInfo = array(); 
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
}
