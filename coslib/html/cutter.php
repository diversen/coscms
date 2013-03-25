<?php

// from: https://code.google.com/p/htmlcutting/
// 
class html_cutter{
    private static $fakeSymb = "\r";
    private static $tags = array();
    private static $tagCounter = 0;
    private static $openTags = array();
    private static $closeTags = array();
    private static $exTags = array('br');

    private static function tagOut($tag){
        self::$tagCounter++;
        self::$tags[self::$tagCounter] = $tag;
        return self::$fakeSymb;
    }

    private static function tagIn($fake=''){
        self::$tagCounter++;
        $tag = self::$tags[self::$tagCounter];

        preg_match("/^<(\/?)(\w+)[^>]*>/i", $tag, $mathes);
        if (!in_array($mathes[2], self::$exTags)){
            if ($mathes[1]!='/'){
                self::$openTags[] = $mathes[2];
            } else {
                self::$closeTags[] = $mathes[2];
            }
        }
        return $tag;
    }


    function cut($text, $lenght=250){
        $text = html_entity_decode($text);
        $text = preg_replace("/".self::$fakeSymb."/", "", $text);

        //move all tags to array tags
        $text = preg_replace("/(<\/?)(\w+)([^>]*>)/e", "html_cutter::tagOut('\\0')", $text);

        //check how many tags in cutter text to fix cut length
        $preCut = mb_substr($text, 0, $lenght);
        $fakeCount = mb_substr_count($preCut, self::$fakeSymb);
        //cut string
        $text = mb_substr($text, 0, $lenght + ($fakeCount*mb_strlen(self::$fakeSymb)));
        //remove last word
        $text = preg_replace("/\S*$/", "", $text);

        //return tags back
        self::$tagCounter = 0;
        $text = preg_replace("/".self::$fakeSymb."/e", "html_cutter::tagIn()", $text);

        //get count not closed tags
        $closeCount = count(self::$openTags)-count(self::$closeTags);
        //close opened tags
        for ($i=0; $i < $closeCount; $i++){
            $tagName = array_pop(self::$openTags);
            $text .= "</{$tagName}>";
        }

        return $text;
    }
}