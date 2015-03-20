<?php

namespace diversen\template;

use diversen\html;
use diversen\conf as config;
use diversen\template;
use diversen\strings;
use diversen\template\assets;

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
class meta extends template {

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
     * method for setting meta tags. The tags will be special encoded in
     * this method
     * @param array $ary $meta tags            
     */
    public static function setMeta($ary) {

        foreach ($ary as $key => $val) {
            if (isset(self::$meta[$key])) {
                continue;
            }
            self::$meta[$key] = html::specialEncode($val);
        }
    }

    /**
     * sets all <head></head> meta info. 
     * Params should not be encoded
     * @param string $title html page title
     * @param string $description html page description and og description
     * @param string $keywords keywords
     * @param string $image image
     * @param string $type og type
     */
    public static function setMetaAll(
            $title, 
            $description ='', 
            $keywords = '', 
            $image = '', 
            $type = '', 
            $author = '') {
    
        // title
        assets::setTitle(html::specialEncode($title));
        self::setMetaAsStr(
                '<meta property="og:title" content="' . html::specialEncode($title) . '" />' . "\n");

        // description
        if (empty($description)) {
            $description = config::getMainIni('meta_desc');
        }
        
        
        $desc = strings::substr2($description, 255);
        $og_desc = html::specialEncode(strings::substr2($description, 320));
        
        if (!empty($og_desc)) {
            self::setMetaAsStr(
                    '<meta property="og:description" content="' . $og_desc . '"/>' . "\n");
        }
        
        if (!empty($desc)) {
            self::setMeta(
                    array('description' => $desc));
        }
        
        if (!empty($author)) {
            self::setMeta(
                    array('author' => $author));
        }
        
        // keywords
        if (empty($keywords)) {
            $keywords = config::getMainIni('meta_meywords');
        }
        
        if (!empty($keywords)) {
            self::setMeta(
                    array('keywords' => $keywords));
        }
                   
        // image
        if (!empty($image)) {
            $server = config::getSchemeWithServerName();
            $image = $server . $image;
        }

        if (!empty($image)) {
            self::setMetaAsStr(
                    '<meta property="og:image" content="' . $image . '"/>' . "\n");
        }

        // type
        if (!empty($type)) {
            self::setMetaAsStr(
                    '<meta property="og:type" content="' . $type . '"/>' . "\n");
        }
    }
    
    /**
     * set canonical link rel
     * @param string $canon path without server scheme and name
     */
    public static function setCanonical ($canon) {
        $canon = config::getSchemeWithServerName() . $canon;
        $str = "<link rel=\"canonical\" href=\"$canon\" />\n";
        self::setMetaAsStr($str);
    }

    /**
     * sets a meta string directly. 
     * @param string $str <code><meta name="description" content="test" /></code>
     */
    public static function setMetaAsStr($str) {
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
    public static function getMeta() {

        if (!isset(self::$meta['keywords'])) {
            $str = config::getMainIni('meta_keywords');
            if (!empty($str)) {
                self::$meta['keywords'] = $str;
            }
        }

        // master domains are allow visible for robots
        $master = config::getMainIni('master');
        if (!isset(self::$meta['robots']) && $master) {
            $str = config::getMainIni('meta_robots');
            if (!empty($str)) {
                self::$meta['robots'] = $str;
            }
        }

        if (empty(self::$meta['description'])) {
            $str = config::getMainIni('meta_desc');
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
