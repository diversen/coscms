<?php



/**
 * File contains contains class for creating layout, which means menus and
 * blocks
 *
 * @package    coslib
 */

/**
 * class for creating layout, which means menus and blocks
 * class also loads template in its constructor.
 *
 * @package    coslib
 */


class layout extends db {

    /**
     *
     * @var     object  uri object
     */
    public $uri;

    /**
     *
     * @var     array   menu for holding different menus (main / module / sub)
     */
    public static $menu = array();

    /**
     * @var     string  holds if user is user, admin or super or null .)
     */
    //public static $user = null;
    public static $blocks_sec;

    public static $breadcrumbs = array();

    public static $options = array();

    public static $blocksContent = array();
    
    public static $module = null;


    /**
     * constructer method where we init our uri object and checks users
     * credentials (admin / user) thus generating
     * accurate menus for each group.
     */
    function __construct(){

        $this->uri = uri::getInstance();

        // include template
        if (session::isAdmin() && isset(register::$vars['coscms_main']['admin_template'])){
            register::$vars['coscms_main']['template'] = register::$vars['coscms_main']['admin_template'];
        }
        $template_path = 
            _COS_PATH .
            "/htdocs/templates/" .
            register::$vars['coscms_main']['template'];
        
        include_once $template_path . "/common.inc";
        include_once $template_path . "/template.inc";
    }
    /**
     * method for loading menus. All Main menu entries is generated from
     * database, while all module or submodule menus are generated from
     * files (menu.inc).
     *
     */
    public function loadMenus(){

        $num = $this->uri->numFragments();
        $db = new db();

        // always a module menu in web mode

        self::$menu['main'] = $db->selectQuery("SELECT * FROM `menus` WHERE  (`parent` IS NULL OR `parent` = '0') AND `admin_only` IS NULL ORDER BY `weight` ASC");

        if (session::isAdmin()){
            self::$menu['admin'] = $db->selectQuery("SELECT * FROM `menus` WHERE `admin_only` IS NOT NULL ORDER BY `weight` ASC");

        }

        // if status is set we don't load module menus. Must be 404 or 403.
        // we then return empty array. module loader will know what to do.

        if (!empty(moduleLoader::$status)){
            return array();
        }

        // get base menu from file

        self::$module = $module = $this->uri->fragment(0);
        if ($num >= 2){
            
            $menu = self::getBaseModuleMenu($module);
            self::$menu['module'] = $menu;

        }

        // parse sub level module menu if a such exists
        if ($num > 2){
            $sub = $this->uri->fragment(0). '/' . $this->uri->fragment(1);
            self::$menu['sub'] = self::getSubMenu($sub);
        }
        
        $parent = moduleLoader::getParentModule(self::$module);
        if (isset($parent)) {
            self::$menu['sub'] = self::getMenuFromFile(self::$module);
        }
    }

    
    function initBlocks () {
        $blocks = get_main_ini('blocks_all');
        if (!isset($blocks)) return;
        //print_r($blocks); die;
        $blocks = explode(',', $blocks);

        foreach ($blocks as $val) {
            self::$blocksContent[$val] = self::parseBlock($val);
        }        
    }
    
    function getBlock ($block) {
        return self::$blocksContent[$block];
    }
    /**
     *
     * @param  string   $module
     * @return array    children menus items
     */
    public static function getChildrenMenus ($module){
        static $children;
        if (isset($children[$module])) return $children[$module];

        $db = new db();
        $children[$module] = $db->select('menus', null, array('parent' => $module));
        return $children[$module];
    }

    /**
     *
     * @param  string   module e.g. content or content/article
     * @return array    menu as array
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
     *
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
            $module_menu = self::setDbConfigMenuItem ($module_menu, $module);
        }
        
        return $module_menu;
    }
    
    public static function setDbConfigMenuItem($module_menu, $module) {
        $config_menu_item = array (
            'url' => "/$module/config/index",
            'title' => lang::translate('config_main_menu_edit'));
        
        // if e.g. account_allow_db_config is not set we use admin as base setting
        $allow_config = $module . "_allow_db_config";
        $allow = get_module_ini($allow_config);
        if (!$allow) {
            $allow = 'admin';
        }
        
        $config_menu_item['auth'] = $allow;        
        $module_menu[] = $config_menu_item;
        return $module_menu;
    }

    /**
     *
     * @param  string  name of sub menu to fetch $sub
     * @return array with $_SUB_MODULE_MENU
     */
    public static function getSubMenu ($sub) {
        return (self::getMenuFromFile($sub));
    }



    /**
     * function for parsing Admin menu list.
     * Admin menu is the menu holding all info about modules in database.
     * Therefore it is also some sort of top level module menu.
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
     * function for parsing Admin menu list.
     * Admin menu is the menu holding all info about modules in database.
     * Therefore it is also some sort of top level module menu.
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
     * @param   array   menu to parse
     * @return  string  containing menu in html form ul li
     */
    public static function parseModuleMenu($menu, $type){

        $str = '';              
        $num_items = $ex = count($menu);

        foreach($menu as $k => $v){         
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
        
        return "<ul>\n$str</ul>\n";

        if (empty($str)){
            return '';
        }
        $str = MENU_LIST_START . $str . MENU_LIST_END . "\n";
        return $str;
    }
    
    static function setModuleMenuExtra($items) {
        self::$menu['extra'] = $items;
    }
    
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
     * method for getting module menu in a html form
     * We just parse module menu and sub (module ) menu
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
     * method for getting all blocks to be used
     *
     *                  blocks to use
     * @return array    blocks containing strings with html to display
     */
    public static function parseBlock($block){

        $blocks = array();

        if (isset(register::$vars['coscms_main'][$block],register::$vars['coscms_main']['module'][$block])){
            $blocks = array_merge(register::$vars['coscms_main'][$block], register::$vars['coscms_main']['module'][$block]);
        } else if (isset(register::$vars['coscms_main'][$block])) {
            $blocks = register::$vars['coscms_main'][$block];
        } else if (isset(register::$vars['coscms_main']['module'][$block])){
            $blocks = register::$vars['coscms_main']['module'][$block];
        } else {
            return $blocks;
        }

        $ret_blocks = array();
        foreach ($blocks as $key => $val) {
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