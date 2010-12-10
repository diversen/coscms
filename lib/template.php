<?php

/**
 * simple template class for cos cms
 */
abstract class template {
    
    //static $meta = '';
    static $css = array();
    static $js = array();
    static $inlineJs = array();
    static $inlineCss = array();
    static $meta = '';
    static $title = '';
    static $endHTML = '';
    static $endContent = '';

    public static function setTitle($title){
        self::$title = $title;
    }

    public static function getTitle(){
        return self::$title;
    }
    /**
     * method for setting meta tags.
     * @param   array   array of metatags with name => content
     *
     */
    public static function setMeta($ary){
        foreach($ary as $key => $val){
            self::$meta.="<meta name=\"$key\" value=\"$val\" />\n";
        }
    }

    public static function substituteElement ($element){


    }


    /**
     *
     * @param string    string css_url pointing to the css on your server e.g. /templates/module/good.css
     * @param int       loading order. 0 is loaded first and > 0 is loaded later
     */
    public static function setCss($css_url, $order = null, $options = null){
        if (isset($order)){
            self::$css[$order] = $css_url;
        } else {
            self::$css[] = $css_url;
        }
    }

    /**
     *
     * @return  string  the css as a string
     */
    public static function getCss(){
        $str = "";

        ksort(self::$css);

        //print_r(self::$css);
        $str.= "<style type=\"text/css\" title=\"no-style\" media=\"screen\">\n";
        foreach (self::$css as $key => $val){
            $str.= "\t@import \"$val\";\n";
        }
        $str.= "</style>\n";
        return $str;
    }


    /**
     *
     * @param   string   string pointing to the path of the javascript
     * @param   int      int. the loading order of javascript 0 is first > 0 is
     *                   later.
     */
    public static function setJs($js, $order = null){
        
        if (isset($order)){
            self::$js[$order] = $js;
        } else {
            self::$js[] = $js;
        }
    }

    /**
     *
     * @return  string  the css as a string
     */
    public static function getJs(){
        $str = "";
        ksort(self::$js);
        foreach (self::$js as $key => $val){
            $str.= "<script src=\"$val\" type=\"text/javascript\"></script>\n";
        }
        return $str;
    }
    
    /**
     * Will load the js as file and place it inline in the html document
     * 
     * @param   string   string file path of the javascript
     * @param   int      int. the loading order of javascript 0 is first > 0 is
     *                   later.
     */
    public static function setInlineJs($js, $order = null){
        $str = file_get_contents($js);
        if (isset($order)){
            self::$inlineJs[$order] = $str;
        } else {
            self::$inlineJs[] = $str;
        }
    }

    /**
     *
     * @return  string  the css as a string
     */
    public static function getInlineJs(){
        $str = "";
        ksort(self::$inlineJs);
        foreach (self::$inlineJs as $key => $val){
            $str.= "<script type=\"text/javascript\">$val</script>\n";
        }
        return $str;
    }
/**
     * Will load the css as file and place it inline in the html document
     *
     * @param   string   string file path of the css
     * @param   int      int. the loading order of css 0 is first > 0 is
     *                   later.
     */
    public static function setInlineCss($css, $order = null){
        $str = file_get_contents($css);
        if (isset($order)){
            self::$inlineCss[$order] = $str;
        } else {
            self::$inlineCss[] = $str;
        }
    }

    public static function setParseVarsCss($css, $vars, $order = null){
        $str = get_include_contents($css, $vars);
        //$str = file_get_contents($css);
        if (isset($order)){
            self::$inlineCss[$order] = $str;
        } else {
            self::$inlineCss[] = $str;
        }
    }

    /**
     *
     * @return  string  the css as a string
     */
    public static function getInlineCss(){
        $str = "";
        ksort(self::$inlineCss);
        foreach (self::$inlineCss as $key => $val){
            $str.= "<style type=\"text/css\">$val</style>\n";
        }
        return $str;
    }

    public static function setEndHTML($str){
        self::$endHTML.=$str;
    }

    public static function getEndHTML(){
        return self::$endHTML;
    }


    public static function setEndContent($str){
        self::$endContent.=$str;
    }

    public static function getEndContent(){
        return self::$endContent;
    }
}