<?php

/**
 * primitive bounce parser
 * only tested with postfix
 */

include_once "coslib/date.php";
cosRB::connect();

/**
 * function which is easy to add to a cron job
 */
function mailer_cron_bounce () {
    
    $connect = array(
        'host'     => config::getMainIni('imap_host'),
        'port'     => config::getMainIni('imap_port'), 
        'user'     => config::getMainIni('imap_user'),
        'password' => config::getMainIni('imap_password'),
        'ssl'      => config::getMainIni('imap_ssl')
     );

    $i = new imap();
    $i->connect($connect);
    $c = $i->countMessages();

    log::error("Parse bounces. Num messages: $c\n");	    
    $i->mail->noop();
    
    // reverse - we start with latest message = $c
    for ($x = $c; $x >= 1; $x--) { 

        log::error("Pasing num: $x");
        
        $email = null;
        $message = $i->mail->getMessage($x);
        
  
	$i->mail->noop();
        
        // get mail parts
        $parts = $i->getAllParts($message);
        
        // check for valid message/delivery-status'
        if (isset($parts['message/delivery-status'][0])) {      
            $email = mailer_parse_bounce($parts['message/delivery-status'][0]);         
            if ($email) {
                
                log::error("Found email in message: $email");
                $bean = cosRB::getBean('mailerbounce');
                $bean->deleted = 0;
                
                
                // get bounce code
                $bounce_code = mailer_get_bounce_code ($parts['message/delivery-status'][0]);
                if ($bounce_code) {
                    $bounce_ary = explode('.', $bounce_code);
                    $bean->major = $bounce_ary[0];
                    $bean->minor = $bounce_ary[1];
                    $bean->part = $bounce_ary[2];
                    $bean->bouncecode = $bounce_code;
                        
                } else {
                    $bean->bouncecode = null;    
                }
                    
                $bean->email = $email;
                if (function_exists('cosmail_bounce_attach_account_id')) {
                    $bean->user = cosmail_bounce_attach_account_id($email);
                }
                
                $bean->bouncedate = dateGetDateNow(array ('hms' => true));
                $bean->message = $parts['message/delivery-status'][0];
                $bean->returnpath = $message->getHeader('return-path', 'string');
                R::store($bean);                    
                log::error( "stored user with email: $email");  
            } else {
                log::error("did not get a mail from message: " . $parts['message/delivery-status'][0]);
            }
        } else {
            log::error( "No delivery status" );
        }
        
        $i->mail->noop(); // keep alive
        $i->mail->removeMessage($x);  
        $i->mail->noop(); // keep alive
        sleep(1);
        
        
    }

    log::error ("deleting bounces");
    mailer_bounces_delete();
}



/**
 * returns bounce code from [message/delivery-status] part of message
 * e.g. 4.2.2
 * @param type $email
 * @return string $code e.g. 4.2.2
 */
function mailer_get_bounce_code ($mail) {
    
    // make txt an array
    $ary = explode("\n", $mail);
    foreach ($ary as $key => $val) {
        
        // find satus line
        $str = strtolower($val);
        if (strstr($str, 'status')) {
            $str = str_replace('status', '', $str);
            $str = str_replace(':', '', $str);
            $str = str_replace (' ', '', $str);
            $str = trim($str);
            return $str;
            
        }
    }
    return null;
    
}

/**
 * returns bounced email from [message/delivery-status] part of message
 * @param type $txt
 * @return string $email
 */
function mailer_parse_bounce ($txt) {
    $pattern = "/([\s]*)([_a-zA-Z0-9-]+(\.[_a-zA-Z0-9-]+)*([ ]+|)@([ ]+|)([a-zA-Z0-9-]+\.)+([a-zA-Z]{2,}))([\s]*)/i";

    // preg_match_all returns an associative array
    preg_match_all($pattern, $txt, $matches);

    // all emails caught in $matches[0]
    if (!empty($matches[0])) {
        foreach ($matches[0] as $key => $val) {
            $matches[0][$key] = strtolower(trim($val));
        }
    }
    
    
    $matches = array_unique($matches[0]);
    
    log::error("Finding email in message");
    if (!empty($matches)) {
        $unset_aray = array ('bounce@sweetpoints.dk', 'mail@sweetpoints.dk');
        foreach ($matches as $key => $val) {
            if (in_array($val, $unset_aray)) {
                unset($matches[$key]);
            }
        }
    }

    if (count($matches) > 1 ) {
        // should only have one
        log::error($matches);
        return array_pop($matches);
    }
    
    else if (count($matches) == 0 ) {
        $message = 'Did not find user in account|account_sub tables';
        log::error($message);
        return false;
    } else {
        $match = reset($matches);
        return $match; 
    }      
}
