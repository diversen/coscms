<?php

/**
 * File containing methods for getting a user profile connected to the 
 * account system. This is made in order to make it possible to switch
 * profile system for websites. 
 * 
 * @package coslib
 */

/**
 * Class contains methods for getting account and profile info
 * depending on the profile system. 
 * 
 * @package coslib
 */

class user {
    
    /**
     * var holding the profile object. 
     * @var object 
     */
    public static $profile_object = null;
    
    
    /**
     * function for getting an account
     * @param int $id user_id 
     * @return array $row from account 
     */
    public static function getAccount ($id) {   
        $db = new db();
        $row = $db->selectOne('account', 'id', $id);
        return $row;
    }
    
    public static function initProfile () {
        if (!isset(self::$profile_object)){
            
            $profile_system = config::getMainIni('profile_module');
            if (!isset($profile_system) || !moduleLoader::isInstalledModule($profile_system)){
                include_once "coslib/default_profile.php";
                $profile_system = 'default_profile';
            }
            
            moduleLoader::includeModule ($profile_system);
            self::$profile_object = new $profile_system();
        }
    }
    
    /**
     * Gets user profile info if a profile system is in place.
     * E.g. to be showed on login page when logged in. 
     *
     * @param   array   $user options
     * @return  string  $str html or text showing info about the profile
     */
    public static function getProfileInfo ($user){
        self::initProfile();
        if (!is_array($user)) {
            $user = user::getAccount($user);
        }

        return self::$profile_object->getProfileInfo($user);
    }
    
    /**
     * method for getting html for logging out a user. 
     * @param param $row
     * @return string $html
     */
    public static function getLogoutHTML ($row) {
        
        self::initProfile();
        return self::$profile_object->getLogoutHTML($row);
    }
    
    /**
     * method for getting a profile link in the most simple way
     * e.g. any blog post will have a text (the post date) and a user 
     * profile link or box. 
     * 
     * @param array $user the user array or an annon user in an array
     *                    can be an array from account table or
     *                    it can be an anoo user comment. 
     *                    if the user is anon then the user_id = '0'
     *                    and supplied can be homepage and email. 
     *                    e.g. in comment.  
     * 
     * 
     * @param string $text string to add to user e.g. date of the post. 
     * @return string $str profile html.  
     */
    public static function getProfile($user, $text = '') {
        
        self::initProfile();
        if (!is_array($user)) {
            $user = user::getAccount($user);
        }

        return self::$profile_object->getProfile($user, $text);       
    }
    
    /**
     * method for getting a profile link in the most simple way
     * e.g. any blog post will have a text (the post date) and a user 
     * profile link or box. 
     * 
     * @param array $user the user array or an annon user in an array
     *                    can be an array from account table or
     *                    it can be an anoo user comment. 
     *                    if the user is anon then the user_id = '0'
     *                    and supplied can be homepage and email. 
     *                    e.g. in comment.  
     * 
     * 
     * @param string $text string to add to user e.g. date of the post. 
     * @return string $str profile html.  
     */
    public static function getProfileSimple($user, $text = '') {
        
        self::initProfile();
        if (!is_array($user)) {
            $user = user::getAccount($user);
        }
        return self::$profile_object->getProfileSimple($user, $text);       
    }

    
    /**
     * Gets user profile link if a profile system is in place.
     * Profile systems must be set in main config/config.ini
     * the option array can be used to setting special options for profile module
     * 
     * @param   array   $user_id the user in question
     * @return  string  $string string showing the profile
     */
    public static function getProfileEditLink ($user_id){
        self::initProfile();
        return self::$profile_object->getProfileEditLink($user_id);
    }
    
}

/**
 * @deprecated
 * @param type $user
 * @param type $options
 * @return type 
 */
function get_profile_link ($user, $options = null){
    return user::getProfileLink($user);
}
/**
 * @deprecated
 * @param type $user_id
 * @return type 
 */
function get_profile_edit_link ($user_id){
    return user::getProfileEditLink($user_id);
}
