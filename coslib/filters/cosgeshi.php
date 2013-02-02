<?php

include_once "geshi/geshi.php";
/**
 *
 * class for highlighting source code. Uses geshi filter. 
 */
class cosgeshi {

    /**
     *
     * @var <string> $language - the language to use.
     */
    private $lang;

    /**
     *
     * @param <array> $article the article row from database to filter.
     * @return <type>
     */
    public function filter($article){
        //foreach($article as $k => $v){
        //    if ($k == 'content'){
                // find all codes of type [hl:lang]
                $reg_ex = "{(\[hl:[a-z\]]+)}i";
                preg_match_all($reg_ex, $article, $match);
                $match  = array_unique($match[1]);
                foreach ($match as $key => $val){
                    $ary = explode(":", $val);
                    preg_match("{([a-z]+)}i", $ary[1], $lang);
                    if (isset($lang[0]) && isset($lang[1])){
                        if ($lang[0] == $lang[1]){
                            if (config::getModuleIni('filter_geshi_use_files')){
                                $article = $this->HighlightCodeFile($article, $lang[0]);
                            } else {
                                $article = $this->highlightCode($article, $lang[0]);
                            }

                            
                        }
                    }
                }
          //  }
        //}
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
        $ret = preg_replace_callback("{\[hl:$lang\]((.|\n)+?)\[/hl:$lang\]}i",array(get_class($this), 'replaceCodeFile'), $str);
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
        $ret = preg_replace_callback("{\[hl:$lang\]((.|\n)+?)\[/hl:$lang\]}i",array(get_class($this), 'replaceCode'), $str);
        return $ret;
    }

    /**
     *
     * @param <string> $replace string to highlight code from
     * @return <string> highlighted code.
     */
    private function replaceCode(&$replace){

          $str = trim($replace[1], "\n ");
          $geshi = new GeSHi($str, $this->lang);
          return $geshi->parse_code();
    }
/**
     *
     * @param <string> $replace string to highlight code from
     * @return <string> highlighted code.
     */
    private function replaceCodeFile(&$replace){
    $file =  trim ($replace[1]);
    if (!file_exists($file)){
        return "File does not exists: $file";
    }
    $str = file_get_contents($file);
    //return highlight_string($str, true);
          $geshi = new GeSHi($str, $this->lang);
          return $geshi->parse_code();
    }
}
