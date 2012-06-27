<?php

/**
 * File contains contains class for doing checks on seesions
 *
 * @package    coslib
 */

/**
 * Class contains contains methods for setting sessions
 *
 * @package    coslib
 */
class session {
    // {{{ static public function initSession()
    /**
     * method for initing a session
     * set in_session and start_time of session
     * checks if we use memcached which is a good idea
     */
    static public function initSession(){
        //return;
        // figure out session time
        $session_time = config::getMainIni('session_time');
        if ($session_time) {
            ini_set("session.cookie_lifetime", $session_time);
        }

        $session_path = config::getMainIni('session_path');
        if ($session_path) {
            ini_set("session.cookie_path", $session_path);
        }

        $session_host = config::getMainIni('session_host');
        if ($session_host){
            ini_set("session.cookie_domain", $session_host);
        }

        // use memcache if available
        $handler = config::getMainIni('session_handler');
        if ($handler == 'memcache'){
            $host = config::getMainIni('memcache_host');
            if (!$host) {
                $host = 'localhost';
            }
            $port = config::getMainIni('memcache_port');
            if (!$port) {
                $port = '11211';
            }
            $query = config::getMainIni('memcache_query');
            if (!$query) {
                $query = 'persistent=0&weight=2&timeout=2&retry_interval=10';
            }
            $session_save_path = "tcp://$host:$port?$query,  ,tcp://$host:$port  ";
            ini_set('session.save_handler', 'memcache');
            ini_set('session.save_path', $session_save_path);
        }

        session_start();
        session::checkSystemCookie();

        // if 'started' is set for previous request
        // we truely know we are in 'in_session'
        if (isset($_SESSION['started'])){
            $_SESSION['in_session'] = 1;
        }

        // if not started we do not know for sure if session will work
        // we destroy 'in_session'
        if (!isset($_SESSION['started'])){
            $_SESSION['started'] = 1;
            $_SESSION['in_session'] = null;
        }

        // we set a session start time
        if (!isset($_SESSION['start_time'])){
            $_SESSION['start_time'] = time();
        }
    }

    public static function checkSystemCookie(){
        
        if (isset($_COOKIE['system_cookie'])){

            // user is in session. Can only be this after first request. 
            if (isset($_SESSION['in_session'])){
                return;
            }

            if (isset($_SESSION['id'])){
                // user is logged in we return
                return;
            }
            
            

            $db = new db();
            //print_r($db); die;
            $db->connect();
            $row = $db->selectOne ('system_cookie', 'cookie_id', $_COOKIE['system_cookie']);

            
            if (!empty($row)){
                $account = $db->selectOne('account', 'id', $row['account_id']);
                if ($account){
                    $_SESSION['id'] = $account['id'];
                    $_SESSION['admin'] = $account['admin'];
                    $_SESSION['super'] = $account['super'];
                    
                    $args = array (
                        'action' => 'account_login',
                        'user_id' => $account['id']
                    );
                    
                    cos_debug("Notice: Fireing session events");
                    event::getTriggerEvent(
                        config::getMainIni('session_events'), $args
                    );
                }
            } 
            //return;
        }
    }


    /**
     * sets a system cookie. 
     * @param int $user_id
     * @return boolean $res true on success and false on failure. 
     */
    public static function setSystemCookie($user_id){
        
        cos_debug("Notice: Settings system cookie");
        $uniqid = uniqid();
        $uniqid= md5($uniqid);

        $days = config::getMainIni('cookie_time');
        
        // calculate days into seconds
        if ($days == -1) {
            // ten years
            $cookie_time = 3600 * 24 * 365 * 10;
        }
        
        else if ($days >= 1) {
            $cookie_time = 3600 * 24 * $days;
        }
        
        else {
            $cookie_time = 0;
        }
            
        $timestamp = time() + $cookie_time;
        setcookie('system_cookie', $uniqid, $timestamp, '/');
        
        $db = new db();

        // only keep one system cookie (e.g. if user clears his cookies)
        $db->delete('system_cookie', 'account_id', $user_id);
        $values = array ('account_id' => $user_id , 'cookie_id' => $uniqid, 'timestamp' => $timestamp);
        return $db->insert('system_cookie', $values);
    }
    
    /**
     * try to get system cookie
     * @return false|string     retruns cookie md5 or false    
     */
    public static function getSystemCookie (){
        if (isset($_COOKIE['system_cookie'])){
            return $_COOKIE['system_cookie'];
        } else {
            return false;
        }
    }

    /**
     * method for killing a session
     * unsets the system cookie and unsets session credentials
     */
    public static function killSession (){
        setcookie ("system_cookie", "", time() - 3600, "/");
        unset($_SESSION['id'], $_SESSION['admin'], $_SESSION['super'], $_SESSION['account_type']);
        session_destroy();
    }

    /**
     * method for testing if user is in session or not
     * @return  boolean true or false
     */
    static public function isInSession(){
        if (isset($_SESSION['in_session'])){
            return true;
        } else {
            return false;
        }
    }

    /**
     * method for getting how long user has been in session
     *
     * @return int secs in session
     */
    static public function getSessionTime(){
        if (!isset($_SESSION['start_time'])){
            return 0;
        } else {
            return time() - $_SESSION['start_time'];
        }
    }

