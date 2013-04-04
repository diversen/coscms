<?php

/**
 * File containing template class. 
 * 
 * @package template
 */

/**
 * simple template class for cos cms
 * which will be used for display the page
 * 
 * @package template
 */
class template {

    /**
     * holding title of page being parsed
     * @var string $title
     */
    public static $title = '';

    /**
     * holding end html string
     * @var string $endHTML
     */
    public static $endHTML = '';

    /**
     * holding start html string
     * @var string $startHTML
     */
    public static $startHTML = '';
    
    /**
     * holding end of content string
     * @var string $endContent  
     */
    public static $endContent = '';
    
    /**
     * holding templateName
     * @var string  $templateName 
     */
    public static $templateName = null;


    

    
    /**
     * method for setting title of page
     * @param string $title the title of the document
     */
    public static function setTitle($title){
        self::$title = html::specialEncode($title);
    }

    /**
     * method for getting title of page
     * @return string   $title title of document
     */
    public static function getTitle(){
        return self::$title;
    }

    /**
     * method for setting meta tags. The tags will be special encoded
     * @param   array   $ary of metatags e.g. 
     *                         <code>array('description' => 'content of description meta tags')</code>
     *                         or string which will be set direct. E.g. 
     *                         
     */
    public static function setMeta($ary){
        template_meta::setMeta($ary);
    }
    
    /**
     * sets meta tags directly. 
     * @param string $str e.g. <code><meta name="description" content="test" /></code>
     */
    public static function setMetaAsStr ($str) {
        template_meta::setMetaAsStr($str);
    }
    
    /**
     * check if template common.inc exists
     * @param string $template
     * @return boolean $res true if exists else false
     */
    public static function templateCommonExists ($template) {
        if (file_exists( _COS_HTDOCS . "/templates/$template/common.inc")) {
            return true;
        }
        return false;
    }
    
    /**
     * gets rel assets. assure that we only get every asset once.
     * @return string $assets 
     */
    public static function getRelAssets () {
        return template_assets::getRelAssets();
    }
    
    /**
     * method for adding css or js in top of document. 
     * @param string $type 'css' or 'js'
     * @param string $link 'src' link of the asset 
     */
    public static function setRelAsset ($type, $link) {
        template_assets::setRelAsset($type, $link);
    }
    
    /**
     * method for getting html for front page. If no logo has been 
     * uploaded. You will get logo as html
     * @param type $options options to give to html::createHrefImage
     * @return string $str the html compsoing the logo or main title
     */
    public static function getLogoHTML ($options = array()) {
        return template_logo::getLogoHTML($options);
    }

    /**
     * method for getting the meta tags as a string
     * You can specifiy meta keywords and description global in config.ini
     * by using the settings, meta_desc and meta_keywords.
     *  
     * @return string $str the meta tags as a string. This can be used
     *                     in your mainTemplate
     */
    public static function getMeta (){        
        return template_meta::getMeta();
    }
    

    
    /**
     * set css that should not be cached. We have downloaded source of a 
     * js script and we will compress anything into a single file. In order
     * to avoid path issues with images in css we can use this in order
     * to just link to the CSS
     * @param string $css_url
     * @param string $order
     * @param array $options
     */
    public static function setNoCacheCss ($css_url, $order = null, $options = array ()) {
        template_assets::setNoCacheCss($css_url, $order, $options);
    }

    /**
     * method for setting css files to be used on page
     *
     * @param string $css_url pointing to the css on your server e.g. /templates/module/good.css
     * @param int  $order loading order. 0 is loaded first and > 0 is loaded later
     * @param array $options
     */
    public static function setCss($css_url, $order = null, $options = null){
        template_assets::setCss($css_url, $order, $options);
    }


    /**
     * method for getting css for displaing in user template
     * @return  string  the css as a string
     */
    public static function getCss(){
        return template_assets::getCss();
    }
    

    /**
     * takes all CSS and puts in one file. It works the same way as 
     * template::getCss. You can sepcify this in your ini settings by using
     * cached_assets_compress = 1
     * Usefull if you have many css files. 
     * @return string $str
     */
    public static function getCompressedCss(){
        return template_assets::getCompressedCss();
    }
    
    /**
     * Will load the js as file and place and add it to array which can
     * be parsed in user templates.
     * 
     * @param   string   $js file path of the javascript
     * @param   int $order the loading order of javascript 0 is first > 0 is
     *                   later.
     * @param array $options
     */
    public static function setStringJs($js, $order = null, $options = array()){
        template_assets::setStringJs($js, $order, $options);
    }


