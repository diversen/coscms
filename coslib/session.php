<?php

/**
 * File contains contains class for doing checks on seesions
 *
 * @package    session
 */

/**
 * Class contains contains methods for setting sessions
 *
 * @package    session
 */
class session {
    /**
     * method for initing a session
     * set in_session and start_time of session
     * checks if we use memcached which is a good idea
     */
    public static function initSession(){
        
        session::setSessionIni();
        session::setSessinHandler();

        session_start();
        session::checkSystemCookie();

        // if 'started' is set for previous request
        //we truely know we are in 'in_session'
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
    
    /**
     * sets ini for 
     * session.cooke_lifetime, session.cookie_path,session.cookie_domain
     */
    public static function setSessionIni () {
        
        // figure out session time
        $session_time = config::getMainIni('session_time');
        if (!$session_time) $session_time = '0';
        ini_set("session.cookie_lifetime", $session_time);

        $session_path = config::getMainIni('session_path');
        if ($session_path) {
            ini_set("session.cookie_path", $session_path);
        }

        $session_host = config::getMainIni('session_host');
        if ($session_host){
            ini_set("session.cookie_domain", $session_host);
        }
    }
    
    /**
     * sets session handler. 
     * only memcahce if supported
     */
    public static function setSessinHandler () {
        
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
    }

    /**
     * checks if there is a cookie we can use for log in. If cookie exists 
     * we will log in the user
     * 
     * You can run trigger events which needs to be set in session_events
     * in config/config.ini 
     * 
     * @return void
     */
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
            $row = $db->selectOne ('system_cookie', 'cookie_id', @$_COOKIE['system_cookie']);
            
            // we got a cookie that equals one found in database
            if (!empty($row)){
                $days = session::getCookiePersistentDays();
                // delete system_cookies that are out of date. 
                
                $sql = "
                    DELETE FROM `system_cookie` WHERE 
                        account_id = '$row[account_id]' AND 
                        last_login < now() - interval $days day";
                $db->rawQuery($sql);
                
                // on every cookie login we update the cookie id and
                // delete every user cookie that is older than 1 month
                
                $new_cookie_id = random::md5();
                $values = array ('cookie_id' => $new_cookie_id);
                $search = array ('cookie_id' => $_COOKIE['system_cookie']);
                
                // update new cookie id in db
                $db->update('system_cookie', $values, $search);
                
                // update browser cookie
                $cookie_time = session::getCookiePersistentSecs();              
                $timestamp = time() + $cookie_time;
                setcookie('system_cookie', $new_cookie_id, $timestamp, '/');
                
                // get account which is connected to account id
                $account = $db->selectOne('account', 'id', $row['account_id']);
                
                // user with account
                if ($account){
                    
                    $_SESSION['id'] = $account['id'];
                    $_SESSION['admin'] = $account['admin'];
                    $_SESSION['super'] = $account['super'];
                    $_SESSION['type'] = $account['type'];
                    
                    $args = array (
                        'action' => 'account_login',
                        'user_id' => $account['id']
                    );

                    // trigger session_events
                    $login_events = config::getMainIni('session_events');
                    event::getTriggerEvent(
                        $login_events, $args
                    );
                } else {
                    // keep anon user in session
                    $_SESSION['id'] = 0;
                    $_SESSION['type'] = 'anon';
                }
            } 
        } else {
            
        }
    }

    /**
     * sets a system cookie. 
     * @param int $user_id
     * @return boolean $res true on success and false on failure. 
     */
    public static function setSystemCookie($user_id){

        $uniqid = random::md5();  
        
        $cookie_time = session::getCookiePersistentSecs();              
        
        $timestamp = time() + $cookie_time;
        setcookie('system_cookie', $uniqid, $timestamp, '/');
        
        $db = new db();
        
        // cookie_id is indexed with KEY `cookie_id_index` (`cookie_id`)
        $row = $db->selectOne ('system_cookie', 'cookie_id', @$_COOKIE['system_cookie']);

        // place cookie in system cookie table
        // last login is auto updated
        $values = array (
            'account_id' => $user_id, 
            'cookie_id' => $uniqid);
        
        return $db->insert('system_cookie', $values);
    }
    
    /**
     * return persistent cookie time in secs
     * @return int $time in secs
     */
    public static function getCookiePersistentSecs () {
        
        $days = config::getMainIni('cookie_time');        
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
        
        return $cookie_time;
    }
    
        /**
     * return persistent cookie time in secs
     * @return int $time in secs
     */
    public static function getCookiePersistentDays () {
        
        $days = config::getMainIni('cookie_time');        
        if ($days == -1) {
            // ten years
            $cookie_time = 365 * 10;
        }
        
        else if ($days >= 1) {
            $cookie_time = $days;
        }
        
        else {
            $cookie_time = 0;
        }
        
        return $cookie_time;
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
        // only keep one system cookie (e.g. if user clears his cookies)
        $db = new db();
        $db->delete('system_cookie', 'cookie_id', @$_COOKIE['system_cookie']);
        
        setcookie ("system_cookie", "", time() - 3600, "/");
        unset($_SESSION['id'], $_SESSION['admin'], $_SESSION['super'], $_SESSION['account_type']);
        session_destroy();
    }
    
    /**
     * method for killing all cookie sessions
     * unsets the system cookie and unsets session credentials
     * @param int $user_id
     */
    public static function killAllSessions ($user_id){
        // only keep one system cookie (e.g. if user clears his cookies)
        $db = new db();
        $db->delete('system_cookie', 'account_id', $user_id);
        
        setcookie ("system_cookie", "", time() - 3600, "/");
        unset($_SESSION['id'], $_SESSION['admin'], $_SESSION['super'], $_SESSION['account_type']);
        session_destroy();
    }
    
    /**
     * you can specify one event in your main ini (config/config.ini) file.
     * session_events:  
     * 
     * e.g. $args = array (
     *                  'action' => 'account_login',
     *                  'user_id' => $account['id']
     *              );
     * 
     * This is called on a login  
     */
    public static function __events () {
        
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
     * @return int $secs time in session measured in secs
     */
    static public function getSessionTime(){
        if (!isset($_SESSION['start_time'])){
            return 0;
        } else {
            return time() - $_SESSION['start_time'];
        }
    }
    
    /**
     * sets a persistent session var
     * @param string $name index of var
     * @param mixed $var array or object or string or int
     */
    public static function setPersistentVar ($name, $var) {
        if (!isset($_SESSION['system_persistent_var'])) $_SESSION['system_persistent_var'] = array ();
        $_SESSION['system_persistent_var'][$name] = serialize($var);
        
    }
    
    /**
     * returns a persistent var from index name
     * @param string $name index of var
     * @param boolean $clean true will clean var from session, false will not 
     * @return mixed $ret array or object or string or int
     */
    public static function getPersistentVar($name, $clean = true) {
        if (!isset($_SESSION['system_persistent_var'][$name])) {
            return null;
        }
        
        $ret = unserialize($_SESSION['system_persistent_var'][$name]);
        if ($clean) { 
            unset($_SESSION['system_persistent_var'][$name]);
        }
        return $ret;
    }

    /**
     * method for setting an action message. Used when we want to tell a
     * user what happened if he is e.g. redirected. You can force to
     * close the session, which means you can write to screen after you
     * session vars has been set. This should be avoided.  
     *
     * @param string $message the action message.
     * @param boolean $close to close session writing or not
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
     * You can template this message by adding a template_get_action_message
     * in your template. 
     * @return string $str actionMessage
     */
    public static function getActionMessage(){
        if (isset($_SESSION['system_message'])){
            $messages = $_SESSION['system_message'];
            $ret = '';
            if (is_array ($messages)){
                if (function_exists('template_get_action_message')) {
                    $ret = template_get_action_message ($messages);
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
     * @return  boolean $res true or false
     */
    public static function isSuper(){
        if ( isset($_SESSION['super']) && ($_SESSION['super'] == 1)){
            return true;
        } else {
            return false;
        }
    }

    /**
     * method for testing if user is admin or not
     * @return  boolean $res true or false
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
     * return   mixed   $res null or string if null then user is not logged in
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
        if ( isset($_SESSION['id']) && $_SESSION['id'] != 0 ) {
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
     * Checks access control against a module ini setting 
     * e.g. in blog.ini default is: blog_allow = 'admin'
     * then you should call checkAccessControl('blog_allow') in order to prevent
     * others than 'admin' in using the page
     * 
     * If user does not have perms then the default 403 page will be set, 
     * and a 403 header will be sent. 
     * 
     * @param   string  $allow user or admin or super
     * @param   boolean $setErrorModule set error module or not
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
                    moduleloader::$status[403] = 1;
                }
                return false;
            }
        }


        // check other than users. 'admin' and 'super' is set
        // in special session vars when logging in. User is
        // someone who just have a valid $_SESSION['id'] set
        if (!isset($_SESSION[$allow]) || $_SESSION[$allow] != 1){
            if ($setErrorModule){
                moduleloader::$status[403] = 1;
            }
            return false;
        } else {
            return true;
        }
    }
    
    /**
     * checks access for user, admin or super. It 
     * Loads error module if user level is not present
     * @return boolean $res true if admin else false. 
     */
    public static function checkAccess ($type = null) {
        $res = false;
        if ($type == 'user') {
            $res = session::isUser();
        }
        
        if ($type == 'admin') {
            $res = session::isAdmin();
        }
        
        if ($type == 'super') {
            $res = session::isSuper();
        }
        
        if (!$res) {
            moduleloader::$status[403] = 1;
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
            moduleloader::includeModule('account');
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
