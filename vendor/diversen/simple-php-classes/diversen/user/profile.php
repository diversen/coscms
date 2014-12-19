<?php

namespace diversen\user;
use diversen\lang;
use diversen\html;
/**
 * File contains methods when no user profile system is in place
 * @package user
 */

/**
 * class for generating user html and info when no user profile
 * system is specified. 
 * @package user
 */

class profile {
    
    /**
     * return empty string 
     * @return string $ret 
     */
    public static function getProfileInfo () {
        return '';
    }
    
        /**
     * retrun profile as a link
     * @param   int     $id
     * @return  string  $str html profile link
     */
    public static function getProfileAdminLink ($id){
        //$profile = self::getAccountProfileFromId($id);
        $str = html::createLink(
            "/account/admin/edit/$id", 
            lang::translate('(Admin) Edit profile'));
        return $str;
    }
    
    /**
     * gets default logout string
     * @param array $row
     * @return string $str html logout link 
     */
    public static function getLogoutHTML ($id = null, $type = '') {
        $logout_url = "/account/logout";
        $redirect = $_SERVER['REQUEST_URI'];
        $logout_url.= "?redirect=" . rawurlencode($redirect);
              
        $link = lang::translate('system_profile_logout');
        $str = html::createLink(
                $logout_url, 
                $link);
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
