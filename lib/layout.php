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

    public static $module = NULL;


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
        if (isset($parent)) $module = $parent;

        // load parent ini settings
        moduleLoader::getModuleIniSettings($module);

        // get base menu as file
        $_MODULE_MENU = self::getMenuFromFile($module);

        $children_menu = self::getChildrenMenus($module);

        if (isset($_MODULE_MENU)){
            $_MODULE_MENU = array_merge($_MODULE_MENU, $children_menu);
            return $_MODULE_MENU;
        } else {
            return $children_menu;
        }
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
    public static function parseAdminMenuList (){

        $module_base = uri::$info['module_base'];
        $parent = moduleLoader::getParentModule($module_base);
        if ($parent){
            $module_base = "/" . $parent;
        }

        $menu = array();
        if (!isset(self::$menu['admin'])) return;
        $menu = self::$menu['admin'];
        $str = $css = '';
        foreach($menu as $k => $v){
            if ( !empty($v['auth'])){
                if (!session::isUser()) continue;
                if (!session::isAdmin() && $v['auth'] == 'admin') continue;
                if (!session::isSuper()  && $v['auth'] == 'super') continue;
            }
            
            if (isset($v['url']) && !empty($module_base)){       
                if (strstr($v['url'], $module_base)){
                    $css = 'current';
                } else {
                    $css = false;
                }
            }

            $str.="<li>";
            $link = create_link( $v['url'], $v['title'], false, $css);

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

        $module_base = uri::$info['module_base'];
        $parent = moduleLoader::getParentModule($module_base);
        if ($parent){
            $module_base = "/" . $parent;
        }

        $menu = array();
        $menu = self::$menu['main'];
        $str = $css = '';
        foreach($menu as $k => $v){
            if ( !empty($v['auth'])){
                if (!session::isUser()) continue;
                if (!session::isAdmin() && $v['auth'] == 'admin') continue;
                if (!session::isSuper()  && $v['auth'] == 'super') continue;
            }

            if (isset($v['url']) && !empty($module_base)){
                if (strstr($v['url'], $module_base)){
                    $css = 'current';
                } else {
                    $css = false;
                }
            }

            $str.="<li>";
            $link = create_link( $v['url'], $v['title'], false, $css);

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

        $module_base = uri::$info['module_base'];
        $parent = moduleLoader::getParentModule($module_base);
        if ($parent){
            $module_base = "/" . $parent;
        }

        $str = '';      
        
        $num_items = $ex = count($menu);

        foreach($menu as $k => $v){         
            if ( !empty($v['auth'])){
                if (!session::isUser()) continue;
                if (!session::isAdmin() && $v['auth'] == 'admin') continue;
                if (!session::isSuper()  && $v['auth'] == 'super') continue;
            }
            
            $str .= MENU_SUBLIST_START;
            if ($num_items && ($num_items != $ex) ){
                $str .= MENU_SUB_SEPARATOR;
            }
            $num_items--;

            if ( (strstr($v['url'] , $module_base))
                 ){
                $css = 'current';
            } else {
                $css = false;
            }

            $str .= create_link($v['url'], $v['title'], false, $css);
            $str .= MENU_SUBLIST_END;
        }

        if (empty($str)){
            return '';
        }
        $str = MENU_LIST_START . $str . MENU_LIST_END . "\n";
        return $str;
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
    public static function getBlocks(){

        $blocks = array();

        if (isset(register::$vars['coscms_main']['blocks'],register::$vars['coscms_main']['module']['blocks'])){
            $blocks = array_merge(register::$vars['coscms_main']['blocks'], register::$vars['coscms_main']['module']['blocks']);
        } else if (isset(register::$vars['coscms_main']['blocks'])) {
            $blocks = register::$vars['coscms_main']['blocks'];
        } else if (isset(register::$vars['coscms_main']['module']['blocks'])){
            $blocks = register::$vars['coscms_main']['module']['blocks'];
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
            $ret_blocks[] = $func();
        }
        return $ret_blocks;
    }

    /**
     * method for getting all blocks to be used
     *
     *                  blocks to use
     * @return array    blocks containing strings with html to display
     */
    public static function getBlocksSec(){

        $blocks = array();

        if (isset(register::$vars['coscms_main']['module']['blocks_sec_module_only'])){
            $blocks = register::$vars['coscms_main']['module']['blocks_sec'];
        } else if (isset(register::$vars['coscms_main']['blocks_sec'], register::$vars['coscms_main']['module']['blocks_sec'])){
            $blocks = array_merge(register::$vars['coscms_main']['blocks_sec'], register::$vars['coscms_main']['module']['blocks_sec']);
        } else if (isset(register::$vars['coscms_main']['blocks_sec'])) {
            $blocks = register::$vars['coscms_main']['blocks_sec'];
        } else if (isset(register::$vars['coscms_main']['module']['blocks_sec'])){
            $blocks = register::$vars['coscms_main']['module']['blocks_sec'];
        } else {
            return $blocks;
        }

        $ret_blocks = array();
        foreach ($blocks as $key => $val) {
            $func = explode('/', $val);
            $num = count($func) -1;
            $func = explode ('.', $func[$num]);
            $func = 'block_' . $func[0];
            include_once _COS_PATH . $val;
            $ret_blocks[] = $func();
        }
        return $ret_blocks;
    }

    /**
     * method for getting top blocks which typicaly will be diplayed in 
     * a page header
     *
     *                  where we can se which top blocks to parse
     * @return array    array of blocks containing strings with html to display
     */
    public static function getTopBlocks(){
        if (!isset(register::$vars['coscms_main']['blocks_top'])){
            register::$vars['coscms_main']['blocks_top'] = array();
        }
        $blocks = array();
        foreach (register::$vars['coscms_main']['blocks_top'] as $key => $val) {
            $func = explode('/', $val);
            $num = count($func) -1;
            $func = explode ('.', $func[$num]);
            $func = 'block_' . $func[0];
            include_once _COS_PATH . $val;
            $blocks[] = $func();
        }
        return $blocks;
    }
}