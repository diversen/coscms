<?php

/**
 * File containing wrappers around user class
 * Get account info. 
 * And get profile info, 
 * @package coslib
 */

/**
 * contains methods for getting account and profile info
 * depending on the profile system. 
 * 
 * @package coslib
 */

class user {
    
    
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
    
    /**
     * Gets user profile user info if a profile system is in place.
     * Profile systems must be set in main config/config.ini
     * the option array can be used to setting special options for profile module
     *
     * @param   array   user options
     * @param   array   options
     * @return  string  string showing the profile
     */
    public static function getProfileInfo (&$user){
        static $profile_object;

        if (!isset($profile_object)){
            $profile_system = config::getMainIni('profile_module');
            if (!isset($profile_system)){
                return false;
            }

            moduleLoader::includeModule ($profile_system);

            $profile_object = moduleLoader::modulePathToClassName($profile_system);
            $profile_object = new $profile_object();
            return $profile_object->getProfileInfo($user);

        }

        return $profile_object->getProfileInfo($user);
    }
    
    /**
     * method for getting a profile link in the most simple way
     * any post will have a text and a user. 
     * @param int|array $user the user id or an annon user in an array
     * @param string $text string to add to user e.g. date of the post. 
     * @return string $str profile html.  
     */
    public static function getProfileSimple($user, $text) {
        if (!is_array($user)) {
            $user = user::getAccount($user);
        }
        
        $options = array ();
        $options['display'] = 'rows';
        $options['row'] = " $text ";      
        $profile_link = self::getProfileLink($user, $options);
        return $profile_link;
    }
    
        /**
     * Gets user profile link if a profile system is in place.
     * Profile systems must be set in main config/config.ini
     * the option array can be used to setting special options for profile module
     *
     * @param   array   $user options
     * @param   array   $options
     * @return  string  $str string showing the profile
     */
    public static function getProfileLink (&$user, $options = null){
        static $profile_object;

        if (!isset($profile_object)){
            $profile_system = config::getMainIni('profile_module');
            if (!isset($profile_system)){
                return '';
            }

            moduleLoader::includeModule ($profile_system);

            $profile_object = moduleLoader::modulePathToClassName($profile_system);
            $profile_object = new $profile_object();
            $link = $profile_object->createProfileLink($user, $options);
            return $link;
        }

        return $profile_object->createProfileLink($user, $options);
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

        static $profile_object;

        if (!isset($profile_object)){
            $profile_system = config::getMainIni('profile_module');
            if (!isset($profile_system)){
                return '';
            }

            moduleLoader::includeModule ($profile_system);

            $profile_object = moduleLoader::modulePathToClassName($profile_system);
            $profile_object = new $profile_object();      
            return $profile_object->getProfileEditLink($user_id);

        }

        return $profile_object->getProfileEditLink($user_id);
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
