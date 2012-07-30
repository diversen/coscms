<?php

/**
 * File contains contains class for creating layout, which means menus and
 * blocks. It operates closely with template. It loads template. In normal
 * loading mode it is almost only used in head.
 *
 * @package    layout
 */

/**
 * class for creating layout, which means menus and blocks
 * class also loads template in its constructor.
 *
 * @package    layout
 */
class layout {

    /**
     * var holding menus
     * @var array $menu
     */
    public static $menu = array();

    /**
     * var holding all blocks content. 
     * @var array $blocksContent  
     */
    public static $blocksContent = array();

    /**
     * construtor method
     * checks for a admin template. 
     * loads a template from htdocs/templates
     * loads a template's common file htdocs/templates/template/common.inc
     * @param string $template
     */
    function __construct($template = null){
        
        // check is a admin template is being used. 
        if (session::isAdmin() && isset(config::$vars['coscms_main']['admin_template'])){
            config::$vars['coscms_main']['template'] = config::$vars['coscms_main']['admin_template'];
        }
        
        if (!isset($template)) {
            $template = config::$vars['coscms_main']['template'];
        }
        
        // load template. This is done before parsing the modules. Then the 
        // modules still can effect the template. Set header, css, js etc. 
        $template_path = 
            _COS_PATH .
            "/htdocs/templates/" .
            $template;
        
        include_once $template_path . "/common.inc";
        include_once $template_path . "/template.inc";
        
        template::init(config::$vars['coscms_main']['template']);
    }
    
    /**
     * @ignore
     */
    public static function setLayout ($template) {
        $layout = null;
        $layout = new layout($template);
    }
    
    /**
     * method for loading menus. All Main menu entries is generated from
     * database, while all module or submodule menus are generated from
     * files (menu.inc).
     * 
     */
    public function loadMenus(){
        $num = uri::getInstance()->numFragments();
        $db = new db();

        // always a module menu in web mode
        self::$menu['main'] = $db->selectQuery("SELECT * FROM `menus` WHERE  ( `parent` = '0') AND `admin_only` = 0 ORDER BY `weight` ASC");

        //self::setMainMenuTitles();
        
        // admin item are special
        if (session::isAdmin()){
            self::$menu['admin'] = $db->selectQuery("SELECT * FROM `menus` WHERE `admin_only` = 1 OR `section` != '' ORDER BY `weight` ASC");

        }

        self::setMainMenuTitles();
        
        // if status is set we don't load module menus. Must be 404 or 403.
        // we then return empty array. module loader will know what to do when
        // including correct error pages. No menus from normal main module 
        // should then be loaded. 
        if (!empty(moduleLoader::$status)){
            return array();
        }

        // get base menu from file. Base menu is always loaded if found.
        // we decide this from num fragments in uri. 
        $module = uri::getInstance()->fragment(0);
        if ($num >= 2){
            $menu = self::getBaseModuleMenu($module);
            self::$menu['module'] = $menu;
        }

        // with num fragments larger then two we know there may be a sub module
        // parse sub level module menu if a such exists
        if ($num > 2){
            $sub = uri::getInstance()->fragment(0). '/' . 
                   uri::getInstance()->fragment(1);
            self::$menu['sub'] = self::getSubMenu($sub);
        }
        
        // check if module being loaded is a child module to a parent module
        $parent = moduleLoader::getParentModule($module);
        if (isset($parent)) {
            self::$menu['sub'] = self::getMenuFromFile($module);
        }
    }
    
    /**
     * translate all menu items. 
     * With main menu items we look for human translation.
     */
    public static function setMainMenuTitles () {
        foreach (self::$menu['main'] as &$val) {
            $val['title'] = lang::translate($val['title']);
            if (!empty($val['title_human'])) $val['title'] = $val['title_human'];
        }
        
        if (session::isAdmin()) {
            foreach (self::$menu['admin'] as &$val) {
                $val['title'] = lang::translate($val['title']);
                //if (!empty($val['title_human'])) $val['title'] = $val['title_human'];
            }    
        }
    }

    /**
     * init blocks and parse blocks. 
     * blocks which should be inited are set in main config/config.ini
     * and looks like this: 
     * 
     * blocks_all = 'blocks,blocks_sec,blocks_top'
     * 
     * Above ini setting means that we use three block sections called:
     *     blocks, blocks_sec and blocks_top. 
     * These names we then can use in our template, and display the blocks 
     * using these names. 
     */
    public static function initBlocks () {
        $blocks = config::getMainIni('blocks_all');
        if (!isset($blocks)) return;
        //print_r($blocks); die;
        $blocks = explode(',', $blocks);

        foreach ($blocks as $val) {
            self::$blocksContent[$val] = self::parseBlock($val);
        }        
    }
    
    /**
     * return a whole block which can be used in templates. 
     * e.g. blocks_top will return all blocks in section blocks_top
     * @param type $block
     * @return type 
     */
    public static function getBlock ($block) {
        if (isset(self::$blocksContent[$block])){
            return self::$blocksContent[$block];
        }
        return array();
    }
    
