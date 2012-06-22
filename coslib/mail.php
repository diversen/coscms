<?php


/**
 * @package coslib
 */

include_once "Mail.php";
include_once "Mail/mime.php";
/**
 * function for sending utf8 mails with native mail function
 *
 * @param   string  $recipient to whom are we gonna send the email
 * @param   string  $subject the subject of the email
 * @param   string  $message the message of the email
 * @param   string  $from from the sender of the email
 * @param   string  $reply_to email to reply to
 * @return  int     1 on success 0 on error
 */
function mail_utf8_direct($to, $subject, $message, $from = null, $reply_to=null) {

    $reply_to = trim($reply_to); $from = trim ($from);
    $headers = 'MIME-Version: 1.0' . "\r\n";
    $headers.= 'Content-type: text/plain; charset=UTF-8' . "\r\n";

    if (!$from) {
        $from = config::getMainIni('site_email');
    }
    
    $headers.= "From: $from\r\n";
    if (!$reply_to){
        $reply_to = $from;
    }

    $headers.= "Reply-To: $reply_to" . "\r\n";

    $bounce = config::getMainIni('site_email_bounce');
    if ($bounce){
        $headers.= "Return-Path: $bounce\r\n";
    }

    $subject = '=?UTF-8?B?'.base64_encode($subject).'?=';
    $message = wordwrap($message, 70);
    
    $log = "Mail to: $to\n";
    $log.= "Subject: $subject\n";
    $log.= "Message: $message\n";
    $log.= "Headers: $headers\n";
    
    if (config::getMainIni('send_mail')){
        if (isset(config::$vars['coscms_main']['smtp_mail'])){
            $res = mail_smtp ($to, $subject, $message, $from, $reply_to);
        } else {
            if ($bounce){
                $res = mail($to, $subject, $message, $headers, "-f $bounce");
            } else {
                $res = mail($to, $subject, $message, $headers);
            }
        }

        
        $log.= "Result: $res\n";
        cos_debug($log);
        return $res;
    } else {
        $log.= "Result: $res\n";
        cos_debug($log);
        return 1;
    }
}


/**
 * function for sending utf8 mails with native mail function
 *
 * @param   string  $recipient to whom are we gonna send the email
 * @param   string  $subject the subject of the email
 * @param   string  $message the message of the email
 * @param   string  $from from the sender of the email
 * @param   string  $reply_to email to reply to
 * @return  int     1 on success 0 on error
 */

function mail_utf8($to, $subject, $message, $from = null, $reply_to=null) {

    $reply_to = trim($reply_to); 
    $from = trim ($from);
    
    $message = wordwrap($message, 70);
    $res = null;
    
    $log = "Mail to: $to\n";
    $log.= "Subject: $subject\n";
    $log.= "Message: $message\n";
    
    if (config::getMainIni('send_mail')){
        if (isset(config::$vars['coscms_main']['smtp_mail'])){
            $res = mail_smtp ($to, $subject, $message, $from, $reply_to);
        } else {
            $res = mail_php($to, $subject, $message, $from, $reply_to);
        }
        
        $log.= "Mail result: $res\n";
        cos_debug($log);
        return $res;
    } else {
        cos_debug($log);
        return 1;
    }
}

/**
 * method for sending mails via pear and php mail function
 * @param   string  $recipient to whom are we gonna send the email
 * @param   string  $subject the subject of the email
 * @param   string  $message the message of the email
 * @param   string  $from from the sender of the email
 * @param   string  $reply_to email to reply to
 * @return  int     $res 1 on success 0 on error
 */
function mail_php ($recipient, $subject, $message, $from = null, $reply_to = null){

    $recipient = "<$recipient>";                               
    $crlf = "\n";
    
    if (!$from) {
        $from = config::getMainIni('site_email');
    }
    
    if (!$reply_to){
        $reply_to = $from;
    }
    
    $headers = array(
        'From'          => $from,
        'Return-Path'   => $from,
        'Reply-To'      => $reply_to,
        'Subject'       => $subject,
        'Content-type' => 'text/plain; charset=UTF-8'
    );

    $bounce = config::getMainIni('site_email_bounce');
    if ($bounce){
        $headers['Return-Path'] = $bounce;
    }

    $mime = new Mail_mime($crlf);
    $mime->setTXTBody($message);
    $body = $mime->get(
            array('text_charset' => 'UTF-8', 
                'html_charset' => "UTF-8",
                'head_charset' => "UTF-8"));
    $headers = $mime->headers($headers);
    $mail =& Mail::factory("smtp", $smtp_params);
    $res = $mail->send($recipient, $headers, $body);
    if (PEAR::isError($res)) {
        //print_r($res);
        cos_error_log($res->getMessage());
        return false;
    }
    return 1;
}


/**
 * method for sending mails via smtp
 * @param   string  $to to whom are we gonna send the email
 * @param   string  $subject the subject of the email
 * @param   string  $message the message of the email
 * @param   string  $from from the sender of the email
 * @param   string  $reply_to email to reply to
 * @return  int     $res 1 on success 0 on error
 */
