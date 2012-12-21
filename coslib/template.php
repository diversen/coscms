<?php

/**
 * File containing template class. 
 * 
 * @package template
 */

/**
 * simple template class for cos cms
 * abstract because it will always be extended by mainTemplate
 * which will be used for display the page
 * 
 * @package template
 */
abstract class template {
    
    /**
     * holding css files
     * @var array   $css
     */
    static $css = array();
    

    /**
     *  holding js files
     * @var array $js
     */
    static $js = array();
    
    /**
     * holding head js
     * @var array $jsHead
     */
    static $jsHead = array ();

    /**
     * holding rel elements
     * @var array $rel
     */
    static $rel = array ();
    
    /**
     * holding inline js strings
     * @var array $inlineJs
     */
    static $inlineJs = array();

    /**
     * holding inline css strings
     * @var array $inlineCss 
     */
    static $inlineCss = array();

    /**
     * holding meta tags
     * @var array $meta  
     */
    static $meta = array();

    /**
     * holding title of page being parsed
     * @var string $title
     */
    static $title = '';

    /**
     * holding end html string
     * @var string $endHTML
     */
    static $endHTML = '';

    /**
     * holding start html string
     * @var string $startHTML
     */
    static $startHTML = '';
    
    /**
     * holding end of content string
     * @var string $endContent  
     */
    static $endContent = '';
    
    /**
     * holding templateName
     * @var string  $templateName 
     */
    static $templateName = null;
    
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
     *                  array('description' => 'content of description meta tags')
     */
    public static function setMeta($ary){
        foreach($ary as $key => $val){
            if (isset(self::$meta[$key])){
                continue;
            }
            self::$meta[$key] = html::specialEncode($val);
        }
    }
    
    public static function templateCommonExists ($template) {
        if (file_exists( _COS_HTDOCS . "/templates/$template/common.inc")) {
            return true;
        }
        return false;
    }
    
