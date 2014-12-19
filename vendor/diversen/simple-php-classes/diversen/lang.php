<?php

namespace diversen;
use diversen\conf as config;
use diversen\moduleloader;
use diversen\cache;
use diversen\db\q as db_q;
use diversen\db;
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
     * flag indicating if all translations are loaded from single file
     * @var type 
     */
    public static $allLoaded = false;

    /**
     * var holding the language to use for a site
     * @var string $language 
     */
    public static $language = null;
    
    /**
     * var holding user language if set
     * @var string $userLanguage
     */
    public static $userLanguage = null;

    /**
     * var holding the translation table
     * @var array $dict
     */
    public static $dict = array ();
    
    /**
     * var holding loaded modules
     * @var array $loadedModules
     */
    public static $loadedModules = array ();

    /**
     * method for getting the language of the site. 
     * @return string $language the language to be used
     */
    public static function getLanguage (){
        
        if (self::$userLanguage) {
            return self::$userLanguage;
        }
        
        // in cli mode there is no option for loading users individual language
        if (!config::isCli()) {
            self::$userLanguage = cache::get('account_locales_language', \session::getUserId());
        }
        
        // if user language is loaded we will use user language
        if (isset(self::$userLanguage)) {
            self::$language = self::$userLanguage;
        } else {
            self::$language = config::getMainIni('language');
        }
        
        return self::$language;
    }
    
    /**
     * loads all system languages or loads language_all (from file or
     * from database)
     */
    public static function loadLanguage ($language = null) {
        $lang_all = config::getMainIni('language_all');
        if ($lang_all) {
            self::loadTemplateAllLanguage($language);       
        } else {
            self::loadSystemLanguage($language);
        } 
    }
    
    /**
     * method for loading main language. This will load the 
     * main language of the site. It is used if a user has set 
     * a language (e.g. admin needs to admin interface in his own language,
     * but needs to send e.g. mails to users in users own language).
     */
    public static function loadMainLanguage () {
        self::$allLoaded = false;
        $language = config::getMainIni('language');
        self::loadLanguage($language);
        self::$loadedModules = array ();
        foreach (moduleloader::$loadedModules['base'] as $key => $val) {
            self::loadModuleLanguage($key, $language);
        }
    }
    
    /**
     * method for initing and loading correct language
     * includes translations found in database (system)
     * 
     */
    public static function loadSystemLanguage($language = null){
        
        if (!$language) {
            $language = self::getLanguage();
        }
        $system_lang = array();
        $db = new db();
        $system_language = db_q::select('language')->
                filter('language =', $language)->
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
     *                  $_COS_LANG_MODULE['module_string'] = "You will be charged {AMOUNT} dear {USER_NAME}"
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
    static function loadModuleLanguage($module, $language = null){
        
        if (self::$allLoaded) {
            
            return;
        }
        
        if (isset(self::$loadedModules[$module])) {
            return;
        }
        
        if (!$language) {
            $language = self::getLanguage();
        }

        $base = _COS_PATH . '/' . _COS_MOD_DIR;
        $language_file =
            $base . "/$module" . '/lang/' .
            $language .
            '/language.inc';

        if (file_exists($language_file)){
            include $language_file;
            if (isset($_COS_LANG_MODULE)){
                self::$dict+= $_COS_LANG_MODULE;
            }
        }

        self::$loadedModules[$module] = true;
    }
    
    /**
     *
     * Loads a template language (templates/mytemplate/lang/en_GB/language.inc). 
     * The template language will only be loaded when atemplate is loaded, while
     * the system language (templates/mytemplate/lang/en_GB/system.inc) is put
     * into db on install, and therefor always loaded. 
     * @param   string  $template the base module to load (e.g. content or account)
     */
    public static function loadTemplateLanguage($template){

        static $loaded = array();
        
        if (self::$allLoaded) {
            return;
        }
        
        if (isset($loaded[$template])) {
            return;
        }
        
        $language = self::getLanguage();

        $base = _COS_HTDOCS . '/templates';
        $language_file =
            $base . "/$template" . '/lang/' .
            $language .
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
     * Loads a template all language, e.g. (templates/mytemplate/lang/en_GB/language-all.inc). 
     * It is based on the main ini setting language_all which should contain
     * The language-all.inc can be collected by using
     * <code>./coscli.sh translate --collect template en_GB</code>
     * It also checks for the locales module. If the locales module exists
     * there is a check for database modifications found in locales table
     */
    public static function loadTemplateAllLanguage($language = null){

        //  template which we load from
        $template = config::getMainIni('language_all');
        self::$allLoaded = true;
        

        if (!$language) {
            $language = self::getLanguage();
        }
        
        // check if there is a template_load_all
        if (moduleloader::isInstalledModule('locales')) {
            moduleloader::includeModule('locales');    
            self::$dict = locales_db::loadLanguageFromDb($language);
            return;
        }
        
        $base = _COS_HTDOCS . '/templates';
        $language_file =
            $base . "/$template" . '/lang/' .
            $language .
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
        if (self::$allLoaded) {
            return;
        }
        
        $language = self::getLanguage();
        $base = _COS_PATH . "/modules";

        $language_file =
            $base . "/$module" . '/lang/' .
            $language .
            '/system.inc';

        if (file_exists($language_file)){
            include $language_file;
            if (isset($_COS_LANG_MODULE)){
                self::$dict+= $_COS_LANG_MODULE;
            }
        }
    }
}
