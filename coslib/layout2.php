<?php



/**
 * File contains a short extension of layout class
 * Using this class will keep better state of current link and 
 * set the current css class
 * @package layout
 */

/**
 * layout2 extension of layout for keeping better state with current link 
 * @package layout
 */
class layout2 extends layout {

    /**
     * method for parsing a module menu.
     * A module menu is a menu connected to a main menu item.
     *
     * @param   array   $menu menu to parse
     * @return  string  containing menu in html form ul li
     */
    public static function parseModuleMenu($menu){
        $str = '';
        $str.= MENU_LIST_START . "\n";
        $num_items = $ex = count($menu);

        foreach($menu as $v){
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

            $str .= html::createLink($v['url'], $v['title']);
            $str .= MENU_SUBLIST_END;
        }
        $str.= MENU_LIST_END . "\n";
        return $str;
    }
    /**
     * function for parsing MAIN menu list.
     * Main menu is the menu holding all info about modules in database.
     * Therefore it is also some sort of top level module menu.
     */
    public static function parseMainMenuList (){

        $module_frag = uri::$info['module_base'];

        $menu = array();
        $menu = self::$menu['main'];
        $str = $css = '';
        foreach($menu as $v){
            
            if ( !empty($v['auth'])){
                if (!session::isUser()) continue;
                if (!session::isAdmin() && $v['auth'] == 'admin') continue;
                if (!session::isSuper()  && $v['auth'] == 'super') continue;
            }

            $options = array ();

            $url = explode('/', $v['url']);
            if (isset($url[1]) && isset($module_frag)) {
               if ("/$url[1]" == $module_frag) {
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
     * method for getting main module menu as html
     *
     * @return string containing menu module menu as html
     */
    public static function getMainMenu(){

        $str = '';
        $str.= '<ul>' . "\n";
        $str.= self::parseMainMenuList();
        $str .= "</ul>\n";
        return $str;
    }
}
