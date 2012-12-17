<?php


/**
 * file contains functions for sending mail 
 * using sendmail, smtp or mail function using UTF-8
 * It uses the excellent PEAR Mail and Mail:mime 
 * classes. And this file just wraps the functionality of these classes. 
 * 
 * You really only need to use one function call: 
 * 
 * mail_multipart_utf8($to, $subject, $message, $from, $reply_to);
 * 
 * with this command you can send mails as txt, html (or both) and 
 * add attachments by only setting the attachment part of the message array
 * 
 * You can use this as standalone component. You will need to the following
 * includes: 
 * 
 * include_once "coslib/file.php"
 * include_once "coslib/config.php";
 * 
 * $params = array ('mail_method' => 'smtp');
 * config::setMainIniWithArray($params);
 * 
 * 
 *  
 * @package mail
 */

include_once "Mail.php";
include_once "Mail/mime.php";

/**
 * sets type of mailing to do
 * @staticvar null $options
 * @return null|string
 */
function mail_init () {
    static $options = null;
    if (isset($options)) return $options;
    if (isset(config::$vars['coscms_main']['mail_method'])) {
        $options['mail_method'] = config::$vars['coscms_main']['mail_method'];
    } else {
        $options['mail_method'] = 'smtp';
    }
    return $options;
}

/**
 * get mail params
 * @return array $params
 */
function mail_get_params () {
    $options = mail_init();
    
    $params = array();
    if ($options['mail_method'] == 'smtp') {

        // SMTP authentication params
        $params["host"]     = config::getMainIni('smtp_params_host');
        $params["port"]     = config::getMainIni('smtp_params_port');
        $params["auth"]     = config::getMainIni('smtp_params_auth');
        $params["username"] = config::getMainIni('smtp_params_username');
        $params["password"] = config::getMainIni('smtp_params_password');
        $params['debug'] = config::getMainIni('smtp_params_debug');
        if (!config::getMainIni('smtp_params_persist')) {
            $params['persist'] = true;
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
    return  $params;
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
function mail_get_headers ($subject, $from, $reply_to) {
    if (!$from) $from = config::$vars['coscms_main']['site_email']; 
    if (!$reply_to) $reply_to = $from;
                       
    $headers = array(
        'From'          => $from,
        'Return-Path'   => $from,
        'Reply-To'      => $reply_to,
        'Subject'       => $subject,
    );

    $bounce = config::getMainIni('site_email_bounce');
    if ($bounce) $headers['Return-Path'] = $bounce;
    return $headers;
}

/**
 * thin wrapper around Mail::Mime
 */
class mail_mime_wrapper {
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
        $this->mime = new Mail_mime($crlf);
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

/**
 * function for sending text emails as with utf8 encoding
 *
 * @param   string  $to to whom are we gonna send the email
 * @param   string  $subject the subject of the email
 * @param   string  $message the message of the email
 * @param   string  $from the sender of the email
 * @param   string  $reply_to email to reply to
 * @return  int     1 on success 0 on error
 */
function mail_utf8($to, $subject, $message, $from = null, $reply_to=null) {
    
    $to = "<$to>"; 
    
    $headers = mail_get_headers($subject, $from, $reply_to);
    
    $mime = new mail_mime_wrapper();
    $mime->setTxt($message);

    $body = $mime->getBody();
    $mime_headers = $mime->getHeaders($headers);

    $options = mail_init();
    $params = mail_get_params ();
  
    $mail = Mail::factory($options['mail_method'], $params);
    $res = $mail->send($to, $mime_headers, $body);
    if (PEAR::isError($res)) {
        log::debug($res->getMessage());
        return false;
    }
    return true;
    
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
 * @return  int             $res 1 on success 0 on error
 */
function mail_multipart_utf8 ($to, $subject, $message, $from = null, $reply_to = null){
    
    $to = "<$to>";
    
    $headers = mail_get_headers($subject, $from, $reply_to);
    
    $mime = new mail_mime_wrapper();
    if (is_array($message)) {
                
        if (isset($message['txt'])) {
            $mime->setTxt($message['txt']);
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

    $options = mail_init();
    $params = mail_get_params();

    $mail = Mail::factory($options['mail_method'], $params);
    $res = $mail->send($to, $mime_headers, $body);
    if (PEAR::isError($res)) {
        log::debug($res->getMessage());
        return false;
    }
    return true;
}

/**
 * sends a mail to primary system user
 * @param string $subject
 * @param string $message
 * @param string $from (optional)
 * @param string $reply_to (optional)
 * @return boolean $res
 */
function mail_system_user_utf8 ($subject, $message, $from = null, $reply_to = null) {
    $to = config::getMainIni('site_email');
    return $res = mail_utf8(
            $to, 
            $subject, 
            $message, 
            $from, 
            $reply_to);
}