    /**
     * method for setting js files to be used by user templates. This is
     * used with javascripts which are placed in web space.
     * @param   string   $js_url pointing to the path of the javascript
     * @param   int      $order the loading order of javascript 0 is first > 0 is
     *                   later.
     * @param   array    $options defaults: array ('head' => false)
     */
    public static function setJs($js_url, $order = null, $options = null){
        template_assets::setJs($js_url, $order, $options);
    }

    /**
     * method for getting css files used in user templates
     * @return  string  the css as a string
     */
    public static function getJs(){
        return template_assets::getJs();
    }
      
    /**
     * takes all JS and puts them in one file. It works the same way as 
     * template::getJs (except you only get one file) 
     * You can sepcify this in your ini settings by using
     * cached_assets_compress = 1
     * Usefull if you have many JS files. 
     * @return string $str
     */
    public static function getCompressedJs(){
        return template_assets::getCompressedJs(); 
    }
    
    /**
     * gets js for head as a string
     */
    public static function getJsHead(){
        return template_assets::getJsHead();
    }
    
    /**
     * returns favicon html
     * @return string $html 
     */
    public static function getFaviconHTML () {
        return template_favicon::getFaviconHTML();
    }
    
    /**
     * Will load the js as file and place and add it to array which can
     * be parsed in user templates. This is used with js files that exists
     * outside webspace, e.g. in modules
     * 
     * @param   string   $js file path of the javascript
     * @param   int      $order the loading order of javascript 0 is first > 0 is
     *                   later.
     * @param array $options
     */
    public static function setInlineJs($js, $order = null, $options = array()){
        template_assets::setInlineJs($js, $order, $options);
    }

    /**
     * method for getting all inline js as a string
     * @return  string  $str the js as a string
     */
    public static function getInlineJs(){
        return template_assets::getInlineJs();
    }

    /**
     * method for setting user css used inline in user templates.
     *
     * @param   string   $css string file path of the css
     * @param   int      $order the loading order of css 0 is first > 0 is
     *                   later.
     * @param array $options
     */
    public static function setInlineCss($css, $order = null, $options = array()){
        template_assets::setInlineCss($css, $order, $options);
    }
    
        /**
     * method for setting user css used inline in user templates.
     *
     * @param   string   $css string file path of the css
     * @param   int      $order the loading order of css 0 is first > 0 is
     *                   later.
     * @param array $options
     */
    public static function setModuleInlineCss($module, $css, $order = null, $options = array()){
        template_assets::setModuleInlineCss($module, $css, $order, $options);
    }

    
    /**
     * method for caching a asset (js or css)
     * @param type $css
     * @param type $order
     * @param type $type 
     */
    public static function cacheAsset ($css, $order, $type) {
        template_assets::cacheAsset($css, $order, $type);
    }
    
    /**
     * returns a included files content with vars substitued
     * @param string $filename
     * @param array $vars
     * @return mixed $res false on failure and string on success
     */
    
    public static function getFileIncludeContents($filename, $vars = null) {
        if (is_file($filename)) {
            ob_start();
            include $filename;
            $contents = ob_get_contents();
            ob_end_clean();
            return $contents;
        }
        return false;
    }
    
    /**
     * method for parsing a css file and substituing css var with
     * php defined values
     * @param string $css
     * @param array  $vars
     * @param int    $order
     */
    public static function setParseVarsCss($css, $vars, $order = null){
        template_assets::setParseVarsCss($css, $vars, $order);
    }

    /**
     * method for getting css used in inline in user templates
     * @return  string  the css as a string
     */
    public static function getInlineCss(){
        return template_assets::getInlineCss();
    }

    /**
     * method for adding string to end of html
     * @param   string  string to add to end of html
     */
    public static function setStartHTML($str){
        self::$startHTML.=$str;
    }

    /**
     * method for getting end of html
     * @return  string  end of html
     */
    public static function geStartHTML(){
        return self::$startHTML;
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
    
    /**
     * inits a template
     * set template name and load init settings
     * @param string $template name of the template to init. 
     */
    public static function init ($template) {       
        self::$templateName = $template;
        if (!isset(config::$vars['template'])) {
            config::$vars['template'] = array();
        }       
        moduleloader::setModuleIniSettings($template, 'template');
        $css = config::getMainIni('css');
        if ($css) {
            template_assets::setTemplateCssIni($template, $css);
        }
        

    }
    
    public static function loadTemplateIniAssets () {
        template_assets::loadTemplateIniAssets();
    }
    
    
    /**
     * checks if a css style is registered. If not
     * we use common.css in template folder.
     * 
     * @param string $template
     * @param int $order
     * @param string $version
     */
    public static function setTemplateCss ($template = '', $order = 0, $version = 0){
        template_assets::setTemplateCss($template, $order, $version);

    }

}
