<?php

class GTranslate {
    public static $options;

    const GTranslate_URL = 'https://www.googleapis.com/language/translate/v2';

    public static function setOptions ($options) {
        self::$options = $options;
    }

    public static function getSupportLangs (){
        $url = self::GTranslate_URL . "/languages?";
        $url.= "key=" . self::$options['key'] . "&";
        $url.= "target=" . self::$options['target'];
        $str = file_get_contents($url);
        $ary = json_decode($str, true);
        return $ary;
    }

    public static function translateString ($str) {
        $url = self::GTranslate_URL . "?";
        $url.= "key=" . self::$options['key'] . "&";
        $url.= "target=" . self::$options['target'] . "&";
        $url.= "source=" . self::$options['source'] . "&";
        $url.= "q=" . urlencode($str);
        $str = file_get_contents($url);
        $ary = json_decode($str, true);
        return $ary;
    }

    public static function translateSingle ($str) {
        $ary = self::translateString($str);
        if (isset($ary['data']['translations'][0]['translatedText'])){
            return $ary['data']['translations'][0]['translatedText'];
        }
        return null;
    }
}