    /**
     * method for getting child menus to a module. 
     * @param  string   $module
     * @return array    children menus items
     */
    public static function getChildrenMenus ($module){
        
        $db = new db();
        $children = $db->selectAll('menus', null, array('parent' => $module));
        
        foreach ($children as $key => $val) {
            $children[$key]['title'] = lang::translate($val['title']);
        }
        
        return $children;
    }

    /**
     * reads a module menu from a file. 
     * @param  string   $module e.g. content or content/article
     * @return array    $menu as array
     */
    public static function getMenuFromFile ($module){
        $module_menu =
        _COS_PATH . '/modules/' . $module . '/menu.inc';

        if (file_exists($module_menu)){
            include $module_menu;
        }

        if (isset($_MODULE_MENU)){
            return $_MODULE_MENU;
        }

        if (isset($_SUB_MODULE_MENU)){
            return $_SUB_MODULE_MENU;
        }

        return array();

    }

    /**
     * gets the base modules menu. 
     * @param   string  module name
     * @return  array   array with top level module menu
     */
    public static function getBaseModuleMenu($module){
        $menu = array();

        $parent = moduleLoader::getParentModule($module);
        if (isset($parent)) { 
            $module = $parent;
        }

        moduleLoader::getModuleIniSettings($module);
        $module_menu = self::getMenuFromFile($module);
        
        $children_menu = self::getChildrenMenus($module);
        $module_menu = array_merge($module_menu, $children_menu);  
        
        $db_config_file = _COS_PATH . "/modules/$module/configdb.inc";
        
        if (file_exists($db_config_file)) {
            include $db_config_file;
            if (isset($db_config_menu)) {
                $module_menu = self::setDbConfigMenuItem ($module_menu, $module);
            }
        }
        
        return $module_menu;
    }
    
    /**
     * Attach a db module config menu item to a module menu. See module dbconfig
     * This can be used for auto generating config settings for your module
     * The url for a dbconfig setting is always $module/config/index
     * 
     * @param array $module_menu
     * @param type $module
     * @return array $module_menu now with the config menu item
     */
    public static function setDbConfigMenuItem($module_menu, $module) {
        $config_menu_item = array (
            'url' => "/$module/config/index",
            'title' => lang::translate('config_main_menu_edit'));
        
        // if e.g. account_allow_db_config is not set we use admin as base setting
        $allow_config = $module . "_allow_db_config";
        $allow = config::getModuleIni($allow_config);
        if (!$allow) {
            $allow = 'admin';
        }
        
        $config_menu_item['auth'] = $allow;        
        $module_menu[] = $config_menu_item;
        return $module_menu;
    }

    /**
     * gets a modules sub menu from file. 
     * @param  string  $sub the name of the module to get menu from
     * @return array $ary the module menu. 
     */
    public static function getSubMenu ($sub) {
        return (self::getMenuFromFile($sub));
    }



    /**
     * function for parsing Admin menu list.
     * Admin menu is the menu holding all info which is only accessed
     * by super or admin. 
     * 
     * Therefore it is also some sort of top level admin menu.
     * @param array $options
     * @return string $admin_menu as a str li ... li
     */
    public static function parseAdminMenuList ($options = array()){

        $module_base = uri::$info['module_base'];
        $parent = moduleLoader::getParentModule($module_base);
        if ($parent){
            $module_base = "/" . $parent;
        }

        $menu = array();
        if (!isset(self::$menu['admin'])) return;
        if (isset($options['menu'])) {
            $menu = $options['menu'];
        } else {
            $menu = self::$menu['admin'];
        }
        
        $str = $css = '';
        foreach($menu as $k => $v){
            if ( !empty($v['auth'])){
                if (!session::isUser() && $v['auth'] == 'user') continue;
                if (!session::isAdmin() && $v['auth'] == 'admin') continue;
                if (!session::isSuper()  && $v['auth'] == 'super') continue;
            }
            
            $options = array();
            if (isset($v['url']) && !empty($module_base)){
                if (strstr($v['url'], $module_base)){
                    $options['class'] = 'current';
                } 
            }

            $str.="<li>";
            $link = html::createLink( $v['url'], $v['title'], $options);

            $str.=  $link;
            if (isset($v['sub'])){
                $str .= self::parseMainMenuList($v['sub']);
            }
            $str .= "</li>\n";
        }
        return $str;

    }

    /**
     * function for parsing main menu list
     * @return string   the main menu. as <li>item</li> etc 
     */
    public static function parseMainMenuList (){
        $menu = array();
        $menu = self::$menu['main'];
        $str = $css = '';
        foreach($menu as $k => $v){
            if ( !empty($v['auth'])){
                if (!session::isUser()) continue;
                if (!session::isAdmin() && $v['auth'] == 'admin') continue;
                if (!session::isSuper()  && $v['auth'] == 'super') continue;
            }


            $str.="<li>";
            $link = html::createLink( $v['url'], $v['title']);
            $str.=  $link;
            if (isset($v['sub'])){
                $str .= self::parseMainMenuList($v['sub']);
            }
            $str .= "</li>\n";
        }
        return $str;

    }


