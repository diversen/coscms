<?php

/**
 * @package     translate
 */

/**
 * find all lang::translate('sentences') in a string and returns them
 * @param string $str
 * @return array $strings
 */
function translate_match ($str) {
    // find all strings matching inside lang::translate('e.g. Here is a sentense')
    $search = "/lang::translate\('([^']+)'/s";
    preg_match_all($search, $str, $out);
    $strings = $out[1];
    $strings = array_unique($strings);
    
    $search = '/lang::translate\("([^"]+)"/s';
    preg_match_all($search, $str, $out2);
    $strings2 = $out2[1];
    $strings = array_merge($strings, $strings2);
    
    return $strings;
}

/**
 * if a string uses '' we write the array with ""
 * if a string uses "" we write the array with ''
 * @param type $str
 */
function translate_with_quote ($key, $value = '') {
    if (empty($value)) $value = $key; $str = '';
    
    // search for apostrophe (') somewhere 
    // in order to know how to write out the array
    $apo_key = strpos($key, "'");
    if ($apo_key) {
        $str.= "\$" . '_COS_LANG_MODULE["'.$key.'"] = ';
    } else {
        $str.= "\$_COS_LANG_MODULE['$key'] = ";
    }
    
    // and also for the value
    $apo_val = strpos($key, "'");
    if ($apo_val) {
        $str.= '"'.$value.'";' . "\n";
    } else {
        $str.= "'$value';" . "\n";
    }
        
    return $str;
}

/**
 * function for creating a file with all strings
 * to be translated in a module by traversing all files
 * in the module.
 *
 *
 * @param array $options
 */
function translate($options){

    $strings_all = array();
    $strings_all[] = '';

    if (isset($options['template'])) {
        $module_dir = _COS_HTDOCS . '/templates/' . $options['module'];
        $lang_dir = _COS_HTDOCS . "/templates/$options[module]/lang/$options[language]";
    } else {
        $module_dir = _COS_MOD_PATH . "/$options[module]";
        $lang_dir = _COS_MOD_PATH . "/$options[module]/lang/$options[language]";
    }
        
    if (!file_exists($module_dir)){
        cos_cli_print_status('Notice', 'y', "No such module dir. Skipping: $module_dir");
        return;
    }
    
    if (!file_exists($module_dir . "/lang")) {
        mkdir($module_dir . "/lang");
    }
    
    if (!file_exists($module_dir . "/lang/$options[language]")) {
        mkdir($module_dir . "/lang/$options[language]");
    }

    // get all files  from modules dir
    $file_list = file::getFileListRecursive($module_dir);
    
    // compose a php file
    $str = $sys_str = "<?php\n\n";
    
    foreach ($file_list as $val){
  
        if (!translate_is_text($val)) {
            continue;
        }

        $file_str = file_get_contents($val);    
        $strings = translate_match ($file_str);


        // no strings we continue
        if (empty($strings)) continue;

        if (strstr($val, 'menu.inc') || strstr($val, 'install.inc') ||  strstr($val, 'system_lang.inc') ){
            // system translation
            // we add the file info to translation as comment
            $file = str_replace(_COS_PATH, '', $val);
            $sys_str.="// Translation of file $file\n\n";

            // and we add all strings in that file
            foreach ($strings as $trans){
                $sys_str.= translate_with_quote($trans);
            }
        } else {

            // we add the file info to translation as comment
            $file = str_replace(_COS_PATH, '', $val);
            $str.="// Translation of file $file\n\n";

            // and we add all strings in that file
            foreach ($strings as $trans){
                // check if string already has been translated
                if (array_search($trans, $strings_all)) {
                    continue;
                }
                $str.= translate_with_quote($trans);
            }

        }
        $values = array_values($strings);
        $strings_all = array_merge($strings_all, $values);

    }

    if (!file_exists($lang_dir)){
        $res = mkdir($lang_dir);
        if ($res){
            cos_cli_print("Dir: $lang_dir created\n");
        } else {
            cos_cli_abort("Dir could not be created: $lang_dir\n");
        }
    }

    // final: write the translation file
    $write_file = $lang_dir . "/language.inc";

    // issue warning if language file already exists
    if (file_exists($write_file)){
        if (!cos_confirm_readline("language files already exists.\nThese file will be over written")) {
            cos_cli_abort();
        }
    }

    file_put_contents($write_file, rtrim($str) . "\n");

    // final: write the translation file
    $write_sys_file = $lang_dir . "/system.inc";
    file_put_contents($write_sys_file, rtrim($sys_str) . "\n");
}


