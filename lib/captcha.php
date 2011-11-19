<?php

/**
 * File contains very simple captcha class
 *
 * @package    coslib
 */

/**
 * Class contains contains simple methods for doing captcha
 *
 * @package    coslib
 */
class captcha {
    // {{{ static pulbic function createCaptcha()
    /**
     * very simple captcha function doing a multiplication
     * @return  string  the catcha to be used in forms
     */
    static public function createCaptcha($method = 'stringrandom'){
        if (moduleLoader::moduleExists('image_captcha')){
            include_module('image_captcha');
        }
        
        $method = get_module_ini ('image_captcha_method');
        if ($method) {
            return self::$method ();
        } else {
            return self::simpleadd();
        }
    }

    // }}}
    // {{{ static pulbic function createCaptcha()
    /**
     * very simple captcha function doing a multiplication
     * @return  string  the catcha to be used in forms
     */
    static public function simpleadd(){
        
        if (!isset($_SESSION['ctries'])) {
            $_SESSION['ctries'] = 0;
        }
        
        if ($_SESSION['ctries'] == 3) {
            $_SESSION['ctries'] = 0;
        }
        
        $_SESSION['ctries']++;
        if (isset($_SESSION['cstr']) && $_SESSION['ctries'] != '3'){
            if (get_main_ini('captcha_image_module')) {
                return self::createCaptchaImage();
            }
            return "* " . $_SESSION['cstr'];
        }
        $num_1 = mt_rand  ( 20  , 40  );
        $num_2 = mt_rand  ( 20  , 40  );
        $str = "$num_1 + $num_2 = ?";
        $res = $num_1 + $num_2;
        $_SESSION['cstr'] = $str;
        $_SESSION['ckey'] = md5($res);
        
        if (get_main_ini('captcha_image_module')) {
            return self::createCaptchaImage();
        }
        return "* " . $str;
    }

    // }}}
    // {{{ static pulbic function createCaptcha()
    /**
     * very simple captcha function doing a multiplication
     * @return  string  the catcha to be used in forms
     */
    static public function stringrandom(){
        if (!isset($_SESSION['ctries'])) {
            $_SESSION['ctries'] = 0;
        }
        
        if ($_SESSION['ctries'] == 3) {
            $_SESSION['ctries'] = 0;
        }
        
        $_SESSION['ctries']++;
        if (isset($_SESSION['cstr']) && $_SESSION['ctries'] != '3'){
            if (get_main_ini('captcha_image_module')) {
                return self::createCaptchaImage();
            }
            return "* " . $_SESSION['cstr'];
        }
        
        $_SESSION['cstr'] = $str = self::genRandomString();
        $_SESSION['ckey'] = md5($str);
        
        if (get_main_ini('captcha_image_module')) {
            return self::createCaptchaImage();
        }
        return "* " . $str;
    }

    // }}}
    public static function genRandomString() {
        $length = 8;
        $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
        $string ='';    

        for ($p = 0; $p < $length; $p++) {
            $string.= $characters[mt_rand(0, strlen($characters)-1)];
        }

        return $string;
    }
    // {{{ static public function checkCaptcha($res)

    /**
     * Method for checking if entered answer to captcha is correct
     *
     * @param   int  checks if the entered int in a captcha form
     * @return  int 1 on success and 0 on failure.
     */
    static public function checkCaptcha($res){
        if (isset($_SESSION['ckey']) && md5($res) == $_SESSION['ckey']){
            return 1;
        } else {
            return 0;
        }
    }
    // }}}
    
    static public function createCaptchaImage () {

        $options = array ('align' => 'top');
        $options['title'] = lang::translate('system_captcha_alt_image');
        return "* " . html::createImage('/image_captcha/index', $options);
    }
}