    /**
     * method for parsing a module menu.
     * A module menu is a menu connected to a main menu item.
     *
     * @param array $menu array to parse
     * @param array $options
     * @return string $str containing menu in html form, an ul with li elements
     */
    public static function parseModuleMenu($menu, $options = null){

        $str = '';              
        $num_items = $ex = count($menu);

        foreach($menu as $v){         
            if ( !empty($v['auth'])){
                if (!session::isUser() && $v['auth'] == 'user') continue;
                if (!session::isAdmin() && $v['auth'] == 'admin') continue;
                if (!session::isSuper()  && $v['auth'] == 'super') continue;
            }
            
            $str.= "<li>";
            if ($num_items && ($num_items != $ex) ){
                $str .= MENU_SUB_SEPARATOR;
            }
            $num_items--;       
            $str .= html::createLink($v['url'], $v['title']);
            $str.= "</li>\n";
        }
        
        if (empty($str)) return '';
        
        return "<ul>\n$str</ul>\n";
    }
    
    /**
     * method for adding extra menu items to a menu
     * @param array $items menu items to add.  
     */
    public static function setModuleMenuExtra($items) {
        self::$menu['extra'] = $items;
    }
    
    /**
     * attach $menu['extra'] to a menu
     * @return string $str the html menu 
     */
    static function parseModuleMenuExtra () {
        $str = '';              
        $num_items = $ex = count(self::$menu['extra']);

        foreach(self::$menu['extra'] as $k => $v){         
            $str.= "<li>";
            if ($num_items && ($num_items != $ex) ){
                $str .= MENU_SUB_SEPARATOR;
            }
            $num_items--;       
            $str .= $v;
            $str.= "</li>\n";
        }
        return "<ul>\n$str</ul>\n";
    }

    /**
     * method for getting module menu as html
     * We just parse module menu and sub (if any) and extra (if any)
     * and return it as html.
     *
     * @return  string  containing menu as html
     */
    public static function getModuleMenu(){
        $str = '';
        if (!empty(self::$menu['module'])){            
            $str.= self::parseModuleMenu(self::$menu['module'], 'module');
        }
        if (!empty(self::$menu['sub'])){
            $str.= self::parseModuleMenu(self::$menu['sub'], 'sub');
        }
        
        if (!empty(self::$menu['extra'])) {
            $str.= self::parseModuleMenuExtra();
        }
        return $str;
    }

    /**
     * method for getting main module menu as html
     *
     * @return string containing menu module menu as html
     */
    public static function getMainMenu(){

        $list = self::parseMainMenuList();
        if (empty($list)){
            return '';
        }
        $str = '';
        $str.= '<ul>' . "\n";
        $str.= $list;
        $str .= "</ul>\n";
        return $str;
    }
    
    /**
     * method for getting all parsed blocks
     * @todo clearify what is going on
     * @param string $block
     * @return array blocks containing strings with html to display
     */
    public static function parseBlock($block){

        $blocks = array();
        if (isset(config::$vars['coscms_main'][$block],config::$vars['coscms_main']['module'][$block])){
            $blocks = array_merge(config::$vars['coscms_main'][$block], config::$vars['coscms_main']['module'][$block]);
        } else if (isset(config::$vars['coscms_main'][$block])) {
            $blocks = config::$vars['coscms_main'][$block];
        } else if (isset(config::$vars['coscms_main']['module'][$block])){
            $blocks = config::$vars['coscms_main']['module'][$block];
        } else {
            return $blocks;
        }

        $ret_blocks = array();
        foreach ($blocks as $val) {
            
            // numeric is custom block added to database
            if (is_numeric($val)) {
                include_module('block_manip');
                $row = block_manip::getOne($val); 
                $row['content_block'] = get_filtered_content(
                    get_module_ini('block_manip_filters'), $row['content_block']
                );
                $row['title'] = htmlspecialchars($row['title']);
                $content = templateView::get('block_manip', 'block_html', $row);
                $ret_blocks[] = $content;
                continue;
            }
            
            if ($val == 'module_menu'){
                $ret_blocks[] = self::getMainMenu();
                continue; 
            }
            $func = explode('/', $val);
            $num = count($func) -1;
            $func = explode ('.', $func[$num]);
            $func = 'block_' . $func[0];
            $path_to_function = _COS_PATH . $val;
            include_once $path_to_function;
            ob_start();
            $ret = $func();
            if ($ret) {
                $ret_blocks[] = $ret; 
            } else {
                $ret_blocks[] = ob_get_contents();
                ob_end_clean();
            }
        }
        return $ret_blocks;
    }
}
