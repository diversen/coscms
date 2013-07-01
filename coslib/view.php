<?php



/**
 * a collection of view function
 * used for views
 * @package view
 */

/**
 * class with simple view methods
 * @package view
 */
class view {
   
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
            $filename = _COS_PATH . '/' . _COS_MOD_DIR . '/' . self::$options['module'];
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

        if (self::$override) {
            $filename = self::getOverrideFilename();
        } else {
            $filename = _COS_MOD_PATH . "/$module/" . self::$options['folder'] . "/$view.inc";
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
        $view = $view . ".inc";
        return self::getFile($module, $view, $vars);
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
    public static function getFile ($module, $view, $vars = null) {
       
        // only template who has set name will be able to override this way
        $template = config::getModuleIni('template_name');
        if ($template) {
            $override = _COS_HTDOCS . "/templates/$template/$module/$view";
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
        include_once _COS_MOD_PATH .  "/$module/$file";
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
