<?php

/**
 * @package coslib
 */

/**
 * simple template class for cos cms
 * @package coslib
 */
abstract class template {
    
    /**
     * @var array   holding css files
     */
    static $css = array();

    /**
     * @var array   holding js files
     */
    static $js = array();

    /**
     * @var array   holding inline js strings
     */
    static $inlineJs = array();

    /**
     * @var array   holding inline css strings
     */
    static $inlineCss = array();

    /**
     * @var string  holding meta tags
     */
    static $meta = array();

    /**
     * @var string  holding title of page being parsed
     */
    static $title = '';

    /**
     * @var string   holding last html strings
     */
    static $endHTML = '';

    /**
     * @var string   holding end of content string
     */
    static $endContent = '';
    
    /**
     *
     * @var string  $templateName 
     */
    static $templateName = null;

    /**
     * method for setting title of page
     * @param string $title
     */
    public static function setTitle($title){
        self::$title = $title;
    }

    /**
     * method for getting title of page
     * @return <type>
     */
    public static function getTitle(){
        return self::$title;
    }

    /**
     * method for setting meta tags.
     * @param   array   array of metatags with name => content
     */
    public static function setMeta($ary){
        foreach($ary as $key => $val){
            if (isset(self::$meta[$key])){
                continue;
            }
            self::$meta[$key] = html::specialEncode($val);
        }
    }

    public static function getMeta (){
        
        $str = '';

        if (!isset(self::$meta['keywords'])) {
            $str = '';
            $str = get_main_ini('meta_keywords');
            $str = trim($str);
            if (!empty($str)) {
                self::$meta['keywords'] = $str;
            }
        }

        if (empty(self::$meta['description'])) {
            $str = '';
            $str = get_main_ini('meta_desc');
            $str = trim($str);
            if (!empty($str)) {
                self::$meta['description'] = $str;
            }
        }

        $str = '';
        foreach (self::$meta as $key => $val) {
            $str.= "<meta name=\"$key\" content=\"$val\" />\n";
        }

        return $str;

    }

    /**
     * method for setting css files to be used on page
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
     * method for getting css for displaing in user template
     * @return  string  the css as a string
     */
    public static function getCss(){
        $str = "";
        ksort(self::$css);
        //$str.= "<style type=\"text/css\" title=\"no-style\" media=\"screen\">\n";

        foreach (self::$css as $key => $val){
            //$str.= "\t@import \"$val\";\n";
            $str.= "<link rel=\"stylesheet\" type=\"text/css\" href=\"$val\" />\n";

            //$str.= "<script src=\"$val\" type=\"text/css\"></script>\n";
        }
        //$str.= "</style>\n";
        return $str;
    }


    /**
     * method for setting css files to be used by user templates
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
     * method for getting css files used in user templates
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
     * Will load the js as file and place and add it to array which can
     * be parsed in user templates.
     * 
     * @param   string   string file path of the javascript
     * @param   int      int. the loading order of javascript 0 is first > 0 is
     *                   later.
     */
    public static function setInlineJs($js, $order = null, $search = null, $replace = null){
        $str = file_get_contents($js);
        if ($search){
            $str = str_replace($search, $replace, $str);
        }
        if (isset($order)){
            self::$inlineJs[$order] = $str;
        } else {
            self::$inlineJs[] = $str;
        }
    }

    /**
     * method for getting all inline js as a string
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
     * method for setting user css used inline in user templates.
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

    /**
     * method for parsing a css file and substituing css var with
     * php defined values
     * @param string $css
     * @param array  $vars
     * @param int    $order
     */
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
     * method for getting css used in inline in user templates
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

    /**
     * method for adding string to end of html
     * @param   string  string to add to end of html
     */
    public static function setEndHTML($str){
        self::$endHTML.=$str;
    }

    /**
     * method for getting end of html
     * @return  string  end of html
     */
    public static function getEndHTML(){
        return self::$endHTML;
    }

    /**
     * method for setting end html
     * @param string    end content
     */
    public static function setEndContent($str){
        self::$endContent.=$str;
    }

    /**
     * method for getting end of html
     * @return <type>
     */
    public static function getEndContent(){
        return self::$endContent;
    }
    
    public static function init ($template) {
        self::$templateName = $template;   
        self::loadIniSettings();
    }
    
    public static function loadIniSettings () {
        $ini_file = _COS_PATH . "/htdocs/templates/" . 
                    self::$templateName . '/' . 
                    self::$templateName . '.ini';
        if (file_exists($ini_file)) {    
            register::$vars['template'] = parse_ini_file($ini_file, true);
        }
    }
    
    public static function getIniSetting ($var) {
        if (isset(register::$vars['template'][$var])){
            return register::$vars['template'][$var];
        }
        
        return null;
    }

    /**
     * checks if a css style is registered. If not
     * we use common.css in template folder.
     * 
     * @param string $template
     */
    public static function setTemplateCss ($template = '', $version = 0){
        if (empty($template)) {
            $template = self::$templateName;
            if (empty($template)) {
                die ('No template name is set');
            }
        }
        
        if (!empty(register::$vars['coscms_main']['css'])){
            $css = register::$vars['coscms_main']['css'];
            $css_dir =  _COS_PATH . "/htdocs/templates/$template/$css";
            if (is_dir($css_dir)){
                self::setTemplateCssDir ($template, $css);
                return;
            }
            template::setCss("/templates/$template/$css?version=$version");
        } else {
            template::setCss("/templates/$template/default/default.css?version=$version");
        }
    }

    public static function setTemplateCssDir ($template, $css){
        self::setCss("/templates/$template/$css/$css.css");

        // load js connected to css if any
        $js = "/templates/$template/$css/$css.js";
        if (file_exists(_COS_PATH . "/htdocs/$js")){
            self::setJs($js, 1000);
        } 
    }
}

class templateView {

    static $viewFolder = 'views';
    // {{{ include_view
    /**
     * function for including a view file.
     * Maps to module (e.g. 'tags' and 'view file' e.g. 'add')
     * we presume that views are placed in modules views folder
     * e.g. tags/views And we presume that views always has a .inc
     * postfix
     *
     * @param string $module
     * @param string $file
     */
    static function includeModuleView ($module, $view, $vars = null, $return = null){
        $filename = _COS_PATH . "/modules/$module/" . self::$viewFolder . "/$view.inc";

        if (is_file($filename)) {
            ob_start();
            include $filename;
            $contents = ob_get_contents();
            ob_end_clean();
            if ($return) {
                return $contents;
            } else {
                echo $contents;
            }
        } else {
            echo "View: $filename not found";
            return false;
        }
    }
}