/**
 * will update all translation files in specified language
 * @param array $options
 */
function translate_all_update ($options) {
    $mods = moduleloader::getAllModules();
    foreach ($mods as $mod) {
        cos_cli_print_status('Notice', 'g', "Translating $mod[module_name]");
        $options['module'] = $mod['module_name'];
        translate_update($options);
    }
    
    $temps = layout::getAllTemplates();
    foreach ($temps as $mod) {
        $options['template'] = true;
        cos_cli_print_status('Notice', 'g', "Translating template $mod");
        $options['module'] = $mod;
        translate_update($options);
    }
    
    
}

function translate_temp($options){ 
    $options['template'] = true;
    translate($options);
}

function translate_temp_update($options){ 
    $options['template'] = true;
    translate_update($options);
}

/**
 * check if prim mime type is text
 * @param string $filename
 * @return boolean $res true if text else false
 */
function translate_is_text ($file) {
    $prim_mime = file::getPrimMime($file);
    if ($prim_mime == 'text') {
        return true;
    }
    return false;
}

/**
 * function for creating a file with all strings
 * to be translated in a module by traversing all files
 * in the module.
 *
 * Could easily be refined to check if strings are translated
 * So far it is as it is .)
 *
 * @param array $options
 */
function translate_update($options){
   
    if (isset($options['template'])) {
        $module_dir = _COS_HTDOCS . '/templates/' . $options['module'];
        $lang_dir = _COS_HTDOCS . "/templates/$options[module]/lang/$options[language]";
    } else {
        $module_dir = _COS_MOD_PATH . "/$options[module]";
        $lang_dir = _COS_MOD_PATH . "/$options[module]/lang/$options[language]";
    }
    
    if (!file_exists($module_dir)){
        cos_cli_print_status('Notice', 'y', "No such module|template dir. Skipping: $module_dir");
        return;
    }
    
    $translate_dir = $module_dir . "/lang/$options[language]";
    $translate_file = $translate_dir . "/language.inc";
    $system_file = $translate_dir . "/system.inc";
    
    // just do start translation if file does not exists
    if (!file_exists($translate_file)){
        translate($options);
        return;
    }

    include $translate_file;
    if (!isset($_COS_LANG_MODULE)) {
        $org_lang = $_COS_LANG_MODULE = array ();
    } else {
        $org_lang = $_COS_LANG_MODULE;
    }
        
    if (file_exists($system_file)){
        include $system_file;
        $org_lang = array_merge($_COS_LANG_MODULE, $org_lang);
    }


    // compose a php file
    $translation_str = $translation_sys_str = "<?php\n\n";
    
    // get all files  from modules dir
    $file_list = file::getFileListRecursive($module_dir);

    // arrays to use if we know if some strings are extracted
    $done_system = $done_lang = array ();
    foreach ($file_list as $val){
        
        if (!translate_is_text($val)) {
            continue;
        }
        
        $file_str = file_get_contents($val);


        $strings = translate_match ($file_str);
        // no strings we continue
        if (empty($strings)) continue;

        // and we add all strings
        // all menus are added to system translation
        
        if (strstr($val, 'menu.inc') || strstr($val, 'install.inc') ||  strstr($val, 'system_lang.inc') ){
            
            $file = str_replace(_COS_PATH, '', $val);
            $translation_sys_str.="\n// Translation of file $file\n\n";
            
            foreach ($strings as $trans){
                if (isset($done_system[$trans])) continue;
                $done_system[$trans] = 1;
                
                // if string is not set in _COS_LANG_MODULE
                if (!isset($org_lang[$trans])){
                    $translation_sys_str.=translate_with_quote($trans);
                } else {
                    $new_str = $org_lang[$trans];
                    $translation_sys_str.= translate_with_quote($trans, $new_str);
                }
            }
        } else {
            
            $file = str_replace(_COS_PATH, '', $val);
            $translation_str.="\n// Translation of file $file\n\n";
            
            foreach ($strings as $trans){
                
                if (isset($done_lang[$trans])) continue;
                $done_lang[$trans] = 1;
                
                if (!isset($org_lang[$trans])){
                    $translation_str.= translate_with_quote($trans);
                } else {
                    $new_str = $org_lang[$trans];
                    $translation_str.=translate_with_quote($trans, $new_str);
                }
            }
        }
    }

    $lang_dir = $module_dir . "/lang/$options[language]";
    if (!file_exists($lang_dir)){
        $res = mkdir($lang_dir);
        if ($res){
            cos_cli_print("Dir: $lang_dir created\n");
        } else {
            cos_cli_abort("Dir could not be created: $lang_dir\n");
        }
    }

    // final: write the translation file
    $write_file = $lang_dir . "/language.inc";
    file_put_contents($write_file, rtrim($translation_str) . "\n");

    // final: write the translation file
    $write_sys_file = $lang_dir . "/system.inc";
    file_put_contents($write_sys_file, rtrim($translation_sys_str) . "\n");

}

