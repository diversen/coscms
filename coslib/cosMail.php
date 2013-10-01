<?php

/**
 * contains mail class and mail functions
 * use cosMail::multipart, this will send text, html, add attachments
 * All in all it is a wrapper around PEAR::mail
 * 
 * @package cosMail
 */

/**
 * file contains functions for sending mail 
 * using sendmail, smtp or mail function using UTF-8
 * It uses the excellent PEAR Mail and Mail:mime 
 * classes. And this file just wraps the functionality of these classes. 
 * 
 * You really only need to use one function call: 
 * 
 * 
 <code>
   $message = array (
  'txt' => 'message', 
  'html' => '<h3>html message</h3>',                       
  'attachments => array ('/path/to/file', '/path/to/another/file'));
   cosMail::multipart($to, $subject, $message);
 </code>
 * 
 * with this command you can send mails as txt, html (or both) and 
 * add attachments by only setting the attachment part of the message array
 *  
 * @package cosMail
 */

include_once "Mail.php";

class cosMail {
    
    public static $wordwrap = 0;
    public static $mail = null;
    public static $params = array ();
    public static $queueParams = array ();
    
    /**
     * sets type of mailing to do
     * @return null|string
     */
    public static function init () {
        static $options = null;
        if (isset($options)) { 
            return $options;
        }
        $method = config::getMainIni('mail_method');
        if ($method) {
            $options['mail_method'] = $method;
        } else {
            $options['mail_method'] = 'smtp';
        }
        return $options;
    }
    
    public static function setParams ($params) {
        self::$params = $params;
    }
    
    /**
     * get mail params from a cos config file
     * @return array $params
     */
    public static function getCosParams () {
        
        $options = cosMail::init();
        $params = array();
        if ($options['mail_method'] == 'smtp') {

            // SMTP authentication params
            $params["host"]     = config::getMainIni('smtp_params_host');
            $params["port"]     = config::getMainIni('smtp_params_port');
            $params["auth"]     = config::getMainIni('smtp_params_auth');
            $params["username"] = config::getMainIni('smtp_params_username');
            $params["password"] = config::getMainIni('smtp_params_password');
            $params['debug'] = config::getMainIni('smtp_params_debug');
            $params['persist'] = config::getMainIni('smtp_params_persist');
            //$params['pipelining'] = config::getMainIni('smtp_params_pipelining');
            if (!config::getMainIni('smtp_params_persist')) {
                $params['persist'] = false;
            }
        }

        if ($options['mail_method'] == 'sendmail') {
            // sendmail params
            $params["sendmail_path"] = config::getMainIni('sendmail_path');
            $params["sendmail_args"] = config::getMainIni('sendmail_args');
        }

        if ($options['mail_method'] == 'mail') {
            // mail function params
            $params["mail_params"] = config::getMainIni('mail_function_params');
        }
        $params =  array_merge($params, self::$params);
        return $params;
    }
    
    
   /**
    * get headers
    * @param string $to email to send mail to
    * @param string $subject subject of the mail
    * @param string $from from
    * @param string $reply_to reply to
    * @param string $content_type
    * @return array $headers
    */
    public static function getHeaders ($to, $subject, $from, $reply_to, $more = array ()) {
        if (!$from) $from = config::getMainIni('site_email'); 
        if (!$reply_to) $reply_to = $from;

        $headers = array(
            'To'            => $to,
            'From'          => $from,
            'Return-Path'   => $from,
            'Reply-To'      => $reply_to,
            'Subject'       => $subject,
        );

        $bounce = config::getMainIni('site_email_bounce');
        if ($bounce) $headers['Return-Path'] = $bounce;

        if (!empty($more)) {
            foreach($more as $key => $val) {
                $headers[$key] = $val;
            }
        }
        return $headers;
    }
    
    
    