    /**
     * method for setting an action message. Used when we want to tell a
     * user what happened if he is redirected
     *
     * @param string the action message.
     */
    public static function setActionMessage($message, $close = false){
            if (!isset($_SESSION['system_message'])) {
                $_SESSION['system_message'] = array ();
            } 
            
            $_SESSION['system_message'][] = $message;
            
            if ($close) {
                session_write_close();
            }
    }

    /**
     * method for reading an action message
     *
     * @return string current set actionMessage
     */
    public static function getActionMessage(){
        if (isset($_SESSION['system_message'])){
            $messages = $_SESSION['system_message'];
            $ret = '';
            if (is_array ($messages)){
                if (function_exists('template_get_action_message')) {
                    $ret = template_geT_action_message ($messages);
                } else {
                
                    foreach ($messages as $message) {
                        $ret.= $message;
                    }
                }
            }
            unset($_SESSION['system_message']);
            return $ret;
        }
        return null;
    }

    /**
     * method for testing if user is in super or not
     *
     * @return  boolean true or false
     */
    static public function isSuper(){
        if ( isset($_SESSION['super']) && ($_SESSION['super'] == 1)){
            return true;
        } else {
            return false;
        }
    }

    /**
     * method for testing if user is admin or not
     *
     * @return  boolean true or false
     */
    static public function isAdmin(){
        if ( isset($_SESSION['admin']) && ($_SESSION['admin'] == 1)){
            return true;
        } else {
            return false;
        }
    }

    /**
     * method for getting users level (null, user, admin, super)
     * return   mixed   null or string if null then user is not logged in
     *                  if string we get the users highest level, user, admin or super.
     */
    public static function getUserLevel(){
        if (self::isSuper()){
            return "super";
        }
        if (self::isAdmin()){
            return "admin";
        }
        if (self::isUser()){
            return "user";
        }
        return null;
    }
    
    /**
     * method for testing if user is loged in or not
     *
     * @return  boolean true or false
     */
    static public function isUser(){
        if ( isset($_SESSION['id']) ){
            return true;
        } else {
            return false;
        }
    }

    /**
     * checks $_SESSION['id'] and if set it will return 
     * method for getting a users id
     *
     * @return  mixed false if no user id or the users id. 
     */
    static public function getUserId(){
        if ( !isset($_SESSION['id']) || empty($_SESSION['id']) ){
            return false;
        } else {
            return $_SESSION['id'];
        }
    }

    /**
     * checkAccessControl($allow)
     * checks user level:
     *      super has access to all,
     *      admin has access to more
     *      user has access to less
     *      null has access to least
     *
     * @param   string  user or admin or super
     * @return  boolean true if user has required accessLevel.
     *                  false if not. 
     * 
     */
    static public function checkAccessControl($allow, $setErrorModule = true){
        
        // we check to see if we have a ini setting for 
        // the type to be allowed to an action
        // allow_edit_article = super
        $allow = config::getModuleIni($allow);

        // is allow is empty means the access control
        // is not set and we grant access
        if (empty($allow)) {
            return true;
        }
        
        // anon is anonymous user. Anyone if allowed
        if ($allow == 'anon') {
            return true;
        }

        // check if we have a user
        if ($allow == 'user'){
            if(self::isUser()){
                return true;
            } else {
                if ($setErrorModule){
                    moduleLoader::$status[403] = 1;
                }
                return false;
            }
        }


        // check other than users. 'admin' and 'super' is set
        // in special session vars when logging in. User is
        // someone who just have a valid $_SESSION['id'] set
        if (!isset($_SESSION[$allow]) || $_SESSION[$allow] != 1){
            if ($setErrorModule){
                moduleLoader::$status[403] = 1;
            }
            return false;
        } else {
            return true;
        }
    }

    /**
     * method for relocate user to login, and after correct login 
     * redirect to the page where he was. You can set message to
     * be shown on login screen.
     *  
     * @param string $message 
     */
    public static function loginThenRedirect ($message){
        unset($_SESSION['redirect_on_login']);
        if (!session::isUser()){
            moduleLoader::includeModule('account');
            $_SESSION['redirect_on_login'] = $_SERVER['REQUEST_URI'];
            session::setActionMessage($message);
            account::redirectDefault();
            die;
        }
    }
}



/**
 * simple method for saving $_POST vars to session
 * @param   string  $id the id of the saved <code>$_POST</code> 
 *                  used when retriving the <code>$_POST</code>
 */
function save_post ($id){
     $_SESSION[$id] = $_POST;
}

/**
 * method for loading <code>$_POST</code> vars from session
 * @param   string  $id id of the post to load. 
 * @return  boolean $res true on success and false if no session var was 
 *                  found with the given id
 */
function load_post($id){
    if (!isset($_SESSION[$id])) {
        return false;
    }
    $_POST = $_SESSION[$id];
    return true;
}

/**
 * get a session var from id. 
 * @param mixed $id the id of the session var to fetch
 * @return mixed $res the var which was set or false 
 */
function get_post($id) {
    if (!isset($_SESSION[$id])) {
        return false;
    }
    return $_SESSION[$id];
}

/**
 * function for unsetting a session var
 * @param type $id the id of the session var
 */
function unset_post ($id) {
    unset($_SESSION[$id]);
}
