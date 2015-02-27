<?php
namespace diversen\user;

interface Iprofile  {
        /**
     * return empty string 
     * @return string $ret 
     */
    public static function getProfileInfo ($user_id);
    
    /**
     * retrun profile as a link
     * @param   int     $id
     * @return  string  $str html profile link
     */
    public static function getProfileAdminLink ($user_id);
    
    /**
     * gets default logout string
     * @param array $row
     * @return string $str html logout link 
     */
    public static function getLogoutHTML ($user_id, $text = '');
    
    /**
     * get profile string
     * @param array|int $user row or id
     * @return string $str return empty string
     */
    public static function getProfile ($user_id = null, $text = '') ;
    
    /**
     * get simple profile string
     * @param array|int $user row or id
     * @return string $str return empty string
     */
    public static function getProfileSimple ($user_id = null, $text = '') ;
}