   /**
    * sends a mail to primary system user
    * @param string $subject
    * @param string $message
    * @param string $from (optional)
    * @param string $reply_to (optional)
    * @param array  $more additional headers
    * @return boolean $res
    */
    public static function systemUser ($subject, $message, $from = null, $reply_to = null, $more = array ()) {
        
        $to = config::getMainIni('site_email');
        return $res = cosMail::text(
            $to, 
            $subject, 
            self::wrapText($message), 
            $from, 
            $reply_to,
            $more);
    }
    
    public static function wrapText ($message) {
        if (!self::$wordwrap) return $message;
        return wordwrap($message, self::$wordwrap,  "\r\n");
    }

    /**
     * method for sending multi part emails. Default to txt if $message is a string 
     * @param   string          $to to whom are we send the email
     * @param   string          $subject the subject of the email
     * @param   array|string    $html the html message the message of the email, e.g.
     *                  array ('txt' => 'message', 'html' => '<h3>html message</h3>',
     *                         'attachments => array ('/path/to/file', '/path/to/another/file'));
     * @param   string          $from from the sender of the email
     * @param   string          $reply_to email to reply to
     * @param   array   $more additional headers
     * @return  int             $res 1 on success 0 on error
     */
    public static function multipart ($to, $subject, $message, $from = null, $reply_to = null, $more = array ()){
        
        $headers = cosMail::getHeaders($to, $subject, $from, $reply_to, $more);
        $mime = new cosMail_mime();
        if (is_array($message)) {

            if (isset($message['txt'])) {
                $mime->setTxt(self::wrapText($message['txt']));
            }
            
            if (isset($message['text'])) {
                $mime->setTxt(self::wrapText($message['text']));
            }
            
            if (isset($message['html'])) {
                if (strlen($message['html']) != 0) {
                    $mime->setHTML($message['html']);
                } 
            }

            if (isset($message['attachment'])) {
                foreach ($message['attachment'] as $val) {
                    $mime->setAttachment($val);
                }
            }
        } else {
            $mime->setTxt($message);
        }

        $body = $mime->getBody();
        $mime_headers = $mime->getHeaders($headers);

        return self::send($to, $mime_headers, $body);

    }  
   
   /**
    * function for sending text emails as with utf8 encoding
    *
    * @param   string  $to to whom are we gonna send the email
    * @param   string  $subject the subject of the email
    * @param   string  $message the message of the email
    * @param   string  $from the sender of the email
    * @param   string  $reply_to email to reply to
    * @param   array   $more additional headers
    * @return  int     1 on success 0 on error
    */
    public static function text($to, $subject, $message, $from = null, $reply_to=null, $more = array ()) {
        $headers = cosMail::getHeaders($to, $subject, $from, $reply_to, $more);
        
        $mime = new cosMail_mime();
        $mime->setTxt(self::wrapText($message));

        $body = $mime->getBody();
        $mime_headers = $mime->getHeaders($headers);

        return self::send($to, $mime_headers, $body);
   }
   
   /**
    * actual sending of the mail
    * @param string $to
    * @param array $mime_headers
    * @param string $body
    * @return boolean $res true on success and false on failure
    */
    public static function send ($to, $mime_headers, $body) {
        if (self::$queue) {
            return cosMail_queue::add ($to, $mime_headers, $body);
        }
        
        $options = cosMail::init();
        $params = cosMail::getCosParams();

        if (!is_object(self::$mail)) {
            self::$mail = Mail::factory($options['mail_method'], $params);
        }

        $res = self::$mail->send($to, $mime_headers, $body);
        if (PEAR::isError($res)) {
            log::error($res->getMessage());
            return false;
        }
        return true;
    }
    
    /**
     * when doing bulk mail it is a wise idea to destroy the socket connected
     * to a mail host and generate a new one, e.g. after sending 1000 mails
     * to one domain.
     */
    public static function unsetMailObj () {
        self::$mail = null;
    }
        
    /**
     *
     * @var boolean $queue if true queue is enabled else queue is not enabled
     */
    public static $queue = false;
    
    /**
     * enables mail queue
     */
    public static function enableQueue ($params = array ()) {
        self::$queue = true;
    }
    
    /**
     * disables mail queue
     */
    public static function disableQueue () {
        self::$queue = false;
    }
}
