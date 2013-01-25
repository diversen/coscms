<?php

/**
 * File contains methods when no user profile system is in place
 * @package default profile
 */

/**
 * class for generating default_profile html and info when no user profile
 * system is specified. 
 * @package default profile
 */

class defaultProfile {
    
    /**
     * return empty string 
     * @return string $ret 
     */
    public static function getProfileInfo () {
        return '';
    }
    
    /**
     * gets default logout string
     * @param array $row
     * @return string $str html logout link 
     */
    public static function getLogoutHTML ($id = null) {
        $logout_url = "/account/login/index/1";
        
        
        //$profile = self::getProfile(session::getUserId());
        $db = new db();
        $profile = $db->selectOne('account', 'id', $id);
        //if (!empty($row)) return $row;
        
        
        $link = lang::translate('system_profile_logout');
        $str = html::createLink(
                $logout_url, 
                $link);
        $str.= " ($profile[email])";
        return $str;
    } 
    
    /**
     * get profile string
     * @param array|int $user row or id
     * @return string $str return empty string
     */
    public function getProfile ($id = null) {
        
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
