<?php

/**
 * File containing class for parsing template assets. 
 * @package template
 */

include_once "csspacker.php";

/**
 * class used for parsing assets (css and js) and caching them
 * @package template
 */



// Nice find but does not work properly
// include_once "class.JavaScriptPacker.php";

class template_assets extends template {
    
        /**
     * holding css files
     * @var array   $css
     */
    public static $css = array();
    

    /**
     *  holding js files
     * @var array $js
     */
    public static $js = array();
    
    /**
     * holding head js
     * @var array $jsHead
     */
    public static $jsHead = array ();

    /**
     * holding rel elements
     * @var array $rel
     */
    public static $rel = array ();
    
    /**
     * holding inline js strings
     * @var array $inlineJs
     */
    public static $inlineJs = array();

    /**
     * holding inline css strings
     * @var array $inlineCss 
     */
    public static $inlineCss = array();
    
        
    /**
     * name of dir where we cache assets
     * @var string $cacheDir 
     */
    public static $cacheDir = 'cached_assets';

    /**
     * name of cache dir web where we cache assets
     * @var string $cacheDirWeb  
     * 
     */
    public static $cacheDirWeb = '';
    
    /**
     * array holding css that should not be cached or compressed
     * @var array $noCacheCss
     */
    public static $noCacheCss = array ();
    
        
     /**
     * method for caching a asset (js or css)
     * @param type $css
     * @param type $order
     * @param type $type 
     */
    public static function cacheAsset ($asset, $order, $type, $options = array ()) {
        if (config::isCli()) {
            return;
        }
        static $cacheChecked = false;
        
        if (!$cacheChecked) {
            self::$cacheDirWeb = config::getWebFilesPath(self::$cacheDir);
            self::$cacheDir = config::getFullFilesPath() . '/' . self::$cacheDir;
            if (!file_exists(self::$cacheDir)) {
                mkdir(self::$cacheDir);
            }  
            $cacheChecked = true;
        }
        
        $md5 = md5($asset);        
        $cached_asset = config::getFullFilesPath() . "/cached_assets/$md5.$type";
        $cache_dir = config::getWebFilesPath('/cached_assets');
        if (file_exists($cached_asset && !config::getMainIni('cached_assets_reload'))) {
            
            if ($type == 'css') {
                self::setCss("$cache_dir/$md5.$type", $order);
            }
            
            if ($type == 'js') {
                self::setJs("$cache_dir/$md5.$type", $order);
            }          
        } else {
            
            $str = file_get_contents($asset); 
            if (isset($options['search'])) {
                $str = self::searchReplace($str, $options);
            }
            
            file_put_contents($cached_asset, $str, LOCK_EX);

            if ($type == 'css') {
                self::setCss("$cache_dir/$md5.$type", $order);
            }
            
            if ($type == 'js') {
                self::setJs("$cache_dir/$md5.$type", $order);
            } 
        }
    }
    
        /**
     * gets rel assets. assure that we only get every asset once.
     * @return string $assets 
     */
    public static function getRelAssets () {
        $str = '';
        static $set = array ();
        foreach (self::$rel as $val) {
            if (isset($set[$val])) { 
                continue;
            } else {
                $set[$val] = 1;
                $str.=$val;
            }
        }
        return $str;
    }
    
    /**
     * method for adding css or js in top of document. 
     * @param string $type 'css' or 'js'
     * @param string $link 'src' link of the asset 
     */
    public static function setRelAsset ($type, $link) {
        if ($type == 'css') {
            self::$rel[] = "<link rel=\"stylesheet\" type=\"text/css\" href=\"$link\" />\n";
        }
        if ($type == 'js') {
            self::$rel[] = "<script type=\"text/javascript\" src=\"$link\"></script>\n";
        }
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
         if (isset($order)){
            if (isset(self::$css[$order])) {
                self::setNoCacheCss($css_url, $order + 1, $options);
            } else {
                self::$noCacheCss[$order] = $css_url;
            }
        } else {
            $next = self::getNextCount();
            if (isset(self::$css[$next])) {
                self::setCss($css_url, $order + 1, $options);
            }
            self::$noCacheCss[$next] = $css_url;
        }
    }
    
