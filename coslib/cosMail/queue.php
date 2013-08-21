<?php

class cosMail_queue {
    public static function addToqueue ($to, $mime_headers, $body) {
        cosRB::connect();
        $bean = cosRB::getBean('cosmail_queue');
        $bean->to = $to;
        $bean->mimeheaders = serialize($mime_headers);
        $bean->body = $body;
        return R::store($bean); 
    }
}