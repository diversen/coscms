<?php
/**
 * File contains a wrapper for curl.
 * class found on php.net
 * http://php.net/manual/en/book.curl.php
 * slightly modified from above class.  
 * @package coslib
 */

/**
 * class contains wrappers for curl class. 
 * @package coslib
 */
class mycurl {
     protected $_useragent = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1';
     protected $_url;
     protected $_followlocation;
     protected $_timeout;
     protected $_maxRedirects;
     protected $_cookieFileLocation = '/tmp/cookie.txt';
     protected $_post;
     protected $_postFields;
     protected $_referer ="http://www.google.com";
 
     protected $_session;
     public $_webpage;
     protected $_includeHeader;
     protected $_noBody;
     protected $_status;
     protected $_binaryTransfer;
     public    $authentication = 0;
     public    $auth_name      = '';
     public    $auth_pass      = '';
 
     public function useAuth($use){
       $this->authentication = 0;
       if($use == true) $this->authentication = 1;
     }
 
     public function setName($name){
       $this->auth_name = $name;
     }
     public function setPass($pass){
       $this->auth_pass = $pass;
     }
 
     public function __construct($url,$followlocation = true,$timeOut = 30,$maxRedirecs = 4,$binaryTransfer = false,$includeHeader = false,$noBody = false)
     {
         $this->_url = $url;
         $this->_followlocation = $followlocation;
         $this->_timeout = $timeOut;
         $this->_maxRedirects = $maxRedirecs;
         $this->_noBody = $noBody;
         $this->_includeHeader = $includeHeader;
         $this->_binaryTransfer = $binaryTransfer;
 
         //$this->_cookieFileLocation = dirname(__FILE__).'/cookie.txt';
 
     }
 
     public function setReferer($referer){
       $this->_referer = $referer;
     }
 
     public function setCookiFileLocation($path)
     {
         $this->_cookieFileLocation = $path;
     }
 
     public function setPost ($postFields)
     {
        $this->_post = true;
        $this->_postFields = $postFields;
     }
 
     public function setUserAgent($userAgent)
     {
         $this->_useragent = $userAgent;
     }
 
     public function createCurl($url = 'nul')
     {
        if($url != 'nul'){
          $this->_url = $url;
        }
 
         $s = curl_init();
 
         curl_setopt($s,CURLOPT_URL,$this->_url);
         curl_setopt($s,CURLOPT_HTTPHEADER,array('Expect:'));
         curl_setopt($s,CURLOPT_TIMEOUT,$this->_timeout);
         curl_setopt($s,CURLOPT_MAXREDIRS,$this->_maxRedirects);
         curl_setopt($s,CURLOPT_RETURNTRANSFER,true);
         curl_setopt($s,CURLOPT_FOLLOWLOCATION,$this->_followlocation);
         curl_setopt($s,CURLOPT_COOKIEJAR,$this->_cookieFileLocation);
         curl_setopt($s,CURLOPT_COOKIEFILE,$this->_cookieFileLocation);
 
         if($this->authentication == 1){
           curl_setopt($s, CURLOPT_USERPWD, $this->auth_name.':'.$this->auth_pass);
         }
         if($this->_post)
         {
             curl_setopt($s,CURLOPT_POST,true);
             curl_setopt($s,CURLOPT_POSTFIELDS,$this->_postFields);
 
         }
 
         if($this->_includeHeader)
         {
               curl_setopt($s,CURLOPT_HEADER,true);
         }
 
         if($this->_noBody)
         {
             curl_setopt($s,CURLOPT_NOBODY,true);
         }
         /*
         if($this->_binary)
         {
             curl_setopt($s,CURLOPT_BINARYTRANSFER,true);
         }
         */
         curl_setopt($s,CURLOPT_USERAGENT,$this->_useragent);
         curl_setopt($s,CURLOPT_REFERER,$this->_referer);
 
         $this->_webpage = curl_exec($s);
         $this->_status = curl_getinfo($s,CURLINFO_HTTP_CODE);
         curl_close($s);
 
     }
 
   public function getHttpStatus()
   {
       return $this->_status;
   }
 
   public function __tostring(){
      return $this->_webpage;
   }
   
   public function getWebPage () {
       return $this->_webpage;
   }
}

/**
 * @param type $url
 * @param type $status
 * @param type $wait
 * @return type 
 */
function http_response($url, $status = null, $wait = 3)
{
    $time = microtime(true);
    $expire = $time + $wait;

    // we fork the process so we don't have to wait for a timeout
    $pid = pcntl_fork();
    if ($pid == -1) {
        die('could not fork');
    } else if ($pid) {
        // we are the parent
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, TRUE);
        curl_setopt($ch, CURLOPT_NOBODY, TRUE); // remove body
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $head = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
       
        if(!$head)
        {
            return FALSE;
        }
       
        if($status === null)
        {
            if($httpCode < 400)
            {
                return TRUE;
            }
            else
            {
                return FALSE;
            }
        }
        elseif($status == $httpCode)
        {
            return TRUE;
        }
       
        return FALSE;
        pcntl_wait($status); //Protect against Zombie children
    } else {
        // we are the child
        while(microtime(true) < $expire)
        {
        sleep(0.5);
        }
        return FALSE;
    }
} 
 

