<?php

/**
 * File containing methods for getting a user profile connected to the 
 * account system. This is made in order to make it possible to switch
 * profile system for websites. 
 * 
 * @package user
 */

/**
 * Class contains methods for getting account and profile info
 * depending on the profile system. 
 * 
 * @package user
 */

class user {
    
    /**
     * var holding the profile object. 
     * @var object 
     */
    public static $profile_object = null;
    
    /**
     * checks if a 'user_id' owns 'id' of a 'table' meaning that he should
     * be able e.g. edit and delete this row. 
     * @param string $table able name
     * @param string $id primary id of table 
     * @param type $user_id the users account id
     * @return array|false $row if row was found else false
     */
    public static function ownID ($table, $id, $user_id) {
        $row = db_q::setSelect($table)->
                filter('id =', $id)->
                condition('AND')->
                filter('user_id = ', $user_id)->
                fetchSingle();
        if (empty($row)) {
            return false;
        } 
        
        return $row;
        
    }
    
    /**
     * function for getting an account
     * @param int $id user_id 
     * @return array $row from account 
     */
    public static function getAccount ($id = null) {
        if (!$id) $id = session::getUserId ();
        $db = new db();
        $row = $db->selectOne('account', 'id', $id);
        return $row;
    }
    
    /**
     * gets account from email
     * @param string $email
     * @return array $row
     */
    public static function getAccountFromEmail ($email = null) {
        $db = new db();
        $row = $db->selectOne('account', 'email', $email);
        return $row;
    }
    
    
    /**
     * inits profile system. Include profile module
     */
    public static function initProfile () {
        if (!isset(self::$profile_object)){
            
            $profile_system = config::getMainIni('profile_module');
            if (!isset($profile_system) || !moduleloader::isInstalledModule($profile_system)){
                include_once "coslib/defaultProfile.php";
                $profile_system = 'defaultProfile';
            }
            
            moduleloader::includeModule ($profile_system);
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
        return self::$profile_object->getProfileInfo($user);
    }
    
    /**
     * get all profile info where special chars are encoded
     * @param mixed $user (account row or user_id)
     * @return array $row 
     */
    public static function getProfileInfoEscaped ($user) {
        $profile = self::getProfileInfo($user);
        return html::specialEncode($profile);
    }
    
    /**
     * method for getting html for logging out a user. 
     * @param param $row
     * @return string $html
     */
    public static function getLogoutHTML ($row, $type = null) {        
        self::initProfile();
        return self::$profile_object->getLogoutHTML($row, $type);
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
    public static function getProfile($user, $text = '', $options = array ()) {
        
        self::initProfile();
        if (!is_array($user)) {
            $user = user::getAccount($user);
        }

        return self::$profile_object->getProfile($user, $text, $options);       
    }
    
    /**
     * gets a anon profile
     * @param array $user anon user info. At least we should have array ('email'  => 'email');
     * @param string $text string to add to user e.g. date of the post. 
     * @return string $str profile html.  
     */
    public static function getProfileAnon($user, $text = '') {
        self::initProfile();
        return self::$profile_object->getProfile($user, $text);       
    }
    
    /**
     * same as getProfile. But we add a date to be formatted
     * @param mixed $user array with user info or a user id
     * @param string $date mysql timestamp
     * @param string $format timestamp format
     * @return string $str simple table with user profile
     */
    public static function getProfileWithDate ($user, $date, $format = 'date_format_long') {
        $date_str = time::getDateString($date, $format);
        self::initProfile();
        if (!is_array($user)) {
            $user = user::getAccount($user);
            
        }
        return self::$profile_object->getProfile($user, $date_str);  
    }
    
    /**
     * same as getProfile. But we add a date to be formatted
     * @param array $user anon user info (at least we should have an email)
     * @param string $date mysql timestamp
     * @param string $format timestamp format
     * @return string $str simple table with user profile
     */
    public static function getProfileWithDateAnon ($user, $date, $format = 'date_format_long') {
        $date_str = time::getDateString($date, $format);
        self::initProfile();
        return self::$profile_object->getProfileAnon($user, $date_str);  
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

