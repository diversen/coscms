<?php

/**
 * File contains contains class creating simple translation
 *
 * @package    lang
 */

/**
 * Class for doing simple translations
 *
 * @package    lang
 */
class lang {

    /**
     * var holding the language to use for a site
     * @var string $language 
     */
    static public $language = '';

    /**
     * var holding the translation table
     * @var array $dict
     */
    static public $dict = array ();

    /**
     * method for getting the language of the site. 
     * @return string $language the language to be used
     */
    static function getLanguage (){
        return self::$language;
    }
    
    /**
     * method for initing and loading correct language
     * includes translations found in database (system)
     * 
     */
    public static function init(){
        self::$language = config::$vars['coscms_main']['language'];

        $system_lang = array();
        $db = new db();
        $system_language = $db->select(
            'language',
            'language',
            config::$vars['coscms_main']['language']
        );

        // create system lanugage for all modules
        if (!empty($system_language)){
            foreach($system_language as $key => $val){
                $module_lang = unserialize($val['translation']);
                $system_lang+= $module_lang;
            }
        }      
        self::$dict = $system_lang;
    }


    /**
     * method for doing translations. If a translation is not found we
     * prepend the untranslated string with 'NT' (needs translation)
     *
     * @param   string  $sentence the sentence to translate.
     * @param   array   $substitute array with substitution to perform on sentence.
     *                  e.g. array ('100$', 'username')
     *                  in the string to be translated you will then have e.g.
     *                  $_COS_LANG_MODULE['module_string'] = 
     *                  "You will be charged %1% dear %2%"
     * @return  string  $str translated string
     *                  if no translation is found in translation registry,
     *                  the string suplied will have "NT: " prepended. 
     *                  (Not Translated)
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
     * and it is an alias. But: In order to auto translate modules, 
     * you should use this function if you call translation found
     * in the system module. E.g. for default submit buttons.
     * This is because the ./coscli.sh translate commend looks 
     * for strings which uses lang::translate('string);   
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
     * Loads a module language (modules/yourmodule/lang/en_GB/language.inc). 
     * The module language will only be loaded when a module is loaded, while
     * the system language (modules/yourmodule/lang/en_GB/system.inc) is put
     * into db on installm, and therefor always loaded. 
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
            config::$vars['coscms_main']['language'] .
            '/language.inc';

        if (file_exists($language_file)){
            include $language_file;
            if (isset($_COS_LANG_MODULE)){
                self::$dict+= $_COS_LANG_MODULE;
            }
        }

        $loaded[$module] = true;
    }

    /**
     *
     * method for loaindg a system language. 
     * @param   string   the base module to load (e.g. content or account)
     */
    static function loadModuleSystemLanguage($module){
        $base = _COS_PATH . "/modules";

        $language_file =
            $base . "/$module" . '/lang/' .
            config::$vars['coscms_main']['language'] .
            '/system.inc';

        if (file_exists($language_file)){
            include $language_file;
            if (isset($_COS_LANG_MODULE)){
                self::$dict+= $_COS_LANG_MODULE;
            }
        }
    }
}
