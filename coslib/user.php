<?php

/**
 * contains methods for getting account and profile info
 * depending on the profile system. 
 * 
 * @package coslib
 */

class user {
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
            $profile_system = get_main_ini('profile_module');
            if (!isset($profile_system)){
                return false;
            }

            include_module ($profile_system);

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
            $user = get_account($user);
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
            $profile_system = get_main_ini('profile_module');
            if (!isset($profile_system)){
                return '';
            }

            include_module ($profile_system);

            $profile_object = moduleLoader::modulePathToClassName($profile_system);
            $profile_object = new $profile_object();
            $link = $profile_object->createProfileLink($user, $options);
            return $link;
        }

        return $profile_object->createProfileLink($user, $options);
    }
    
}

/**
 * Gets user profile link if a profile system is in place.
 * Profile systems must be set in main config/config.ini
 * the option array can be used to setting special options for profile module
 * @param   array|int   $user user_id or full account row 
 * @param   array   $options options to use with profile system
 * @return  string  $str string with html showing the profile
 */
function get_profile_link ($user, $options = null){
    
    if (is_numeric($user)) {
        $user = get_account($user);
    }
    static $profile_object;

    if (!isset($profile_object)){
        $profile_system = get_main_ini('profile_module');
        if (!isset($profile_system)){
            return '';
        }

        include_module ($profile_system);
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
function get_profile_edit_link ($user_id){
    
    static $profile_object;

    if (!isset($profile_object)){
        $profile_system = get_main_ini('profile_module');
        if (!isset($profile_system)){
            return '';
        }

        include_module ($profile_system);

        $profile_object = moduleLoader::modulePathToClassName($profile_system);
        $profile_object = new $profile_object();      
        $link = $profile_object->getProfileEditLink($user_id);
        return $link;
    }

    return $profile_object->createProfileLink($user, $options);
}

/**
 * function for getting an account
 * @param int $id user_id 
 * @return array $row from account 
 */
function get_account ($id) {   
    $db = new db();
    $row = $db->selectOne('account', 'id', $id);
    return $row;
}