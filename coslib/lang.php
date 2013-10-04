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
    
    /**
     * flag indicating if all translations are loaded from single file
     * @var type 
     */
    public static $allLoaded = false;

    /**
     * var holding the language to use for a site
     * @var string $language 
     */
    public static $language = '';

    /**
     * var holding the translation table
     * @var array $dict
     */
    public static $dict = array ();

    /**
     * method for getting the language of the site. 
     * @return string $language the language to be used
     */
    public static function getLanguage (){
        return self::$language;
    }
    
    /**
     * method for initing and loading correct language
     * includes translations found in database (system)
     * 
     */
    public static function init(){
        
        self::setAdminLanguage();
        self::$language = config::getMainIni('language');

        $system_lang = array();
        $db = new db();
        $system_language = db_q::select('language')->
                filter('language =', config::$vars['coscms_main']['language'])->
                condition('AND')->
                filter('module_name != ', 'language_all')->
                fetch();
                
        // create system lanugage for all modules
        if (!empty($system_language)){
            foreach($system_language as $val){
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
    public static function translate($sentence, $substitute = array(), $options = array ()){
        
        if (isset($options['no_translate'])) {
            return $sentence;
        }
        
        if (isset(self::$dict[$sentence])){
            if (!empty($substitute)){
                foreach ($substitute as $key => $val) {
                    self::$dict[$sentence] = str_replace('{'.$key.'}', $val, self::$dict[$sentence]);
                }
            }
            return self::$dict[$sentence];
        } else {
            if (!empty($substitute)){
                foreach ($substitute as $key => $val) {
                    $sentence = str_replace('{'.$key.'}', $val, $sentence);
                }
            }
            // don't add NT
            if (isset(config::$vars['coscms_main']['translate_ignore'])) {
                return $sentence;
            } else {
                return "NT: $sentence";
            }
        }
    }
    
    public static function setAdminLanguage () {
        if (session::isAdmin() && config::getMainIni('language_admin')) {
            $language_admin = config::getMainIni('language_admin'); 
            config::$vars['coscms_main']['language'] = $language_admin;
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
     * into db on install, and therefor always loaded. 
     * @param   string  $module the base module to load (e.g. content or account)
     */
    static function loadModuleLanguage($module){
        
        self::setAdminLanguage();
        if (self::$allLoaded) {
            return;
        }
        
        static $loaded = array();
        if (isset($loaded[$module])) {
            return;
        }
        
        

        $base = _COS_PATH . '/' . _COS_MOD_DIR;
        $language_file =
            $base . "/$module" . '/lang/' .
            config::getMainIni('language') .
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
     * Loads a template language (templates/mytemplate/lang/en_GB/language.inc). 
     * The template language will only be loaded when atemplate is loaded, while
     * the system language (templates/mytemplate/lang/en_GB/system.inc) is put
     * into db on install, and therefor always loaded. 
     * @param   string  $template the base module to load (e.g. content or account)
     */
    static function loadTemplateLanguage($template){
        
        self::setAdminLanguage();
        static $loaded = array();
        
        if (self::$allLoaded) {
            return;
        }
        
        if (isset($loaded[$template])) {
            return;
        }

        $base = _COS_HTDOCS . '/templates';
        $language_file =
            $base . "/$template" . '/lang/' .
            config::getMainIni('language') .
            '/language.inc';

        if (file_exists($language_file)){
            include $language_file;
            if (isset($_COS_LANG_MODULE)){
                self::$dict+= $_COS_LANG_MODULE;
            }
        }

        $loaded[$template] = true;
    }
    
    /**
     * Loads a template all language (templates/mytemplate/lang/en_GB/language-all.inc). 
     * It is based on the main ini setting language_all which should contain
     * The language-all.inc can be collected by using
     * <code>./coscli.sh translate --collect template en_GB</code>
     */
    public static function loadTemplateAllLanguage(){
        
        self::setAdminLanguage();
        //echo config::getMainIni('language');die;
        if (self::$allLoaded) {
            return;
        }
        
        //  template which we load from
        $template = config::getMainIni('language_all');
        self::$allLoaded = true;
        
        // check if there is a template_load_all
        if (moduleloader::isInstalledModule('locales')) {
            include_module('locales');
            self::$dict = locales_db::loadLanguageFromDb(config::getMainIni('language'));
            return;
        }
        
        $base = _COS_HTDOCS . '/templates';
        $language_file =
            $base . "/$template" . '/lang/' .
            config::getMainIni('language') .
            '/language-all.inc';

        if (file_exists($language_file)){
            include $language_file;
            if (isset($_COS_LANG_MODULE)){
                self::$dict+= $_COS_LANG_MODULE;
            }
        }

        $loaded[$template] = true;
    }

    /**
     *
     * method for loaindg a system language. 
     * @param   string   the base module to load (e.g. content or account)
     */
    static function loadModuleSystemLanguage($module){
        self::setAdminLanguage();
        if (self::$allLoaded) {
            return;
        }
        
        $base = _COS_PATH . "/modules";

        $language_file =
            $base . "/$module" . '/lang/' .
            config::getMainIni('language') .
            '/system.inc';

        if (file_exists($language_file)){
            include $language_file;
            if (isset($_COS_LANG_MODULE)){
                self::$dict+= $_COS_LANG_MODULE;
            }
        }
    }
}
