<?php

/**
 * contains mail queue
 * used for sending messages delayed
 * @package cosMail
 */

/**
 * example
 * 
 <code>
    $subject = "test";
 
    $message = array (
        'txt' => 'message', 
        'html' => '<h3>html message</h3>',                       
        'attachments => array ('/path/to/file', '/path/to/another/file')
    );
    
    $to = "test@example.com";
    
    cosMail::enableQueue();
    cosMail_queue::setSendTime("+2 days");
    $res = cosMail::multipart($to, $subject, $message);
    // returns last insert id
 </code>
 * 
 * with this command you can send mails as txt, html (or both) and 
 * add attachments by only setting the attachment part of the message array
 *  
 * @package cosMail
 */

class cosMail_queue {
    
    public static $dateTime = null;
    
    /**
     * set when to send mail. Uses same format as <code>strtotime()</code>
     * "now";
     * "10 September 2014"
     * "+1 day"
     * "+1 week"
     * "+1 week 2 days 4 hours 2 seconds";
     * "next Thursday"
     * @see http://php.net/manual/en/function.strtotime.php
     * @param string $str string given to strtotime
     */
    public static function setSendTime ($str) {
        self::$dateTime = $str;
    }
    
    /**
     * adds a mail to the queue
     * @param type $to
     * @param type $mime_headers
     * @param type $body
     * @return type
     */
    public static function add ($to, $mime_headers, $body) {
        cosRB::connect();
        $bean = cosRB::getBean('cosmail_queue');
        $bean->to = $to;
        $bean->mimeheaders = serialize($mime_headers);
        $bean->body = $body;
        $bean->sendtime = self::getDateTime();
        $bean->sent = 0;
        $bean->domain = cosMail_helpers::getDomain($to);
        return R::store($bean); 
    }
    
    /**
     * generate a mysql timestamp
     * if dateTime is not set then it defaults to "now"
     * @return string $dateTime (to be used in SQL)
     */
    public static function getDateTime () {
        if (!self::$dateTime) {
            self::$dateTime = "now";
        }
        
        $ts = strtotime(self::$dateTime);
        $format = 'Y-m-d G:i:s';
        $dateTime = date($format, $ts);
        return $dateTime;
    }
    
    /**
     * method for processing mail queue
     */
    public static function process () {
        
    }
}