function mail_smtp ($to, $subject, $message, $from = null, $reply_to = null) {

    if (!$from) {
        $from = config::getMainIni('site_email');
    }
    
    if (!$reply_to){
        $reply_to = $from;
    }
    
    //$recipient = "<$recipient>"; 

    $headers = array(
        'From'          => $from,
        'Return-Path'   => $from,
        'Reply-To'      => $reply_to,
        'Subject'       => $subject,
        'Content-type' => 'text/plain; charset=UTF-8'
    );

    $bounce = config::getMainIni('site_email_bounce');
    if ($bounce){
        $headers['Return-Path'] = $bounce;
    }

    $crlf = "\n";
    $mime = new Mail_mime($crlf);

    // Setting the body of the email
    $mime->setTXTBody($message);
    $body = $mime->get(
            array('text_charset' => 'UTF-8', 
                'html_charset' => "UTF-8",
                'head_charset' => "UTF-8"));
    $headers = $mime->headers($headers);

    // SMTP authentication params
    $smtp_params = array();
    $smtp_params["host"]     = config::$vars['coscms_main']['smtp_params_host']; //"ssl://smtp.gmail.com";
    $smtp_params["port"]     = config::$vars['coscms_main']['smtp_params_port'];
    $smtp_params["auth"]     = config::getMainIni('smtp_params_auth');
    $smtp_params["username"] = config::$vars['coscms_main']['smtp_params_username'];
    $smtp_params["password"] = config::$vars['coscms_main']['smtp_params_password'];
    $smtp_params['debug'] = config::getMainIni('smtp_params_debug');
    //var_dump($smtp_params); die;
    $mail = Mail::factory("smtp", $smtp_params);
    
    $res = $mail->send($to, $headers, $body);
    if (PEAR::isError($res)) {
        cos_error_log($res->getMessage());
        return false;
    }
    return true;
}

function mail_smtp_zend ($to, $subject, $message, $from = null, $reply_to = null) {

    include_once "Zend/Mail.php";
    include_once "Zend/Mail/Transport/Smtp.php";
    $config = array('auth' => config::getMainIni('smtp_params_auth'),
                    'username' => config::getMainIni('smtp_params_username'),
                    'password' => config::getMainIni('smtp_params_password'),
                    'port' => config::getMainIni('smtp_params_port')
     );
     
    
    $transport = new Zend_Mail_Transport_Smtp(
            config::getMainIni('smtp_params_host'), 
            $config);
     
    $mail = new Zend_Mail();
    $mail->setBodyText($message);
    if (!$from) {
        $from = config::getMainIni('site_email');
    }
    $mail->setFrom($from);
    $mail->addTo($to);
    $mail->setSubject($subject);
    try {
        $res = $mail->send($transport);
    } catch (Exception $e) {
        cos_error_log($e->getTraceAsString());
        return false;
    } 
    return true;
    
}

/**
 * method for sending html mails via smtp
 * @param   string  $recipient to whom are we gonna send the email
 * @param   string  $subject the subject of the email
 * @param   string  $html the html message the message of the email
 * @param   string  $from from the sender of the email
 * @param   string  $reply_to email to reply to
 * @return  int     $res 1 on success 0 on error
 */
function mail_html ($recipient, $subject, $html, $from = null, $reply_to = null){
    
    if (!$from) {
        $from = config::$vars['coscms_main']['smtp_params_sender'];  
    }

    if (!$reply_to){
        $reply_to = $from;
    }
    $recipient = "<$recipient>";                          


    $headers = array(
        'From'          => $from,
        'Return-Path'   => $from,
        'Reply-To'      => $reply_to,
        'Subject'       => $subject,
        'Content-type' => 'text/plain; charset=UTF-8'
    );

    $bounce = config::getMainIni('site_email_bounce');
    if ($bounce){
        $headers['Return-Path'] = $bounce;
    }

    $crlf = "\n";
    $mime = new Mail_mime($crlf);
    $mime->setHTMLBody($html);
    $body = $mime->get(
            array('text_charset' => 'UTF-8', 
                'html_charset' => "UTF-8",
                'head_charset' => "UTF-8"));
    $headers = $mime->headers($headers);

    // SMTP authentication params
    $smtp_params = array();
    $smtp_params["host"]     = config::$vars['coscms_main']['smtp_params_host']; //"ssl://smtp.gmail.com";
    $smtp_params["port"]     = config::$vars['coscms_main']['smtp_params_port'];
    $smtp_params["auth"]     = true; //register::$vars['coscms_main']['smtp_params_auth'];
    $smtp_params["username"] = config::$vars['coscms_main']['smtp_params_username'];
    $smtp_params["password"] = config::$vars['coscms_main']['smtp_params_password'];

    $mail = Mail::factory("smtp", $smtp_params);
    $res = $mail->send($recipient, $headers, $body);
    if (PEAR::isError($res)) {
        //print_r($res);
        cos_error_log($res->getMessage());
        return false;
    }
    return true;
}

function mail_system_user ($subject, $message, $from = null, $reply_to = null) {
    $to = config::getMainIni('mail_address_primary');
    return mail_utf8(
            $to, 
            $subject, 
            $message, 
            $from, 
            $reply_to);
}
