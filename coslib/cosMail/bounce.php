<?php

/**
 * a simple mail bounce parser
 * only tested with postfix
 */

class cosmail_bounce {

    /**
     * constructor. 
     */
    public function __construct () {
        include_once "coslib/date.php";
        cosRB::connect();
    }
    
    
    /**
     * bouncer to include in a cron job
     */
    public function cron () {

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

        // write to log file
        log::error( "Num messages: $c");
        
        // keep alive
        $i->mail->noop();

        // Fetch all message we start with latest message. = $c
        for ($x = $c; $x >= 1; $x--) { 

            $email = null;
            $message = $i->mail->getMessage($x);

            // we need original return path. Should be <> 
            $return_path = $message->getHeader('return-path', 'string');

            // keep alive
            $i->mail->noop(); 

            $parts = $i->getAllParts($message);
            
            // check for [message/delivery-status] part
            if (isset($parts['message/delivery-status'][0])) {
 
                // search for email contained in delivery status
                $email = $this->parseDeliveryStatus($parts['message/delivery-status'][0]);
                if ($email) {

                    // we got a message/delivery-status and a email.
                    $bean = cosRB::getBean('cosmailBounce');
                    $bean->email = $email; 
                    // get bounce code
                    $bounce_code = getStatusCode ($parts['message/delivery-status'][0]);
                    if ($bounce_code) {
                        $bounce_ary = explode('.', $bounce_code);
                        $bean->major = $bounce_ary[0];
                        $bean->minor = $bounce_ary[1];
                        $bean->part = $bounce_ary[2];
                        $bean->bouncecode = $bounce_code;
                    } else {
                        $bean->bouncecode = null;
                    }

                    $bean->bouncedate = dateGetDateNow(array ('hms' => true));
                    $bean->message = $parts['message/delivery-status'][0];
                    $bean->returnpath = $return_path;
                    R::store($bean);                    
                    
                } else {
                    echo "did not get a mail from message: " . $parts['message/delivery-status'][0] . "\n";
                }
            } else {
                echo "No delivery status\n";
            }

            $i->mail->noop(); // keep alive
            $i->mail->removeMessage($x);  
            $i->mail->noop(); // keep alive
            sleep(1);
        }
    }

    /**
     * get bounce code as string
     * @param string $mail
     * @return mixed $ret string or null
     */
    public function getStatusCode ($mail) {

        // make txt a array
        $ary = explode("\n", $mail);
        foreach ($ary as $val) {

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
     * fetch the bounced email from a delivery status message
     * parse a dilevery status
     * @param string $txt
     * @return string $email
     */
    public function parseDeliveryStatus ($txt) {
        $pattern = "/([\s]*)([_a-zA-Z0-9-]+(\.[_a-zA-Z0-9-]+)*([ ]+|)@([ ]+|)([a-zA-Z0-9-]+\.)+([a-zA-Z]{2,}))([\s]*)/i";

        // Finding all emails in message
        preg_match_all($pattern, $txt, $matches);
        if (!empty($matches[0])) {
            foreach ($matches[0] as $key => $val) {
                $matches[0][$key] = trim($val);
            }
        }

        $matches = array_unique($matches[0]);

        if (!empty($matches)) {

            $unset_aray = array (
                config::getMainIni('imap_user'), 
                config::getMainIni('smtp_params_username')
            );

            foreach ($matches as $key => $val) {
                if (in_array($val, $unset_aray)) {
                    unset($matches[$key]);
                }

                if (!cosValidate::emailRfc822($val)) {
                    unset($matches[$key]);
                }
            }
        }

        // we should only have one email now
        return reset($matches);
    }
}
