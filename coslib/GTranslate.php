<?php

/**
 * File contains a simple class for doing translations with google 
 * translate API
 * @package gtranslate
 */

/**
 * class for doing translation with the goolge API
 * @package gtranslate
 */
class GTranslate {
    
    /**
     * var holding options
     * @var array $options
     */
    public static $options;

    /**
     * Gtranslate URL
     * @var string $Gtranslate_URL
     */
    const GTranslate_URL = 'https://www.googleapis.com/language/translate/v2';

    /**
     * set options for translation 
     * @param array $options 
     */
    public static function setOptions ($options) {
        self::$options = $options;
    }

    /**
     * method for getting all languages supported by the API
     * @return array $lang array of languages
     */
    public static function getSupportLangs (){
        $url = self::GTranslate_URL . "/languages?";
        $url.= "key=" . self::$options['key'] . "&";
        $url.= "target=" . self::$options['target'];
        $str = file_get_contents($url);
        $ary = json_decode($str, true);
        return $ary;
    }

    /**
     * merthod for translating a string
     * @param string $str string to be translated
     * @return string $str the translated string
     */
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

    /**
     * method for translating a single string
     * @param string $str the string to be translated
     * @return string $str the translated string. 
     */
    public static function translateSingle ($str) {
        $ary = self::translateString($str);
        print_r($ary);
        if (isset($ary['data']['translations'][0]['translatedText'])){
            return $ary['data']['translations'][0]['translatedText'];
        }
        return null;
    }
}