    /**
     * method for setting css files to be used on page
     *
     * @param string $css_url pointing to the css on your server e.g. /templates/module/good.css
     * @param int  $order loading order. 0 is loaded first and > 0 is loaded later
     * @param array $options
     */
    public static function setCss($css_url, $order = null, $options = null){
        if (isset($options['no_cache'])) {
            self::setNoCacheCss($css_url, $order, $options);
            return;
        }
        
        if (isset($order)){
            if (isset(self::$css[$order])) {
                self::setCss($css_url, $order + 1, $options);
            } else {
                self::$css[$order] = $css_url;
            }
        } else {
            $next = self::getNextCount();
            if (isset(self::$css[$next])) {
                self::setCss($css_url, $order + 1, $options);
            }
            self::$css[$next] = $css_url;
        }
    }
    
    /**
     * var for keep count of css been set. 
     * why: because if we set a css with a order of e.g. 20000
     * then the next css without a order  will be set to 20001
     * Therefor: we use an internal counter of all css were it does not
     * matter what order they are loaded in
     * @var int $count
     */
    private static $count = 1;
    
    /**
     * 
     * @return int $count next available css placeholder
     */
    private static function getNextCount() {
        return self::$count++;
    }
    
    /**
     * method for getting css for displaing in user template
     * @return  string  the css as a string
     */
    public static function getCss(){
        
        $str = "";
        ksort(self::$css);
        
        foreach (self::$css as $key => $val){
            $str.= "<link rel=\"stylesheet\" type=\"text/css\" href=\"$val\" />\n";
            unset(self::$css[$key]);
        }
        
        return $str;
    }
    
    public static function getCssLinkRel ($css) {
        return "<link rel=\"stylesheet\" type=\"text/css\" href=\"$css\" />\n";
    } 
    
    /**
     * gets all css as a single string
     * @return string $css
     */
    public static function getCssAsSingleStr () {
        $str = '';
        foreach (self::$css as $key => $val){
            if (!preg_match('/^(http|https):/', $val) AND !preg_match('#^(//)#', $val) ) {
                unset(self::$css[$key]);
                    
                $file = _COS_HTDOCS . "$val";
                    
                $str.= "\n/* Caching $file*/\n";
                if (!config::getMainIni('cache_disable')) {
                    $str.= file::getCachedFile($file) ."\n\n\n";
                } else {
                    $str.= file_get_contents($file);
                }
            } 
        }
        
        if (config::getMainIni('cached_assets_pack')) {
            $str = csspacker::packcss($str);  
        }
        return $str;
    }
    
    /**
     * puts all css into one file, place this file in `cached_assets`
     * sets the new css path
     */
    public static function setCssAsSingleFile () {
        $str = self::getCssAsSingleStr ();
        if (config::getMainIni('cached_assets_minify')) {
            $cssp = new csspacker();
            $str =$cssp->packcss($str);
        }
            
        $md5 = md5($str);
        $domain = config::getDomain();
            
        $web_path = "/files/$domain/cached_assets"; 
        $file = "/css_all-$md5.css";
           
        $full_path = _COS_HTDOCS . "/$web_path";
        $full_file_path = $full_path . $file;
            
        // create file if it does not exist
        if (!file_exists($full_file_path)) {                  
            file_put_contents($full_file_path, $str, LOCK_EX);
        }            
        self::setCss($web_path . "$file"); 
    }
    
