<?php

class template_meta  {
    
    /**
     * holding meta tags
     * @var array $meta  
     */
    static $meta = array();
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
