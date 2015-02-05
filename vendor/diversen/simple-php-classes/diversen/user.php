<?php

namespace diversen;
use diversen\user\profile;
use diversen\db\q as db_q;
use diversen\db;
use diversen\moduleloader;
use diversen\lang;
use diversen\session;
use diversen\conf as config;

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
     * checks if a user if locked
     * if locked set 403 error
     * @return boolean $res true if locked else false
     */
    public static function lockedSet403 ($message = null) {
        if (self::locked()) {
            moduleloader::$status['403'] = true;
            if (!$message) {
                $message = lang::translate('You can not access this page, because your account has been locked!');
            }
            moduleloader::$message = $message;
            return true;
        }
        return false;
    }
    
    /**
     * is account locked
     * @return boolean
     */
    public static function locked () {
        $user = self::getAccount();
        if (empty($user)) {
            return false;
        }
        if ($user['locked'] == 1) {
            return true;
        }
        return false;
    }
    
    /**
     * function for getting an account
     * @param int $id user_id 
     * @return array $row from account 
     */
    public static function getAccount ($id = null) {
        if (!$id) { 
            $id = session::getUserId();
        }
        $db = new db();
        $row = $db->selectOne('account', 'id', $id);
        return $row;
    }
    
    
    /**
     * inits profile system. Include profile module
     */
    public static function initProfile () {
        if (!isset(self::$profile_object)){

            $profile_system = config::getMainIni('profile_module');
            if (!isset($profile_system) || !moduleloader::isInstalledModule($profile_system)){
                self::$profile_object = new profile();
                return;
            } else {
                moduleloader::includeModule ($profile_system);
                self::$profile_object = new $profile_system();
            }
        }
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
    public static function getProfile($user, $text = '', $options = array ()) {
        
        self::initProfile();
        if (!is_array($user)) {
            $user = self::getAccount($user);
        }

        return self::$profile_object->getProfile($user, $text, $options);       
    }
    
    /**
     * get a link to a profile page
     * @return type
     */
    public static function getProfileLink ($user) {
                
        self::initProfile();
        if (!is_array($user)) {
            $user = self::getAccount($user);
        }

        return self::$profile_object->getProfileLink($user);    
    }
    
}
