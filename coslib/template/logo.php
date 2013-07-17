<?php

/**
 * File containing class getting logo
 * @package template
 */

/**
 * 
 * File containing class getting logo
 * @package template
 */

class template_logo {
    
        /**
     * method for getting html for front page. If no logo has been 
     * uploaded. You will get logo as html
     * @param type $options options to give to html::createHrefImage
     * @return string $str the html compsoing the logo or main title
     */
    public static function getLogoHTML ($options = array()) {
        $logo = config::getMainIni('logo');
        if (!$logo){
            $logo_method = config::getMainIni('logo_method');
            if (!$logo_method) {
                $title = $_SERVER['HTTP_HOST'];
                $link = html::createLink('/', $title);
                return $str = "<div id=\"logo_title\">$link</div>";
            } else {
                moduleloader::includeModule ($logo_method);
                $str =  $logo_method::logo();
                return $str = "<div id=\"logo_title\">$str</div>";
            }
                
        } else {
            $file ="/logo/" . config::$vars['coscms_main']['logo'];
            $src = config::getWebFilesPath($file);
            if (!isset($options['alt'])){           
                $options['alt'] = $_SERVER['HTTP_HOST'];
            }
            $href = html::createHrefImage('/', $src, $options);
            $str = '<div id="logo_img">' . $href . '</div>' . "\n"; 
            //die($str);
            return $str;
        }
    }
    
}