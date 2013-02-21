<?php


use Zend\Mail\Storage\Imap as ZendImap;
//include_once "Zend/Mail/Storage/Imap.php";

/**
 * Wrapper around Zend Mail Storage Imap 
 */

/*
Example: 

 // see send mail imap
 $connect = array(
 
    'host'     => 'mail.example.com',
    'port'     => 465, 
    'user'     => 'dennis@mail.dk',
    'password' => 'password',
    'ssl'      => 'TLS');

$i = new imap();
$i->connect($connect);

// count all
$num_mes = $i->countMessages() ;
echo "Num messages: " . $num_mes . "\n";

// get message
$message = $i->getMessage($num_mes);
//echo "Subject: " . $message->subject . "\n";
//echo "Ctype: " . $message->contentType . "\n";

// remove
// $i->removeMessage($num_mes); 
// get parts
// gets a array of plain text and base64 decoded images
$parts = $i->getParts($message);
foreach ($parts['images'] as $key => $image) {
    echo $image['name'] . "\n";
    echo $image['type'];
    // raw image
    // echo $image['file'];
}

*/


class imap {
    
    public $mail = null;
    
    
    /**
     * connects to an imap host with an array
     * @params array $connect e.g.
     *      array(
     *          'host'     => config::getMainIni('imap_host'),
     *          'port'     => config::getMainIni('imap_port'), 
     *          'user'     => config::getMainIni('imap_user'),
     *          'password' => config::getMainIni('imap_password'),
     *          'ssl'      => config::getMainIni('imap_ssl')
     * );
     *          
     */
    
    function __construct($connect = null) {
        if ($connect) {
            $this->mail = $this->connect($connect);
        }   
    }
    
    public function connect ($connect = null) {
        //print_r($connect); die ('t');
        $this->mail = new ZendImap($connect);
    }
    
    /**
     * sets noop
     */
    function noop () {
        $this->mail->noop();
    }
    
    /**
     * gets a message unique id
     * @param int $i the number of the message
     * @return string $unique_id
     */
    function getUniqueId ($i) {
        return $this->mail->getUniqueId($i); 
    }
    
    /**
     * gets a message 
     * @param int $num the number of message to get
     * @return object $message
     */
    function getMessage ($num) {
        $message = $this->mail->getMessage($num);
        return $message;
    }
    
    /**
     * gets from email from a message
     * @param int $num the message number
     * @return string $email_from
     */
    function getMessageFromEmail ($num) {
        $header = imap_rfc822_parse_headers($this->mail->getRawHeader($num));
        return $from_email = $header->from[0]->mailbox . "@" . $header->from[0]->host;

    }
    
    /**
     * counts number of messages
     * @return int $num
     */
    function countMessages() {
        return $this->mail->countMessages();
    }
    
    /**
     * removes a message
     * @param int $num the message number
     * @return boolean $res
     */
    function removeMessage ($num) {
        return $this->mail->removeMessage($num);
    }
    
    /**
     * gets a parts content-type
     * @param object $part
     * @return string $contentType
     */
    function getContentType ($part) {
        return strtok($part->contentType, ';');
    }
    
    /**
     * gets headers as human readable
     * note: Content-Disposition: attachment; filename=genome.jpeg;
     * @param object $part
     * @return array $headers  
     */
    function getHeadersHuman ($part) {

        $ary = explode(";", $part->contentType);
        if (isset($ary[0])) $ary['content-type'] = $ary[0];
       
        if (isset($ary[1])) {
            $name = explode("=", $ary[1]);
        }
        if (isset($name[1])) $ary['content-name'] = 
            str_replace(
                    array ('"'), 
                    array (''), 
                    trim($name[1]));
        return  $ary;
    }
    
    /** 
     * decode attached parts
     * @param object $part
     */
    function decodePlain ($part) {
        $content = $part->getContent();

        //echo $part->contentTransferEncoding;
        switch ($part->contentTransferEncoding) {
            case 'base64':
                $content = base64_decode($content);
                break;
            case 'quoted-printable':
                $content = quoted_printable_decode($content);
            break;
        }
        return $content;
        
    }
    
    /**
     * gets  a messages attached parts
     * @param type $message
     * @return array $parts attachments
     */
    function getParts ($message) {
        
        $parts = array ();
        
        $parts['subject'] = $message->subject;
        // only one plain text message per email
        $parts['plain'] =  ''; $message->getContent();        
        $parts['images'] = array ();
        $parts['movies'] = array ();
        $parts['unknown'] = array ();
        $parts['html'] = array ();
      //  echo 

        foreach (new RecursiveIteratorIterator($message) as $part) {
            try {
                $type = $this->getContentType($part);
                echo $type . "<br />\n";
                continue;
                if ($type == 'text/plain') {
                    $parts['plain'][] = $this->decodePlain($part);

                } else if ($type == 'image/jpeg' || $type == 'image/png' || $type == 'image/jpg') {
                    $file = base64_decode($part->getContent());  
                    $human = $this->getHeadersHuman ($part);
                    $parts['images'][] = array (
                        'name' => $human['content-name'],
                        'type' => $human['content-type'],
                        'file' => $file
                    );  
                } else if ($type == 'text/html') {
                    $parts['html'][] = $part->getContent();
                } else {
                    
                    $parts['unknown'][] = $part;
                }
            } catch (Exception $e) {
                error_log($e->getMessage());
            }
        } 
        return $parts;
    }
    
    /**
     * gets all parts of a message
     * @param object $message
     * @return array $parts 
     */
    function getAllParts ($message) {
        
        $parts = array ();
        foreach (new RecursiveIteratorIterator($message) as $part) {
            try {
                $type = $this->getContentType($part);
                $parts[$type][] = $part->getContent();

                //print_r($part->getHeaders());
                
            } catch (Exception $e) {
                log::error($e->getMessage());
            }
        } 
        return $parts;
    }
    
    
    function findPart ($id, $type ='text/plain') {
        $foundPart = null;
        foreach (new RecursiveIteratorIterator($this->mail->getMessage($id)) as $part) {
            try {
                if (strtok($part->contentType, ';') == $type) {
                    $foundPart = $part;
                    return $foundPart;
                    break;
                }
            } catch (Zend_Mail_Exception $e) {
                 log::error($e->getMessage());
            }
        }
    }
    

    
    
    
    function getFolders () {
            $folders = new RecursiveIteratorIterator($this->mail->getFolders(),
                                             RecursiveIteratorIterator::SELF_FIRST);

            
            $ary = array ();
            foreach ($folders as $local => $folder ) {
                $ary[htmlspecialchars($local)] = htmlspecialchars($folder);
            }
    
            return $ary;
    }
    
}


            //print_r($folders);
    //echo '<select name="folder">';
            //$ary = array ();
    /*
            foreach ($folders as $localName => $folder) {
        $localName = str_pad('', $folders->getDepth(), '-', STR_PAD_LEFT) .
                     $localName;
        echo '<option';
        if (!$folder->isSelectable()) {
            echo ' disabled="disabled"';
        }
        echo ' value="' . htmlspecialchars($folder) . '">'
            . htmlspecialchars($localName) . '</option>';
    }
    echo '</select>'; */