<?php

namespace diversen;
use diversen\session;
/**
 * File containg methods for doing http work
 * @package http
 */

/**
 * class http
 * @package http
 */
class http {
    
    /**
     * simple function for creating prg pattern. 
     * (Keep state when reloading browser and resends forms etc.) 
     * @param int $last
     */
    public static function prg ($max_time = 0){
        
        // POST
        // genrate a session var holding the _POST
        if ($_SERVER['REQUEST_METHOD'] == 'POST'){
            $uniqid = uniqid();
            $_SESSION['post'][$uniqid] = $_POST;
            $_SESSION['post'][$uniqid]['prg_time'] = time();            
            $_SESSION['REQUEST_URI'] = $_SERVER['REQUEST_URI'];

            header("HTTP/1.1 303 See Other");
            $location = $_SERVER['REDIRECT_URL'] . '?prg=1&uniqid=' . $uniqid;
            self::locationHeader($location);
        }

        
        if (!isset($_SESSION['REQUEST_URI'])){
            $_SESSION['post'] = null;
        } else {
            if (isset($_GET['prg'])){
                $uniqid = $_GET['uniqid'];
                
                if (isset($_SESSION['post'][$uniqid])) {
                    if ( $max_time && ($_SESSION['post'][$uniqid]['prg_time'] + $max_time) < time() ) {
                        unset($_SESSION['post'][$uniqid]);
                    } else {           
                        $_POST = $_SESSION['post'][$uniqid];
                    }
                }
            } else {
                @$_SESSION['REQUEST_URI'] = null;
            }
        }
    }
    
    /**
     * simple function for creating prg pattern. 
     * (Keep state when reloading browser and resends forms etc.) 
     * @param int $last
     */
    public static function prgSinglePost (){
        
        // POST
        // genrate a session var holding the _POST
        if ($_SERVER['REQUEST_METHOD'] == 'POST'){
            $uniqid = uniqid();
            $_SESSION['post'][$uniqid] = $_POST;
            $_SESSION['post'][$uniqid]['prg_time'] = time();            
            $_SESSION['REQUEST_URI'] = $_SERVER['REQUEST_URI'];

            header("HTTP/1.1 303 See Other");
            $location = $_SERVER['REDIRECT_URL'] . '?prg=1&uniqid=' . $uniqid;
            self::locationHeader($location);
        }

        
        if (!isset($_SESSION['REQUEST_URI'])){
            $_SESSION['post'] = null;
        } else {
            if (isset($_GET['prg'])){
                $uniqid = $_GET['uniqid'];
                
                if (isset($_SESSION['post'][$uniqid])) {        
                    $_POST = $_SESSION['post'][$uniqid];
                    unset($_SESSION['post'][$uniqid]);
                }
                
            } else {
                @$_SESSION['REQUEST_URI'] = null;
            }
        }
    }
    
    /**
     * 
     * method for sending cache headers when e.g. sending images from db
     * @param int $expires the expire time in seconds
     */
    public static function cacheHeaders ($expires = null){

        // one month
        if (!$expires) {
            $expires = 60*60*24*30;
        }
        header("Pragma: public");
        header("Cache-Control: maxage=".$expires);
        header('Expires: ' . gmdate('D, d M Y H:i:s', time()+$expires) . ' GMT');

    }
    
    /**
     * send a location header
     * @param type $location the location, e.g. /content/view/article/3
     * @param type $message an action message 
     * @param type $post_id if an post id is set we save the post in a session.
     */
    public static function locationHeader ($location, $message = null, $post_id = null) {
        if (isset($message)) {
            session::setActionMessage($message);
        }

        if (isset($post_id)) {
            $_SESSION[$id] = $_POST;
        }
        
        $header = "Location: $location";
        header($header);
        die();    
    }
    
    /**
     * function for redirecting to ssl
     */
    public static function sslHeaders () {
        if ($_SERVER['SERVER_PORT'] != 443){
            $redirect = "https://" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
            header("HTTP/1.1 301 Moved Permanently");
            self::locationHeader($redirect);
        }     
    }
    
   /**
    * function for redirecting to a exact serverneme.
    * e.g. you have www.example.com and example.com as servernames
    * you want only to allow example.com.
    * call server_recirect('example.com')
    *
    * @param string $server_redirect server_name to redirect to.
    */
    public static function redirectHeaders ($server_redirect) {
        if($_SERVER['SERVER_NAME'] != $server_redirect){
            if ($_SERVER['SERVER_PORT'] == 80) {
                $scheme = "http://";
            } else {
                $scheme = "https://";
            }

            $redirect = $scheme . $server_redirect . $_SERVER['REQUEST_URI'];
            header("HTTP/1.1 301 Moved Permanently");
            self::locationHeader($redirect);
        }
    }
    
    /**
     * function for checking if we need to redirect with 301
     * if param url is not equal to current url, then 
     * we redirect to url given
     * 
     * @param string $url the rul to check against and redirect to.
     * @param array $options set a action message with array ('message' => 'message');  
     */
    public static function permMovedHeader ($redirect, $options = array()) {
        if (isset($options['message'])) {
            session::setActionMessage($options['message']);
        }
        if ($_SERVER['REQUEST_URI'] != $redirect) {
            header("HTTP/1.1 301 Moved Permanently");
            self::locationHeader($redirect);
        }
    }
    
    /**
     * send temorarily unavailable headers
     * and displays an error message found in:
     * COS_HTDOCS . 'temporarily_unavailable.inc'
     */
    public static function temporarilyUnavailable () {
        header('HTTP/1.1 503 Service Temporarily Unavailable');
        header('Status: 503 Service Temporarily Unavailable');
        header('Retry-After: 300'); // 5 minutes in seconds
        include_once _COS_HTDOCS . "/temporarily_unavailable.inc";
        die();
    }
}

