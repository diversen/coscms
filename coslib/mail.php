<?php


/**
 * @package coslib
 */

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

    // prevent injection of other headers by trimming emails
    $reply_to = trim($reply_to); $from = trim ($from);

    // create headers for sending email
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

        $log = "TO: $to\n";
        $log.= "SUBJECT: $subject\n";
        $log.= "MESSAGE: $message\n";
        $log.= "HEADERS: $headers\n";
        $log.= "RESULT $res\n";

        if (isset(config::$vars['coscms_main']['debug'])){
            cos_error_log($log);
        }
        return $res;
    } else {
        $log = "\nSending mail to: $to\n";
        $log.= "Subject: $subject\n";
        $log.= "Message: $message\n";
        $log.= "Header: $headers\n";
        cos_error_log($log);
        return 1;
    }
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




/**
 * method for sending mails via smtp
 * @param   string  $recipient to whom are we gonna send the email
 * @param   string  $subject the subject of the email
 * @param   string  $message the message of the email
 * @param   string  $from from the sender of the email
 * @param   string  $reply_to email to reply to
 * @return  int     $res 1 on success 0 on error
 */
function mail_smtp ($recipient, $subject, $message, $from, $reply_to/*$headers = null*/){
    include_once('Mail.php');
    include_once('Mail/mime.php');
    
    
    
    if (!$from) {
        $from = config::$vars['coscms_main']['smtp_params_sender'];  
        //$from = config::getMainIni('site_email');
    }
    
    //$headers.= "From: $from\r\n";
    if (!$reply_to){
        $reply_to = $from;
    }

    // Your email address
    $recipient = "<$recipient>";                               // The Recipients name and email address
    //$subject = "Another test Email";                                                // Subject for the email
    //$text = 'This is a text message.';                                      // Text version of the email
    //$html = '<html><body><p>This is a html message!</p></body></html>';      // HTML version of the email
    $crlf = "\n";


    $headers = array(
        'From'          => $from,
        'Return-Path'   => $from,
        'Reply-To'      => $reply_to,
        'Subject'       => $subject,//'=?UTF-8?B?'.base64_encode($subject).'?=',//$subject,
        //Content-type: text/plain; charset=UTF-8'
        'Content-type' => 'text/plain; charset=UTF-8'
    );

    $bounce = config::getMainIni('site_email_bounce');
    if ($bounce){
        $headers['Return-Path'] = $bounce;
    }

    // Creating the Mime message
    $mime = new Mail_mime($crlf);

    // Setting the body of the email
    $mime->setTXTBody($message);
    //$mime->setHTMLBody($html);

    // Add an attachment

    //$file = "Hello World!";
    //$file_name = "Hello text.txt";
    //$content_type = "text/plain";
    //$mime->addAttachment ($file, $content_type, $file_name, 0);

    // Set body and headers ready for base mail class
    $body = $mime->get(array('text_charset' => 'utf-8'));
    $headers = $mime->headers($headers);

    // SMTP authentication params
    $smtp_params = array();
    $smtp_params["host"]     = config::$vars['coscms_main']['smtp_params_host']; //"ssl://smtp.gmail.com";
    $smtp_params["port"]     = config::$vars['coscms_main']['smtp_params_port'];
    $smtp_params["auth"]     = true; //register::$vars['coscms_main']['smtp_params_auth'];
    $smtp_params["username"] = config::$vars['coscms_main']['smtp_params_username'];
    $smtp_params["password"] = config::$vars['coscms_main']['smtp_params_password'];

// Sending the email using smtp
    $mail =& Mail::factory("smtp", $smtp_params);
    $result = $mail->send($recipient, $headers, $body);
    return $result;
}