    /**
     * gets rel assets. assure that we only get every asset once.
     * @staticvar array $set
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
     * method for getting html for front page. If no logo has been 
     * uploaded. You will get logo as html
     * @param type $options options to give to html::createHrefImage
     * @return string $str the html compsoing the logo or main title
     */
    public static function getLogoHTML ($options = array()) {
        $logo = config::getMainIni('logo');
        if (!$logo){
            $logo_method = config::getMainIni('logo_method');
            if (!$logo_method) {
                $title = $_SERVER['HTTP_HOST'];
                $link = html::createLink('/', $title);
                return $str = "<div id=\"logo_title\">$link</div>";
            } else {
                include_module ($logo_method);
                //if (function_exists($logo_method)) die ('exists');
                //moduleLoader::includeModuleFromStaticCall($logo_method);
                $str =  $logo_method::logo();
                
                
                return $str = "<div id=\"logo_title\">$str</div>";
            }
                
        } else {
            $file ="/logo/" . config::$vars['coscms_main']['logo'];
            $src = config::getWebFilesPath($file);
            if (!isset($options['alt'])){           
                $options['alt'] = $_SERVER['HTTP_HOST'];
            }
            $href = html::createHrefImage('/', $src, $options);
            $str = '<div id="logo_img">' . $href . '</div>' . "\n"; 
            //die($str);
            return $str;
        }
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
        $str = '';

        if (!isset(self::$meta['keywords'])) {
            $str = '';
            $str = config::getMainIni('meta_keywords');
            $str = trim($str);
            if (!empty($str)) {
                self::$meta['keywords'] = $str;
            }
        }
        
        // master domains are allow visible for robots
        $master = config::getMainIni('master');
        if (!isset(self::$meta['robots']) && $master) {
            
            $str = '';
            $str = config::getMainIni('meta_robots');
            $str = trim($str);
            if (!empty($str)) {
                self::$meta['robots'] = $str;
            }
        }

        if (empty(self::$meta['description'])) {
            $str = '';
            $str = config::getMainIni('meta_desc');
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
     * @param string $css_url pointing to the css on your server e.g. /templates/module/good.css
     * @param int  $order loading order. 0 is loaded first and > 0 is loaded later
     * @param array $options
     */
    public static function setCss($css_url, $order = null, $options = null){
        if (isset($order)){
            if (isset(self::$css[$order])) {
                self::setCss($css_url, $order + 1, $options);
            } else {
                self::$css[$order] = $css_url;
            }
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
        
        foreach (self::$css as $val){
            $str.= "<link rel=\"stylesheet\" type=\"text/css\" href=\"$val\" />\n";
        }
        
        return $str;
    }
    

    /**
     * takes all CSS and puts in one file. It works the same way as 
     * template::getCss. You can sepcify this in your ini settings by using
     * cached_assets_compress = 1
     * Usefull if you have many css files. 
     * @return string $str
     */
    public static function getCompressedCss(){
        
        $str = "";
        ksort(self::$css);
        
        if (config::getMainIni('cached_assets_compress')) {
            foreach (self::$css as $key => $val){
                if (!strstr($val, "http://") ) {
                    unset(self::$css[$key]);
                    $str.= file::getCachedFile(_COS_HTDOCS . "/$val") ."\n\n\n";
                }
            }
            
            $md5 = md5($str);
            $domain = config::getDomain();
            
            $web_path = "/files/$domain/cached_assets"; 
            $file = "/css_all-$md5.css";
           
            $full_path = _COS_HTDOCS . "/$web_path";
            $full_file_path = $full_path . $file;
            
            // create file if it does not exist
            if (!file_exists($full_file_path)) {
                $to_remove = glob($full_path . "/css_all-*");
                file::remove($to_remove);
                file_put_contents($full_file_path, $str);
            }
            
            self::setCss($web_path . "$file");   
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
        
        /*
        if (config::getMainIni('cached_assets') && !isset($options['no_cache'])) {
            self::cacheAsset ($js, $order, 'js');
            return;
        }*/
        
        //$str = file_get_contents($js);
        if (isset($options['search'])){
            $js = str_replace($options['search'], $options['replace'], $str);
        }
        
        //$js = "<script>$js</script>\n";
        //var myvar = <?php echo json_encode($myVarValue); 
        if (isset($order)){
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
     * @param   array    $options
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
            foreach (self::$js as $key => $val){
                if (!strstr($val, "http://") ) {
                    unset(self::$js[$key]);
                    $str.= file::getCachedFile(_COS_HTDOCS . "/$val") ."\n\n\n";
                }
            }
            
            $md5 = md5($str);
            $domain = config::getDomain();
            
            $web_path = "/files/$domain/cached_assets"; 
            $file = "/js_all-$md5.js";
           
            $full_path = _COS_HTDOCS . "/$web_path";
            $full_file_path = $full_path . $file;
            
            // create file if it does not exist
            if (!file_exists($full_file_path)) {
                $to_remove = glob($full_path . "/js_all-*");
                file::remove($to_remove);
                file_put_contents($full_file_path, $str);
            }
            self::setJs($web_path . $file);
        }
        
          
        return self::getJs();
    
    }
    
    /**
     * gets js for head as a string
     */
    public static function getJsHead(){
        $str = "";
        ksort(self::$jsHead);
        foreach (self::$jsHead as $key => $val){
            $str.= "<script src=\"$val\" type=\"text/javascript\"></script>\n";
        }
        return $str;
    }
    
    /**
     * returns favicon html
     * @return string $html 
     */
    public static function getFaviconHTML () {
        $favicon = config::getMainIni('favicon');
        $domain = config::getDomain();
        $rel_path = "/files/$domain/favicon/$favicon";
        $full_path = _COS_HTDOCS . "/$rel_path"; 
        if (!is_file($full_path)) {
            $rel_path = '/favicon.ico';
        }
        
        $str = "<link rel=\"shortcut icon\" href=\"$rel_path\" type=\"image/x-icon\" />\n";
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
            self::cacheAsset ($js, $order, 'js');
            return;
        }
        
        $str = file_get_contents($js);
        if (isset($options['search'])){
            $str = str_replace($options['search'], $options['replace'], $str);
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
     * method for caching a asset (js or css)
     * @param type $css
     * @param type $order
     * @param type $type 
     */
    private static function cacheAsset ($css, $order, $type) {
        static $cacheChecked = false;
        
        if (!$cacheChecked) {
            self::$cacheDirWeb = config::getWebFilesPath(self::$cacheDir);
            self::$cacheDir = config::getFullFilesPath() . '/' . self::$cacheDir;
            if (!file_exists(self::$cacheDir)) {
                mkdir(self::$cacheDir);
            }  
            $cacheChecked = true;
        }
        
        $md5 = md5($css);        
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
            $str = file_get_contents($css); 
            file_put_contents($cached_asset, $str);

            if ($type == 'css') {
                self::setCss("$cache_dir/$md5.$type", $order);
            }
            
            if ($type == 'js') {
                self::setJs("$cache_dir/$md5.$type", $order);
            } 
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
        moduleLoader::setModuleIniSettings($template, 'template');
        $css = config::getMainIni('css');
        if ($css) {
            self::setTemplateCssIni($template, $css);
        }
        

    }
    
    public static function loadTemplateIniAssets () {
                // load rel js
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
            self::setCss("/templates/$template/default/default.css?version=$version", $order);
            return;
        }
        $base_path = "/templates/$template/$css";
        $css_path = _COS_HTDOCS . "/$base_path/$css.css";
        $css_web_path = $base_path . "/$css.css";
        if (file_exists($css_path)) {

            self::setCss("$css_web_path?version=$version", $order);
            
        } else {
            self::setCss("/templates/$template/default/default.css?version=$version", $order);
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

/**
 * class with simple view methods
 * @package template
 */
class templateView {
   
    /**
     * indicate if we will override a view
     * @var boolean $override
     */
    public static $override = false;
    
    /**
     * var holding options for views
     * @var array $options
     */
    public static $options = array (
        'folder' => 'views'
    );
    
    /**
     * currenct view
     * @var string $view
     */
    public static $view = null;
    
    /**
     * method for setting override of normal views
     * @param string $view the view to override, e.g. account_login_email
     * @param array $options options array ('module', 'view', 'template', 'folder') 
     */
    public static function setOverride ($view, $options = array ()) {
        
        self::$override = true;
        self::$view = $view;
        if (isset($options['module'])) {
            self::$options['module'] = $options['module'];
        }
        if (isset($options['view'])) {
            self::$options['view'] = $options['view'];
        }
        if (isset($options['template'])) {
            self::$options['template'] = $options['template'];
        }
        if (isset($options['folder'])) {
            self::$options['folder'] = $options['folder'];
        }
        if (isset($options['ext'])) {
            self::$options['ext'] = $options['ext'];
        }
    }
    
    /**
     * get override view name
     * @return string $str filename of the view 
     */
    public static function getOverrideFilename () {
        
        if (isset(self::$options['template'])) {
            $filename = _COS_HTDOCS . "/template/" . self::$options[template];
        } 
        
        if (isset(self::$options['module'])) {
            $filename = _COS_PATH . "/modules/" . self::$options['module'];
        }
        
        $filename.= '/' . self::$options['folder'];
        if (isset(self::$options['view'])) {
            $filename.= '/' . self::$options['view'];
        } else {
            $filename.= '/' . self::$view; 
        }
        
        if (isset(self::$options['ext'])) {
            $filename.= '.' . self::$options['view'];
        } else {
            $filename.= '.' . 'inc'; 
        }
        return $filename;
    }
    
    /**
     * function for including a view file.
     * Maps to module (e.g. 'tags' and 'view file' e.g. 'add')
     * we presume that views are placed in modules views folder
     * e.g. tags/views And we presume that views always has a .inc
     * postfix
     *
     * @param string $module
     * @param string $view
     * @param array  $vars to parse into template
     * @param boolean return as string (1) or output directly (0)
     * @return string|void $str 
     */
    static function includeModuleView ($module, $view, $vars = null, $return = null){

        $base_path = _COS_PATH . "/modules";
        if (self::$override) {
            $filename = self::getOverrideFilename();
        } else {
            $filename = $base_path . "/$module/" . self::$options['folder'] . "/$view.inc";
        }
        
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
    
    /**
     * shorthand for includeModuleView. Will always return the parsed template 
     * instead of printing to standard output. 
     * 
     * @param string $module the module to include view from
     * @param string $view the view to use
     * @param mixed $vars the vars to use in the template
     * @return string $parsed the parsed template view  
     */
    public static function get ($module, $view, $vars = null) {
       
        // only template who has set name will be able to override this way
        $template = config::getModuleIni('template_name');
        if ($template) {
            $override = _COS_HTDOCS . "/templates/$template/$module/$view.inc";
            if (is_file($override)) {
                return self::getFileView($override, $vars);
            }
        }
        return self::includeModuleView($module, $view, $vars, 1);
    }
    
    /**
     * include a set of module function used for e.g. templates. These 
     * functions can be overridden in template if they exists in a template
     * @param string $module
     * @param string $file
     * @return void
     */
    public static function includeOverrideFunctions ($module, $file) {

        // only template who has set name will be able to override this way
        // templage_name = 'clean'
        $template = layout::getTemplateName();
        if ($template) {
            $override = _COS_HTDOCS . "/templates/$template/$module/$file";
            if (is_file($override)) {
                include_once $override;
                return;
            }
        }
        include_once _COS_PATH . "/modules/$module/$file";
    }
    
    
    /**
     * gets a direct file view
     * @param string $filename
     * @param mixed $vars
     * @return strin $str 
     */
    public static function getFileView ($filename, $vars = null) {
            ob_start();
            include $filename;
            $contents = ob_get_contents();
            ob_end_clean();
            return $contents;
    }
}

/**
 * alias for templateView 
 * @package template
 */
class view extends templateView {
    
}

/**
 * function for including a view file.
 * Maps to module (e.g. 'tags' and 'view file' e.g. 'add')
 * we presume that views are placed in modules views folder
 * e.g. tags/views And we presume that views always has a .inc
 * postfix
 * @ignore
 * @param string $module the module where our view exists
 * @param string $view the view file we want to use
 * @param mixed $vars vars to substitue in view
 * @param boolean $return if true we will return the content of the view
 *                        if false we echo the view
 */
function include_view ($module, $view, $vars = null){
    return templateView::includeModuleView($module, $view, $vars, 1); 
}

/**
 * function for getting content from a file
 * used as a very simple template function
 * @deprecated
 * @param string $filename the full path of the file to include
 * @param mixed  $vars the var to sustitute with
 * @return string $str the parsed template.
 */
function get_include_contents($filename, $vars = null) {
    if (is_file($filename)) {
        ob_start();
        include $filename;
        $contents = ob_get_contents();
        ob_end_clean();
        return $contents;
    }
    return false;
}
