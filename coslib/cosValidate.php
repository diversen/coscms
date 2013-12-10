<?php

/**
 * file contains validating class
 * @package validate
 */
/**
 * @ignore
 */

require_once 'Validate.php';
/**
 * class for validating most common thing: URL's and emails. 
 * 
 * @package validate
 */
class cosValidate {
    /**
     * method for validating email with php filter_var function
     * @param   string  $email email
     * @return  boolean $res true on success and false on failure 
     */
    public static function email ($email){
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)){
            return false;
        }
        return true;
    }

    /**
     * method for validating email with php filter_var function
     * @param   string  $url the url to validate
     * @return  boolean $res true on success and false on failure
     */
    public static function urlWithFilter ($url){
        require_once 'Validate.php';
        if (!filter_var($url, FILTER_VALIDATE_URL)){
            return false;
        }
        return true;
    }

    /**
     * method for validating url with PEAR::Validate filter 
     * @param   string  $url the url to validate
     * @return  boolean $res true on success and false on failure
     */
    public static function url ($url){
        require_once 'Validate.php';
        $schemes = array ('http', 'https');
        if (!Validate::uri($url, array('allowed_schemes' => $schemes))){
            return false;
        }
        return true;
    }
    
    /**
     * validate email rfc822
     * @param string $email
     * @return boolean $res true if ok else false
     */
    public static function emailRfc822 ($email) {
        if (Validate::email($email, array('use_rfc822' => true))) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * method for vaildating email and an emails domain with PEAR:Validate
     * @param   string  $email the email to validate email
     * @param array $options set some options
     * @return  boolean $res true on success and false on failure 
     */
    public static function validateEmailAndDomain ($email, $options = null){

        if (!$options){
            $options = array('check_domain' => 'true');
        }
               
        if (Validate::email($email, $options)) {
            return true;
        }
        return false;
    }
    
    public static function validateEmailAndDomainVerbose ($email) {
        $options = array(
                'check_domain' => 'true',
                'fullTLDValidation' => 'true',
                'use_rfc822' => 'true',
                'VALIDATE_GTLD_EMAILS' => 'true',
                'VALIDATE_CCTLD_EMAILS' => 'true',
                'VALIDATE_ITLD_EMAILS' => 'true',
            );
        return self::validateEmailAndDomain($email, $options);
    }
    
    /**
     * chech a password length
     * @param string $password
     * @param int $length
     * @return boolean $res
     */
    public static function passwordLength ($password, $length) {
        if (function_exists('mb_strlen')) {
            if (mb_strlen($password, 'UTF-8') < $length){
                return false;
            }
        } else {
            if (strlen($password) < $length){
                return false;
            }
        }
        return true;
    }
    
    /**
     * check password match
     * @param string $password
     * @param string $password2
     * @return boolean $res
     */
    public static function passwordMatch ($password, $password2) {
        if ($password != $password2){
            return false;
        }
        return true;
    }
    
    /**
     * validates a hostname
     * @param string $host
     */
    public static function hostname ($host) {
        
        // from stackoverflow
        //$regex = "/^(([a-zA-Z]|[a-zA-Z][a-zA-Z0-9\-]*[a-zA-Z0-9])\.)*([A-Za-z]|[A-Za-z][A-Za-z0-9\-]*[A-Za-z0-9])$/";
        $regex = "/^(([a-zA-Z]|[a-zA-Z][a-zA-Z0-9\-]*[a-zA-Z0-9])\.)*([A-Za-z]|[A-Za-z][A-Za-z0-9\-]*[A-Za-z0-9])$/";
        
        return preg_match($regex, $host); 
    }
}
