<?php

class cosMailQue {
    public static function addToQue ($to, $mime_headers, $body) {
        cosRB::connect();
        $bean = cosRB::getBean('cosmailque');
        $bean->to = $to;
        $bean->mimeheaders = serialize($mime_headers);
        $bean->body = $body;
        return R::store($bean);      
    }
}