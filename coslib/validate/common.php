<?php

class validate_common {
    
    public static function url ($url) {
        require_once 'Validate.php';
        $schemes = array ('http', 'https');
        
        $v = new Validate();
        if (!$v->uri($url, array('allowed_schemes' => $schemes))){
            return false;
        }
        return true;
    }
}

