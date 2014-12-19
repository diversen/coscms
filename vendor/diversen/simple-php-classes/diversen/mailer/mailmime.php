<?php

namespace diversen\mailer;
/**
 * file contains mail mime 
 * @package cosMail
 */

/**
 * @ignore
 */
include_once "Mail/mime.php";

/**
 * class contains mail mime helpers
 * thin wrapper around Mail::Mime
 * @package cosMail
 */
class mailmime {
    /**
     * holding mime object
     * @var object $mime 
     */
    public $mime = null;
    
    /**
     * constructs a mime object
     */
    public function __construct () {
        $crlf = "\n";
        $this->mime = new \Mail_mime($crlf);
    }
    
    /**
     * sets txt part
     * @param string $txt
     */
    public function setTxt ($txt) {
        $this->mime->setTXTBody($txt);   
    }
    
    /**
     * sets html part
     * @param string $html
     */
    public function setHTML ($html) {
        $this->mime->setHTMLBody($html);
    }
    
    /**
     * sets a attachment
     * @param string $file path to file
     */
    public function setAttachment ($file) {
        $mime_type = file::getMime($file);
        $this->mime->addAttachment($file, $mime_type);  
    }
    
    /**
     * get mime headers
     * @param array $headers
     * @return string $headers
     */
    public function getHeaders ($headers) {
        return $this->mime->headers($headers);
    }
    
    /**
     * gets body
     * @return string $body
     */
    public function getBody () {
        return $this->mime->get(
            array('text_charset' => 'utf-8', 
                'html_charset' => "utf-8",
                'head_charset' => "utf-8"));
    }
}
