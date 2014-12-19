<?php

namespace diversen\template;
use diversen\html;
use diversen\conf as config;
use diversen\template;

/**
 * File containing class for adding meta tags
 * 
 * @package template
 */

/**
 * 
 * class used for parsing meta tags
 * @package template
 */

class meta extends template  {
    
        
    /**
     * holding meta tags
     * @var array $meta  
     */
    public static $meta = array();
    
    /**
     * var holding meta tags strings
     * @var string $metaStr
     */
    public static $metaStr = '';
  
    /**
     * method for setting meta tags. The tags will be special encoded
     * @param   array   $ary of metatags e.g. 
     *                         <code>array('description' => 'content of description meta tags')</code>
     *                         or string which will be set direct. E.g. 
     *                         
     */
    public static function setMeta($ary){

        foreach($ary as $key => $val){
            if (isset(self::$meta[$key])){
                continue;
            }
            self::$meta[$key] = html::specialEncode($val);
        }
    }
    
    /**
     * sets meta tags directly. 
     * @param string $str e.g. <code><meta name="description" content="test" /></code>
     */
    public static function setMetaAsStr ($str) {
        self::$metaStr.= $str;
    }
    
    /**
     * method for getting the meta tags as a string
     * You can specifiy meta keywords and description global in config.ini
     * by using the settings, meta_desc and meta_keywords.
     *  
     * @return string $str the meta tags as a string. This can be used
     *                     in your mainTemplate
     */
    public static function getMeta (){        
        $str = '';

        if (!isset(self::$meta['keywords'])) {
            $str = '';
            $str = config::getMainIni('meta_keywords');
            $str = trim($str);
            if (!empty($str)) {
                self::$meta['keywords'] = $str;
            }
        }
        
        // master domains are allow visible for robots
        $master = config::getMainIni('master');
        if (!isset(self::$meta['robots']) && $master) {
            
            $str = '';
            $str = config::getMainIni('meta_robots');
            $str = trim($str);
            if (!empty($str)) {
                self::$meta['robots'] = $str;
            }
        }

        if (empty(self::$meta['description'])) {
            $str = '';
            $str = config::getMainIni('meta_desc');
            $str = trim($str);
            if (!empty($str)) {
                self::$meta['description'] = $str;
            }
        }

        $str = '';
        foreach (self::$meta as $key => $val) {
            $str.= "<meta name=\"$key\" content=\"$val\" />\n";
        }
        
        $str.= self::$metaStr;
        return $str;
    }
}