    /**
     * takes all CSS and puts in one file. It works the same way as 
     * template::getCss. You can sepcify this in your ini settings by using
     * cached_assets_compress = 1
     * Usefull if you have many css files. 
     * @return string $str
     */
    public static function getCompressedCss(){
        
        ksort(self::$css);
        if (config::getMainIni('cached_assets_compress')) {
            self::setCssAsSingleFile();  
        } 
        
        ksort(self::$noCacheCss);
        foreach (self::$noCacheCss as $val) {
            self::setCss($val);
        }
        return self::getCss();
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
        
        if (isset($options['search'])){
            $js = str_replace($options['search'], $options['replace'], $js);
        }
        
        if (isset($order)){
            if (isset(self::$inlineJs[$order])) {
                self::setStringJs($js, $order +1);
            }
            self::$inlineJs[$order] = $js;
            
        } else {
            self::$inlineJs[] = $js;
        }
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
        if (isset($options['head'])) {
            self::$jsHead[] = $js_url;
            return;
        }
        
        if (isset($order)){
            if (isset(self::$js[$order])) {
                self::setJs($js_url, $order + 1, $options);
            } else {
                self::$js[$order] = $js_url;
            }
        } else {
            self::$js[] = $js_url;
        }
    }
    
    public static function setJsHead ($js_url, $order = null) {
        self::setJs($js_url, $order, array ('head' => true));
    }
    
    /**
     * method for getting css files used in user templates
     * @return  string  the css as a string
     */
    public static function getJs(){
        $str = "";
        ksort(self::$js);

        foreach (self::$js as $val){
            $str.= "<script src=\"$val\" type=\"text/javascript\"></script>\n";
        }
        return $str;
    }
    
    /**
     * gets all css as a single string
     * @return string $css
     */
    public static function getJsAsSingleStr () {
        $str = '';
        foreach (self::$js as $key => $val){
            if (!preg_match('#^(http|https)://#', $val) AND !preg_match('#^(//)#', $val) ) {
                unset(self::$js[$key]);
                $str.= file::getCachedFile(_COS_HTDOCS . "/$val") ."\n\n\n";
            }
        }
            
        return $str;
    }
    
    /**
     * sets js as a single file in js-all file 
     */
    public static function setJsAsSingleFile () {
        $str = self::getJsAsSingleStr();
        if (config::getMainIni('cached_assets_minify')) {
            $str = JSMin::minify($str);
        }
        
        $md5 = md5($str);
        $domain = config::getDomain();
            
        $web_path = "/files/$domain/cached_assets"; 
        $file = "/js_all-$md5.js";
           
        $full_path = _COS_HTDOCS . "/$web_path";
        $full_file_path = $full_path . $file;
            
        // create file if it does not exist
        if (!file_exists($full_file_path)) {
            file_put_contents($full_file_path, $str, LOCK_EX);
        }
        self::setJs($web_path . $file);
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
        
        $str = "";
        ksort(self::$js);        
        if (config::getMainIni('cached_assets_compress')) {
            self::setJsAsSingleFile();
            
        }
                 
        return self::getJs();    
    }
    
    /**
     * gets js for head as a string
     */
    public static function getJsHead(){
        $str = "";
        ksort(self::$jsHead);
        foreach (self::$jsHead as $val){
            $str.= "<script src=\"$val\" type=\"text/javascript\"></script>\n";
        }
        return $str;
    }
    
    
    /**
     * search and replace in a asset, e.g. js or css
     * @param string $str asset
     * @param type $options array ('search' => 'SEARCH STRING', 'replace' => 'REPLACE STRING')
     * @return string $str asset
     */
    public static function searchReplace($str, $options) {
        $str = str_replace($options['search'], $options['replace'], $str);
        return $str;
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
        if (config::getMainIni('cached_assets') && !isset($options['no_cache'])) {
            self::cacheAsset ($js, $order, 'js', $options);
            return;
        }
        
        $str = file_get_contents($js);
        if (isset($options['search'])){
            $str = self::searchReplace($str, $options);
        }
        
        if (isset($order)){
            if (isset(self::$inlineJs[$order])) {
                self::$inlineJs[] = $str;
            } else {
                self::$inlineJs[$order] = $str;
            }
        } else {
            self::$inlineJs[] = $str;
        }
    }

