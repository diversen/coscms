<?php


/**
 * file contains functions for sending mail 
 * in various ways. With  PHP or SMTP using UTF-8
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
function mail_get_headers ($subject, $from, $reply_to, $content_type = 'text/html; charset=UTF-8') {
    if (!$from) $from = config::$vars['coscms_main']['site_email']; 
    if (!$reply_to) $reply_to = $from;
                       
    $headers = array(
        'From'          => $from,
        'Return-Path'   => $from,
        'Reply-To'      => $reply_to,
        'Subject'       => $subject,
        'Content-type'  => $content_type
    );

    $bounce = config::getMainIni('site_email_bounce');
    if ($bounce) $headers['Return-Path'] = $bounce;
    return $headers;
}

/**
 * function for sending utf8 mails with native mail function
 *
 * @param   string  $to to whom are we gonna send the email
 * @param   string  $subject the subject of the email
 * @param   string  $message the message of the email
 * @param   string  $from from the sender of the email
 * @param   string  $reply_to email to reply to
 * @return  int     1 on success 0 on error
 */

function mail_utf8($to, $subject, $message, $from = null, $reply_to=null) {
    
    $to = "<$to>"; 
    
    $headers = mail_get_headers($subject, $from, $reply_to, 'text/plain; charset=UTF-8');
    
    $crlf = "\n";
    $mime = new Mail_mime($crlf);

    $mime->setTXTBody($message);
    $body = $mime->get(
            array('text_charset' => 'UTF-8', 
                'html_charset' => "UTF-8",
                'head_charset' => "UTF-8"));
    $headers = $mime->headers($headers);

    $options = mail_init();
    $params = mail_get_params ();
  
    $mail = Mail::factory($options['mail_method'], $params);
    $res = $mail->send($to, $headers, $body);
    if (PEAR::isError($res)) {
        cos_error_log($res->getMessage());
        return false;
    }
    return true;
    
}


/**
 * method for sending html mails via smtp
 * @param   string  $to to whom are we send the email
 * @param   string  $subject the subject of the email
 * @param   string|array  $html the html message the message of the email
 * @param   string  $from from the sender of the email
 * @param   string  $reply_to email to reply to
 * @return  int     $res 1 on success 0 on error
 */
function mail_html_utf8 ($to, $subject, $message, $from = null, $reply_to = null){

    $to = "<$to>"; 
    
    $headers = mail_get_headers($subject, $from, $reply_ro, 'text/plain; charset=UTF-8');

    $crlf = "\n";
    $mime = new Mail_mime($crlf);
     
    // set a both a txt and a html message
    if (is_array($message)) {
        $html_message = $message['html'];
        $txt_message = $message['txt'];
    } else {
        $html_message = $message;
    }
    
    if (isset($txt_message)) {
        $mime->setTXTBody($txt_message);
    }
    
    $mime->setHTMLBody($html_message);
    $body = $mime->get(
            array('text_charset' => 'utf-8', 
                'html_charset' => "utf-8",
                'head_charset' => "utf-8"));
    $headers = $mime->headers($headers);

    $options = mail_init();
    $params = mail_get_params ();

    $mail = Mail::factory($options['mail_method'], $params);

    $res = $mail->send($to, $headers, $body);
    if (PEAR::isError($res)) {
        cos_error_log($res->getMessage());
        return false;
    }
    return true;
}

/**
 * send mail to primary user
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
