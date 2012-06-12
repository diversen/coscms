<?php

/**
 * if no profile is in place this is the default profile
 * which does not do much. It generates a logout link if you are logged out
 * And it returns empty profile links.  
 */
class default_profile {
    
    /**
     * 
     * @return string $ret empty string 
     */
    public static function getProfileInfo () {
        return '';
    }
    
    /**
     * gets loout string
     * @param array $row
     * @return string $str html logout link 
     */
    public static function getLogoutHTML ($row = null) {
        $logout_url = "/account/login/index/1";
        $str = html::createLink(
                $logout_url, 
                lang::translate('system_profile_logout'));     
        return $str;
    } 
    
    /**
     * get profile string
     * @param array|int $user row or id
     * @return string $str return empty string
     */
    public function getProfile ($user = null) {
        return '';
    }
    
    /**
     * get simple profile string
     * @param array|int $user row or id
     * @return string $str return empty string
     */
    public function getProfileSimple ($user = null) {
        return '';
    }
}