    /**
     * method for getting all inline js as a string
     * @return  string  $str the js as a string
     */
    public static function getInlineJs(){
        $str = "";
        ksort(self::$inlineJs);
        foreach (self::$inlineJs as $val){            
            $str.= "<script type=\"text/javascript\">$val</script>\n";
        }
        return $str;
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

        if (config::getMainIni('cached_assets') && !isset($options['no_cache'])) {
            self::cacheAsset ($css, $order, 'css');
            return;
        }
          
        $str = file_get_contents($css);
        /*
        if (method_exists('mainTemplate', 'assetsReplace')) {
            $str = mainTemplate::assetsReplace($str);
        }*/
                
        if (isset($order)){
            self::$inlineCss[$order] = $str;
        } else {
            self::$inlineCss[] = $str;
        }
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
        
        $module_css = _COS_MOD_PATH . "/$module/$css";
        
        $template_name = layout::getTemplateName();
        $template_override =  "/templates/$template_name/$module$css";
        
        if (file_exists(_COS_HTDOCS . $template_override) ) {
            self::setCss($template_override);
            return;
        }
        
        self::setInlineCss($module_css);
    }
    
        /**
     * method for parsing a css file and substituing css var with
     * php defined values
     * @param string $css
     * @param array  $vars
     * @param int    $order
     */
    public static function setParseVarsCss($css, $vars, $order = null){
        $str = template::getFileIncludeContents($css, $vars);
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
            unset(self::$inlineCss[$key]);
        }
        return $str;
    }
    
    
    /**
     * gets css style for a random gallery image 
     * @return string $str
     */
    public static function getGalleryBgCssRandom ($interval = 7) {
        if (!moduleloader::moduleExists('gallery')) {
            return '';
        }
        moduleloader::includeModule('gallery');
        $g = new gallery();
        $random_img = $g->getShiftingImageURL();
        if (empty($random_img)) {
            return '';
        }
        
        $css = <<<EOF
<style>
body {
    background: url("{$random_img}") no-repeat fixed center center / cover rgba(0, 0, 0, 0);
    overflow: scroll;
    
}
</style>
EOF;
    return $css;

    }
        
    /**
     * load assets specified in ini settings from template
     */
    public static function loadTemplateIniAssets () {

        $js = config::getModuleIni('template_rel_js');
        if ($js) {
            foreach ($js as $val) {
                self::setRelAsset('js', $val);
            }   
        }
        
        $css = config::getModuleIni('template_rel_css');
        if ($css) {
            foreach ($css as $val) {
                self::setRelAsset('css', $val);
            }
        }
        
        $js = config::getModuleIni('template_js');
        if ($js) {
            foreach ($js as $val) {
                self::setJs($val);
            }
        }
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

        $css = config::getMainIni('css');
        if (!$css) {
            // no css set use default/default.css
            //self::setCss("/templates/$template/default/default.css?version=$version", $order);
            self::setCss("/templates/$template/default/default.css", $order);
            return;
        }
        
        $base_path = "/templates/$template/$css";
        $css_path = _COS_HTDOCS . "/$base_path/$css.css";
        $css_web_path = $base_path . "/$css.css";
        if (file_exists($css_path)) {

            //self::setCss("$css_web_path?version=$version", $order);
            self::setCss("$css_web_path", $order);
        } else {
            //self::setCss("/templates/$template/default/default.css?version=$version", $order);
            self::setCss("/templates/$template/default/default.css", $order);
            return;
        }

    }
    
    /**
     * sets template css from template css ini files
     * @param string $template
     * @param string $css
     */
    public static function setTemplateCssIni ($template, $css) {
        $ini_file = _COS_HTDOCS . "/templates/$template/$css/$css.ini";
        if (file_exists($ini_file)) {
            
            $ary = config::getIniFileArray($ini_file, true);
            config::$vars['coscms_main']['module'] = 
                    array_merge_recursive(config::$vars['coscms_main']['module'], $ary);
        }        
    }
}
