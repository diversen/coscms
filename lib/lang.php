<?php

/**
 * File contains contains class creating simple translation
 *
 * @package    coslib
 */

/**
 * Class for doing simple translations
 *
 * @package    coslib
 */
class lang {

    static public $language = '';

    static public $dict = array ();

    static function getLanguage (){
        return self::$language;
    }
    /**
     * method for initing and loading correct language
     * includes translations found in database and translations found in top
     * translation directory (lang),
     * 
     */


    static function init(){
        self::$language = register::$vars['coscms_main']['language'];

        $system_lang = array();
        $db = new db();
        $system_language = $db->select(
            'language',
            'language',
            register::$vars['coscms_main']['language']
        );

        // create system lanugage for all modules
        if (!empty($system_language)){
            foreach($system_language as $key => $val){
                $module_lang = unserialize($val['translation']);
                $system_lang = array_merge($system_lang, $module_lang);
            }
        }

        // include main language set in config/config.ini
        $lang_file =
            _COS_PATH .
            '/lang/' .
            register::$vars['coscms_main']['language'] . 
            '/language.inc';
        
        if (file_exists($lang_file)){
            include $lang_file;
            self::$dict = array_merge($_COS_LANG, $system_lang);
            return;
        } 
        self::$dict = $system_lang;
    }


    /**
     * method for doing translations. If a translation is not found we
     * prepend the untranslated string with 'NT' (needs translation)
     *
     * @param   string  $sentence the sentence to translate.
     * @param   array   array with substitution to perform on sentence.
     *                  e.g. array ('a name', 'a adresse')
     * @return  string  translated string
     */
    public static function translate($sentence, $substitute = array()){
        if (isset(self::$dict[$sentence])){
            if (!empty($substitute)){
                $i = 1;
                foreach ($substitute as $val) {
                    self::$dict[$sentence] = str_replace("%$i%", $val, self::$dict[$sentence]);
                    $i++;
                }
            }

            return self::$dict[$sentence];
        } else {
            return "NT: '$sentence'";
        }
    }
    
    /**
     * method for doing translations. The method calls translate. 
     * and it is an alias. BUT: In order to auto translate modules, 
     * you should use this function if you call translation found
     * in the system module. E.g. for default submit buttons.   
     *
     * @param   string  $sentence the sentence to translate.
     * @param   array   array with substitution to perform on sentence.
     *                  e.g. array ('a name', 'a adresse')
     * @return  string  translated string
     */
    public static function system($sentence, $substitute = array()){
        return self::translate($sentence, $substitute);
    }

    /**
     *
     *
     * @param   string   the base module to load (e.g. content or account)
     */
    static function loadModuleLanguage($module){
        static $loaded = array();
        
        if (isset($loaded[$module])) {
            return;
        }

        $base = _COS_PATH . "/modules";

        $language_file =
            $base . "/$module" . '/lang/' .
            register::$vars['coscms_main']['language'] .
            '/language.inc';

        if (file_exists($language_file)){
            include $language_file;
            if (isset($_COS_LANG_MODULE)){
                self::$dict = array_merge(self::$dict, $_COS_LANG_MODULE);
            }
        }

        $loaded[$module] = true;
    }

    /**
     *
     *
     * @param   string   the base module to load (e.g. content or account)
     */
    static function loadModuleSystemLanguage($module){
        $base = _COS_PATH . "/modules";

        $language_file =
            $base . "/$module" . '/lang/' .
            register::$vars['coscms_main']['language'] .
            '/system.inc';

        if (file_exists($language_file)){
            include $language_file;
            if (isset($_COS_LANG_MODULE)){
                self::$dict = array_merge(self::$dict, $_COS_LANG_MODULE);
            }
        }
    }
}