function translate_collect ($options) {

    $mods = moduleloader::getAllModules();
    $system = array ();
    $language = array ();
    
    
    // write out string
    $str = "<?php\n\n";
    foreach ($mods as $mod) {
        
        $mod_lang = _COS_MOD_PATH . "/$mod[module_name]/lang/$options[language]";
        $system_file = $mod_lang . "/system.inc";
        $language_file = $mod_lang . "/language.inc";
        if (file_exists($system_file)) {
            
            $file = str_replace(_COS_PATH, '', $system_file);
            $str.="\n// Translation of file $file\n\n";
            
            include $system_file;
            
            foreach ($_COS_LANG_MODULE as $key => $val){
                $str.=translate_with_quote($key, $val);
            }
            $_COS_LANG_MODULE = array();

        }
        
        if (file_exists($language_file)) {
            
            $file = str_replace(_COS_PATH, '', $language_file);
            $str.="\n// Translation of file $file\n\n";
            
            include $language_file;
            
            foreach ($_COS_LANG_MODULE as $key => $val){
                $str.=translate_with_quote($key, $val);
            }
            $_COS_LANG_MODULE = array();

        }
    }


    $temps = layout::getAllTemplates();
    foreach ($temps as $temp) {
        $mod_lang = _COS_HTDOCS . "/templates/$temp/lang/$options[language]";
        $system_file = $mod_lang . "/system.inc";
        $language_file = $mod_lang . "/language.inc";
        
        if (file_exists($system_file)) {
            
            $file = str_replace(_COS_HTDOCS, '', $system_file);
            $str.="\n// Translation of file $file\n\n";
            
            include $system_file;
            foreach ($_COS_LANG_MODULE as $key => $val){
                $str.=translate_with_quote($key, $val);
            }
            $_COS_LANG_MODULE = array();
        }
        
        if (file_exists($language_file)) {
            
            $file = str_replace(_COS_HTDOCS, '', $language_file);
            $str.="\n// Translation of file $file\n\n";
            
            include $language_file;
            foreach ($_COS_LANG_MODULE as $key => $val){
                $str.=translate_with_quote($key, $val);
            }
            $_COS_LANG_MODULE = array();
        }
    }

    
    $mod_lang = _COS_HTDOCS . "/templates/$options[module]/lang/$options[language]";
    $lang_all = $mod_lang . "/language-all.inc";
    
    file_put_contents($lang_all, $str);

}


mainCli::setCommand('translate', array(
    'description' => 'Will extract a translation file for specified language for a module|template by extracting all lang::translate(\'strings\')',
));

mainCli::setOption('translate', array(
    'long_name'   => '--translate',
    'short_name'   => '-t',
    'description' => 'Create a translation file from all strings that should be translated.',
    'action'      => 'StoreTrue'
));

mainCli::setOption('translate_update', array(
    'long_name'   => '--update',
    'short_name'   => '-u',
    'description' => 'Updates a translation file from all strings that should be translated.',
    'action'      => 'StoreTrue'
));

mainCli::setOption('translate_temp', array(
    'long_name'   => '--temp',
    'description' => 'Create a translation file for a template from all strings that can be translated.',
    'action'      => 'StoreTrue'
));

mainCli::setOption('translate_temp_update', array(
    'long_name'   => '--temp-up',
    'description' => 'Update a translation file for a template with new strings found.',
    'action'      => 'StoreTrue'
));

mainCli::setOption('translate_all_update', array(
    'long_name'   => '--all-up',
    'description' => 'Update a translation file for a template with new strings found. Set a dummy module arg e.g. all',
    'action'      => 'StoreTrue'
));

mainCli::setOption('translate_collect', array(
    'long_name'   => '--collect',
    'description' => 'Collected all translations for a language in a single file in a specified module',
    'action'      => 'StoreTrue'
));

mainCli::setArgument('module',
    array('description'=> 'Specicify the module or template for which you will make a translation. If spcified in collect command, then the module is where the collected translation are found',
          'optional' => false));

mainCli::setArgument('language',
    array('description'=> 'Specicify the folder in lang which will serve as language, e.g. en_GB or da_DK or en or da. Also the language which will be collected',
          'optional' => false));

