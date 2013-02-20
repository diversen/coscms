<?php

/**
 *
 * class for highlighting source code with native php higlight_string function.
 * @package    filters
 */


/**
 * class for highlighting php with native php functions.
 */
class phphl {

    /**
     *
     * @var string $language - the language to use.
     */
    private $lang;

    public static function init () {
        static $run = null;
        if (!$run) {
            //template::setInlineCss(config::getModulePath('filter_php') . "/assets/filter_php.css");
            $run = 1;
        }
    }
    
    /**
     *
     * @param string $article string to filter.
     * @return 
     */
    public function filter($article){
        
        self::init();
        
        //if (config::getModuleIni('filter_php_use_files')) {
            $article = self::filterPhpFile($article);
        //}
        $article = self::filterPhpInline($article);
        return $article;
        
    }
    
    /**
     * filter string for files
     * @param string $article
     * @return string $str
     */
    public static function filterPhpFile ($article) {
        // find all codes of type [hl:lang]
        $reg_ex = "{(\[hl_file:[a-z\]]+)}i";
        preg_match_all($reg_ex, $article, $match);
        $match  = array_unique($match[1]);
        
        foreach ($match as $key => $val){
            $ary = explode(":", $val);
            preg_match("{([a-z]+)}i", $ary[1], $lang);
            
            if (isset($lang[0]) && isset($lang[1])){
                if ($lang[0] == $lang[1]){          
                    if ($lang[0] == 'php'){
                        $article = filterPhpHighlightCodeFile($article, $lang[0]);
                    } 
                }
            }
        }
        return $article;
    }

    /**
     * filter string for inline php
     * @param string $article
     * @return string $str
     */
    public static function filterPhpInline ($article) {
        // find all codes of type [hl:lang]
        $reg_ex = "{(\[hl:[a-z\]]+)}i";
        preg_match_all($reg_ex, $article, $match);
        $match  = array_unique($match[1]);
        
        foreach ($match as $key => $val){
            $ary = explode(":", $val);
            preg_match("{([a-z]+)}i", $ary[1], $lang);
            
            if (isset($lang[0]) && isset($lang[1])){
                if ($lang[0] == $lang[1]){                    
                    if ($lang[0] == 'php'){
                        $article = filterPhpHighlightCode($article, $lang[0]);
                    }
                }
            }
        }
        return $article;
    }
}

function filter_php_add_div ($str) {
    return "<div id=\"php\">$str</div>\n";
    return $str;
}


function filterPhpHighlightCode($str, $lang){
    $str = preg_replace_callback("{\[hl:$lang\]((.|\n)+?)\[/hl:$lang\]}i",  'filterPhpReplaceCode', $str);
    return $str;
}

function filterPhpReplaceCode($replace){
    $str = trim($replace[1], "\n ");    
    $str = highlight_string($str, true);
    if (config::getModuleIni('filter_php_add_div')) {
        $str = filter_php_add_div($str);
    }
    return $str;
}

function filterPhpHighlightCodeFile($file, $lang){
    $str = preg_replace_callback("{\[hl_file:$lang\]((.|\n)+?)\[/hl_file:$lang\]}i",  'filterPhpReplaceCodeFile', $file);    
    return $str;
}



function filterPhpReplaceCodeFile($file){
    $file =  trim ($file[1]);
    if (!file_exists($file)){
        return "File does not exists: $file";
    }
    $str = file_get_contents($file);
    $str = highlight_string($str, true);
    if (config::getModuleIni('filter_php_add_div')) {
        $str = filter_php_add_div($str);
    }
    
    return $str;
}
