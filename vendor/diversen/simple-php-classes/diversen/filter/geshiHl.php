<?php

namespace diversen\filter;
use diversen\conf as config;

/**
 * file contains filter for higlighting using geshi
 * You will need to have a recent version of geshi
 * where it can be inclued from
 * 
 * @package    filters
 */

/**
 * @ignore
 */
include_once "geshi/geshi.php";

/**
 * file contains filter for higlighting using geshi
 * You will need to have a recent version of geshi
 * where it can be inclued from
 * 
 * @package    filters
 */



class geshiHl {

    /**
     *
     * @var string $language - the language to use.
     */
    private $lang;


    /**
     * highlight a text using geshi. 
     * in your text you will need, e.g. for PHP,
     * <code>[hl:php]<?php echo "hello world";?>[/hl:php]</code>
     * @param string $text string to filter.
     * @return string $text the filtered text
     */
    public function filter($article){
        
        if (config::getMainIni('filters_allow_files')) {
            $article = self::filterGeshiFile($article);
        }
        $article = self::filterGeshiInline($article);
        return $article;
        
    }
    /**
     * filter string for inline php
     * @param string $article
     * @return string $str
     */
    public function filterGeshiInline ($article) {
        // find all codes of type [hl:lang]
        $reg_ex = "{(\[hl:[a-z\]]+)}i";
        preg_match_all($reg_ex, $article, $match);
        $match  = array_unique($match[1]);
        
        foreach ($match as $key => $val){
            $ary = explode(":", $val);
            preg_match("{([a-z]+)}i", $ary[1], $lang);
            
            if (isset($lang[0]) && isset($lang[1])){
                if ($lang[0] == $lang[1]){     
                    $article = $this->highlightCode($article, $lang[0]);
                }
            }
        }
        return $article;
    }
    
    /**
     * filter string for files
     * @param string $article
     * @return string $str
     */
    public function filterGeshiFile ($article) {
        // find all codes of type [hl:lang]
        $reg_ex = "{(\[hl_file:[a-z\]]+)}i";
        preg_match_all($reg_ex, $article, $match);
        $match  = array_unique($match[1]);
        
        foreach ($match as $key => $val){
            $ary = explode(":", $val);
            preg_match("{([a-z]+)}i", $ary[1], $lang);
            
            if (isset($lang[0]) && isset($lang[1])){
                if ($lang[0] == $lang[1]){     
                    $article = $this->HighlightCodeFile($article, $lang[0]);
                }
            }
        }
        return $article;
    }


    /**
     *
     * @param <string> $str the string to perform highlighting on.
     * @param <string> $code the language (php, c++, etc.)
     * @return <string> the $highlighted string
     */
    public function highlightCodeFile(&$str, $lang){
        $this->lang  = $lang;
        $ret = preg_replace_callback("{\[hl_file:$lang\]((.|\n)+?)\[/hl_file:$lang\]}i",array('self', 'replaceCodeFile'), $str);
        return $ret;
    }
    /**
     *
     * @param <string> $str the string to perform highlighting on.
     * @param <string> $code the language (php, c++, etc.)
     * @return <string> the $highlighted string
     */
    public function highlightCode(&$str, $lang){
        $this->lang  = $lang;
        $ret = preg_replace_callback("{\[hl:$lang\]((.|\n)+?)\[/hl:$lang\]}i",array('self', 'replaceCode'), $str);
        return $ret;
    }

    /**
     *
     * @param <string> $replace string to highlight code from
     * @return <string> highlighted code.
     */
    public function replaceCode(&$replace){
        $str = trim($replace[1], "\n ");
        $geshi = new \geshi($str, $this->lang);        
        return $geshi->parse_code();
    }
    
    /**
     *
     * @param <string> $replace string to highlight code from
     * @return <string> highlighted code.
     */
    public function replaceCodeFile(&$replace){
        $file =  trim ($replace[1]);
        if (!file_exists($file)){
            return "File does not exists: $file";
        }
        $str = file_get_contents($file);
        $geshi = new \geshi($str, $this->lang);
        return $geshi->parse_code();
    }
}
