<?php

/**
 * File containing class getting favicon
 * @package template
 */

/**
 * 
 * File containing class getting favicon
 * @package template
 */

class template_favicon {
    
        /**
     * returns favicon html
     * @return string $html 
     */
    public static function getFaviconHTML () {
        $favicon = config::getMainIni('favicon');
        $domain = config::getDomain();
        $rel_path = "/files/$domain/favicon/$favicon";
        $full_path = _COS_HTDOCS . "/$rel_path"; 
        if (!is_file($full_path)) {
            $rel_path = '/favicon.ico';
        }
        
        $str = "<link rel=\"shortcut icon\" href=\"$rel_path\" type=\"image/x-icon\" />\n";
        return $str;
    }
}