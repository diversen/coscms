<?php

/**
 * @package     coslib
 */

/**
 * function for creating a file with all strings
 * to be translated in a module by traversing all files
 * in the module.
 *
 * So far it is as it is .)
 *
 * @param array $options
 */
function translate($options){


    $strings_all = array();
    $strings_all[] = '';

    if (isset($options['template'])) {
        $module_dir = _COS_HTDOCS . '/templates/' . $options['module'];
        $lang_dir = _COS_HTDOCS . "/templates/$options[module]/lang/$options[Language]";
    } else {
        $module_dir = _COS_MOD_PATH . "/$options[module]";
        $lang_dir = _COS_MOD_PATH . "/$options[module]/lang/$options[Language]";
    }
    
    
    // don't translate if we don't have a module dir.
    if (!file_exists($module_dir)){
        cos_cli_abort('No such dir: ' . $module_dir);
    }

    // get all files  from modules dir
    $file_list = file::getFileListRecursive($module_dir);
    
    // compose a php file
    $str = $sys_str = "<?php\n\n";
    foreach ($file_list as $key => $val){
        $file_str = file_get_contents($val);
        
        if (!translate_is_text($val)) {
            continue;
        }

        // find all strings matching inside lang::translate('[]')
        $search = '/lang::translate\(\'([^\']+)\'\)/s';
        preg_match_all($search, $file_str, $out);
        $strings = $out[1];

        $strings = array_unique($strings);

        // no strings we continue
        if (empty($strings)) continue;

        if (strstr($val, 'menu.inc')){
            // system translation
            // we add the file info to translation as comment
            $sys_str.="// Translation of menu file $val\n\n";

            // and we add all strings in that file
            foreach ($strings as $trans){
                $sys_str.= "\$_COS_LANG_MODULE['$trans'] = '$trans';" . "\n";
            }
        } else {

            // we add the file info to translation as comment
            $str.="// Translation of file $val\n\n";

            // and we add all strings in that file
            foreach ($strings as $trans){
                // check if string already has been translated
                if (array_search($trans, $strings_all)) {
                    continue;
                }
                $str.= "\$_COS_LANG_MODULE['$trans'] = '$trans';" . "\n";
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
        if (!cos_confirm_readline("Language files already exists.\nThese file will be over written")) {
            cos_cli_abort();
        }
    }

    file_put_contents($write_file, $str);

    // final: write the translation file
    $write_sys_file = $lang_dir . "/system.inc";
    file_put_contents($write_sys_file, $sys_str);
}

function translate_temp($options){ 
    $options['template'] = true;
    translate($options);
}

function translate_is_text ($val) {
    $str = file::getMime($val);
    $ary = explode('/', $str);
    if ($ary[0] == 'text') {
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

    $module_dir = _COS_MOD_PATH . "/$options[module]";
    if (!file_exists($module_dir)){
        cos_cli_abort('No such module dir: ' . $module_dir);
    }

    $translate_dir = $module_dir . "/lang/$options[Language]";

    $translate_file = $translate_dir . "/language.inc";
    if (!file_exists($translate_file)){
        cos_cli_abort('No such translation file: ' . $translate_file);
    }


    include $translate_file;

    $lang = $_COS_LANG_MODULE;
    $translation_str = file_get_contents($translate_file) . "\n";

    $system_file = $translate_dir . "/system.inc";

    if (file_exists($system_file)){
        $translation_sys_str = file_get_contents($system_file) ."\n";
        include $system_file;
        $_COS_LANG_MODULE = array_merge($lang, $_COS_LANG_MODULE);
    }
    
    // get all files  from modules dir
    $file_list = file::getFileListRecursive($module_dir);

    // compose a php file
    foreach ($file_list as $key => $val){
        $file_str = file_get_contents($val);

        // find all strings matching inside lang::translate('[]')
        $search = '/lang::translate\(\'([^\']+)\'\)/s';
        preg_match_all($search, $file_str, $out);
        $strings = $out[1];

        // no strings we continue
        if (empty($strings)) continue;
        $strings = array_unique($strings);
        // we add the file info to translation as comment
        //$str.="// Auto updated translation " . " $val\n\n";

        // and we add all strings in that file
        if (strstr($val, 'menu.inc')){
            foreach ($strings as $trans){
                if (!isset($_COS_LANG_MODULE[$trans])){
                    $translation_sys_str.= "\$_COS_LANG_MODULE['$trans'] = '$trans';" . "\n";
                }
            }
        } else {
            foreach ($strings as $trans){
                if (!isset($_COS_LANG_MODULE[$trans])){
                    $translation_str.= "\$_COS_LANG_MODULE['$trans'] = '$trans';" . "\n";
                }
            }
        }
    }


    $lang_dir = _COS_MOD_PATH . "/$options[module]/lang/$options[Language]";
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
    file_put_contents($write_file, $translation_str);

    // final: write the translation file
    $write_sys_file = $lang_dir . "/system.inc";
    file_put_contents($write_sys_file, $translation_sys_str);

}

mainCli::setCommand('translate', array(
    'description' => 'Will create a translation file for specified language for a module',
));

mainCli::setOption('translate', array(
    'long_name'   => '--translate',
    'description' => 'Create a translation file from all strings that should be translated.',
    'action'      => 'StoreTrue'
));

mainCli::setOption('translate_update', array(
    'long_name'   => '--update',
    'description' => 'Create a translation file from all strings that should be translated.',
    'action'      => 'StoreTrue'
));

mainCli::setOption('translate_temp', array(
    'long_name'   => '--temp-tr',
    'description' => 'Create a translation file for a template from all strings that can be translated.',
    'action'      => 'StoreTrue'
));

mainCli::setOption('translate_temp_update', array(
    'long_name'   => '--temp-tr',
    'description' => 'Update a translation file with new strings being found.',
    'action'      => 'StoreTrue'
));

mainCli::setArgument('module',
    array('description'=> 'Specicify the module or template for which you will make a translation',
          'optional' => false));

mainCli::setArgument('Language',
    array('description'=> 'Specicify the folder in lang which will serve as language, e.g. en_GB or da_DK or en or da',
          'optional' => false));

