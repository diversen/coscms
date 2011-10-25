<?php

/**
 * File contains helper functions
 *
 * @package    common
 */

/**
 * puts a string in logs/coscms.log
 * @param string $message
 */
function cos_error_log ($message) {
    $message = strftime('%c', time()) . ": " . $message;
    $message.="\n";
    $destination = _COS_PATH . "/logs/coscms.log";
    error_log($message, 3, $destination);
}

// Variable function

// {{{ function get_zero_or_positive($int, $max = null);
/**
 * function for checking if var is int larger than zero
 * 
 * @param   mixed   $int the var to check
 * @param   int     $max max size of integer
 * @return  int     0 or positive integer
 */
function get_zero_or_positive($int, $max = null){
    $int = (int)$int;
    if (!is_int($int)){
        $zero = true;
    }
    if (isset($max)){
        if ($int > $max){
            $zero = true;
        }
    }

    //negativ int
    if ($int < 0) {
        $zero = true;
    }
    if (isset($zero)) {
        return 0;
    } else {
        return $int;
    }
}

// }}}
function trim_value(&$value){ 
    $value = trim($value); 
}

function trim_array ($ary) {


    array_walk($ary, 'trim_value');
    return $ary;
}

function isset_and_equal ($var, $val) {
    if (isset($var)) {
        if ($var == $val) {
            return true;
        }
    }
    return false;
}

// {{{ function cos_htmlentites($values)

/**
 * function for creating rewriting htmlentities for safe display on screen
 *
 * @param   array|string    value(s) to transform
 * @return  array|string    value(s) transformed
 */
function cos_htmlentities($values){
    if (is_array($values)){
        foreach($values as $key => $val){
            $values[$key] = htmlentities($val, ENT_COMPAT, 'UTF-8');
        }
    } else if (is_string($values)) {
        $values =  htmlentities($values, ENT_COMPAT, 'UTF-8');
    } else {
        $values = '';
    }
    return $values;
}


/**
 * function for creating rewriting htmlentities for safe display on screen
 *
 * @param   array|string    value(s) to transform
 * @return  array|string    value(s) transformed
 */
function cos_htmlentities_decode($values){
    if (is_array($values)){
        foreach($values as $key => $val){
            $values[$key] = html_entity_decode($val, ENT_COMPAT, 'UTF-8');
        }
    } else if (is_string($values)) {
        $values =  html_entity_decode($values, ENT_COMPAT, 'UTF-8');
    } else {
        $values = '';
    }
    return $values;
}
//html_entity_decode
// }}}
// {{{ function cos_htmlspecialchars($values)

/**
 * function for creating rewriting htmlspecialchars for safe display on screen
 *
 * @param   array|string    value(s) to transform
 * @return  array|string    value(s) transformed
 */
function cos_htmlspecialchars($values){
    if (is_array($values)){
        foreach($values as $key => $val){
            $values[$key] = htmlspecialchars($val, ENT_COMPAT, 'UTF-8');
        }
    } else {
        $values =  htmlspecialchars($values, ENT_COMPAT, 'UTF-8');
    }
    return $values;
}

// }}}
// {{{ function timestamp_to_days($timestamp)
/**
 * method for transforming a timestamp to days
 * @param   string  timestamp
 * @return  int     days
 */
function timestamp_to_days($updated){
    $diff = time() - strtotime($updated);
    $diff / 60 / 60 / 24;
}
// }}}
// {{{ function isvalue($var)
/**
 * method used for checking if something is a value
 * is something is sat and has values
 *
 * @param   mixed
 * @return  boolean
 */
function isvalue($var){
    if (isset($var) && !empty($var)){
        return true;
    }
    return false;
}

// }}}
function print_r_str ($str){
    ob_start();
    print_r($str);
    $str = ob_get_contents();
    ob_end_clean();
    return $str;
}

// {{{ function substr2 ($str, $length, $min)
/** 
 * Substring without losing word meaning and
 * tiny words (length 3 by default) are included on the result.
 *  "..." is added if result do not reach original string length
 * Found on php.net
 *
 * @param   string  string to operate on
 * @param   int     length to cut at
 * @param   int     size of minimum word
 * @return  string  string transformed
 */
function substr2($str, $length, $minword = 3, $use_dots = true)
{
    $sub = '';
    $len = 0;

    foreach (explode(' ', $str) as $word)
    {
        $part = (($sub != '') ? ' ' : '') . $word;
        $sub .= $part;
        $len += strlen($part);

        if (strlen($word) > $minword && strlen($sub) >= $length)
        {
            break;
        }
    }

    if ($use_dots) {
        return $sub . (($len < strlen($str)) ? ' ... ' : '');
    }
    return $sub;
}

// }}}
function cos_remove_extra_ws ($str) {
    $str = preg_replace('/\s\s+/', ' ', $str);
    return $str;
}
// {{{ function save_post($id)
/**
 * simple method for saving $_POST vars to session
 *
 * @param   string  id of the post to save
 */
function save_post ($id){
     $_SESSION[$id] = $_POST;
}

// }}}
// {{{ function load_post($id)
/**
 * method for loading $_POST vars from session
 * @param   string  id of the post to load
 */
function load_post($id){
    if (!isset($_SESSION[$id])) {
        return false;
    }
    $_POST = $_SESSION[$id];
    return true;
}
// }}}

function get_post($id) {
    if (!isset($_SESSION[$id])) {
        return false;
    }
    return $_SESSION[$id];
    //return true;
}

// {{{ function cos_url_encode($string)
/**
 * function for url encoding a utf8 string
 * @param   string  the utf8 string to encode
 * @return  string  the utf8 encoded string
 */
function cos_url_encode($string){
    return urlencode(utf8_encode($string));
}

// }}}
// {{{ function cos_url_decode ($string)
/**
 * function for decoding a url8 encoded string
 * @param   string  the string to decode
 * @return  string  the decoded utf8 string
 */
function cos_url_decode($string){
    return utf8_decode(urldecode($string));
}
// }}}

// File functions

// {{{ function get_file_list($dir)
/**
 * function for getting a file list of a directory (. and .. will not be
 * collected
 *
 * @param   string  the path to the directory where we want to create a filelist
 * @param   array   if $options['dir_only'] isset only return directories.
 *                  if $options['search'] isset then only dirs containing
 *                      search string will be returned
 * @return  array   entries of all files <code>array (0 => 'file.txt', 1 => 'test.php');</code>
 */
function get_file_list($dir, $options = null){
    if (!file_exists($dir)){
        return false;
    }
    $d = dir($dir);
    $entries = array();
    while (false !== ($entry = $d->read())) {
        if ($entry == '..') continue;
        if ($entry == '.') continue;
        if (isset($options['dir_only'])){
            if (is_dir($dir . "/$entry")){
                if (isset($options['search'])){
                    if (strstr($entry, $options['search'])){
                       $entries[] = $entry;
                    }
                } else {
                    $entries[] = $entry;
                }
            }
        } else {
            $entries[] = $entry;
        }
    }
    $d->close();
    return $entries;
}

// }}}
// {{{ function get_file_list_recursive($start_dir)
/**
 *
 * found on php.net
 */
function get_file_list_recursive($start_dir, $pattern = null) {

    $files = array();
    if (is_dir($start_dir)) {
        $fh = opendir($start_dir);
        while (($file = readdir($fh)) !== false) {
            // skip hidden files and dirs and recursing if necessary
            if (strpos($file, '.')=== 0) continue;
            
            $filepath = $start_dir . '/' . $file;
            if ( is_dir($filepath) ) {
                $files = array_merge($files, get_file_list_recursive($filepath, $pattern));
            } else {
                if (isset($pattern)) {
                    if (fnmatch($pattern, $filepath)) {
                        array_push($files, $filepath);
                    }
                } else {
                    array_push($files, $filepath);
                }
            }
        }
        closedir($fh);
    } else {
        // false if the function was called with an invalid non-directory argument
        $files = false;
    }

    return $files;
}
// }}}
// {{{

function include_template_inc ($template){
    include_once _COS_PATH . "/htdocs/templates/$template/common.inc";
}

// System function for including model, modules or controllers. 

function include_reference_module () {
    if (!isset($_GET['reference'])){
        return null;
    }
    return include_module($_GET['reference']);

}

// {{{ function include_module($module)
/**
 * function for including a modules view and model file
 *
 * @param   string  the name of the module to include
 *                  includes the view and the model file for module.
 */
function include_module($module, $options = null){

    static $modules = array ();
    if (isset($modules[$module])){
        // module has been included
        return true;
    }

    $module_path = register::$vars['coscms_base'] . '/modules/' . $module;
    $ary = explode('/', $module);
    $last = array_pop($ary);
    $model_file = $module_path . '/' . "model.$last.inc";  
    $view_file = $module_path . '/' . "view.$last.inc";
    $ary = explode('/', $module);

    lang::loadModuleLanguage($ary[0]);
    moduleLoader::setModuleIniSettings($ary[0]);

    if (file_exists($view_file)){
        include_once $view_file;
    }
    if (file_exists($model_file)){
        include_once $model_file;
        $modules[$module] = true;
        return true;
    } else {
        return false;
    }

}

// }}}
// {{{ function include_model($module)
/**
 *
 * @param   string   $module module to include e.g. (content/article)
 */
function include_model($module){
    $module_path = 'modules/' . $module;
    $ary = explode('/', $module);
    $last = array_pop($ary);
    $model_file = $module_path . '/' . "model.$last.inc";
    include_once $model_file;
}

// }}}
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
function include_view ($module, $view, $vars = null, $return = null){
    $filename = _COS_PATH . "/modules/$module/views/$view.inc";

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
        echo "View not found";
        return false;
    }    
}


// {{{ function include_controller($controller, $options)
/**
 *
 * @param string    controller to include (e.g. content/article/add)
 * @param array     $options
 */
function include_controller($controller, $options = null){
    $module_path = register::$vars['coscms_base']  . '/modules/' . $controller;
    $controller_file = $module_path . '.php';
    include_once $controller_file;
}

// }}}
// {{{ function include_filters($filters)
/**
 * include filters is used to include filters.
 * this is used if you need to set some settings in the
 * filter before using it.
 *
 * @param   mixed   string or array of filters to include
 *
 */
function include_filters ($filter){
    static $loaded = array();

    if (!is_array($filter)){
        $class_path = _COS_PATH . "/modules/filter_$filter/$filter.inc";
        include_once $class_path;
        //lang::loadModuleLanguage("filter_$val");
        moduleLoader::setModuleIniSettings("filter_$filter");
        $loaded[$filter] = true;
    }

    if (is_array ($filter)){
        foreach($filter as $key => $val){
            if (isset($loaded[$val])) continue;

            $class_path = _COS_PATH . "/modules/filter_$val/$val.inc";
            //lang::loadModuleLanguage("filter_$val");
            include_once $class_path;
            moduleLoader::setModuleIniSettings("filter_$val");

            $loaded[$val] = true;
        }
    }
}
// }}}
function get_filters_help ($filters) {
    include_filters($filters);
    $str = '<span class="small-font">';
    $i = 1;

    foreach($filters as $key => $val) {

        $str.= $i . ") " .  lang::translate("filter_" . $val . "_help") . "<br />";
        $i++;
    }
    $str.='</span>';
    return $str;
    
}
// {{{ function get_filtered_content($filter, $content)
/**
 *
 * @param  mixed    string or array (filters to use)
 * @param  mixed    string or array (to use filters on)
 * @return mixed    string or array
 */
function get_filtered_content ($filter, $content){
    
    if (!is_array($filter)){
        //$class_path = _COS_PATH . "/modules/filter_$filter/$filter.inc";
        //include_once $class_path;
        include_filters($filter);
        $class = 'filter' . ucfirst($filter);
        $filter_class = new $class;

        if (is_array($content)){
            foreach ($content as $key => $val){
                $content[$key] = $filter_class->filter($val);
            }
        } else {
            $content = $filter_class->filter($content);
        }
        
        return $content;
    }

    if (is_array ($filter)){

        foreach($filter as $key => $val){

            //$class_path = _COS_PATH . "/modules/filter_$val/$val.inc";
            //include_once $class_path;

            include_filters($val);
            $class = 'filter' . ucfirst($val);
            $filter_class = new $class;
            if (is_array($content)){
                foreach ($content as $key => $val){
                    $content[$key] = $filter_class->filter($val);
                }
            } else {
                $content = $filter_class->filter($content);
            }
        }
        return $content;
    }
    return '';
}

// }}}
// {{{ function get_module_ini($values)
/**
 * method for getting a modules ini settings
 */
function get_module_ini($value){
    if (!isset(register::$vars['coscms_main']['module'][$value])){
        return null;
    }

    return register::$vars['coscms_main']['module'][$value];
    
}
// }}}
// {{{ function get_main_ini($value)
/**
 * method for getting a main ini setting
 *
 * @param   string  ini setting to get
 * @return  mixed   the setting
 */
function get_main_ini($value){
    if (!isset(register::$vars['coscms_main'][$value])){
        return null;
    }

    if (register::$vars['coscms_main'][$value] == '0'){
        return null;
    }
    return register::$vars['coscms_main'][$value];
}

// }}}
// {{{ function get_include_contents ($filename)
/**
 * function for getting content from a file
 * used as a very simple template function
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

// }}}
// {{{ function get_module_path
/**
 * method for getting a path to a module
 *
 * @param   string  the module
 * @return  string  the module path
 */
function get_module_path ($module){
    return _COS_PATH . '/modules/' . $module;
}

// }}}

// HTML and HTTP function

// {{{ function create_seo_title($title)
/**
 * function for creating a seo friendly title
 * 
 * @deprecated use strings::seoTitle
 * @param   string   the title of the url to be created
 * @return  string   the title with _ instead of spaces ' '
 */
function create_seo_title($title){
    $title = explode(' ', ($title));
    $title = strtolower(implode($title, '-'));
    return $title;
}

// }}}
// {{{ function create_link($url, $title, $description)
/**
 * function for creating a link
 * @deprecated
 * @param   string  the url to create the link from
 * @param   string  the title of the link
 * @param   boolean if true we only return the url and not the html link
 * @return  string  the <code><a href='url'>title</></code> tag
 */
function create_link($url, $title, $return_url = false, $css = null){
    if (class_exists('rewrite_manip')) {
        $alt_uri = rewrite_manip::getRowFromRequest($url);
        if (isset($alt_uri)){
            $url = $alt_uri; 
        }
    } 

    if ($return_url){
        return $url;
    }
    if ($_SERVER['REQUEST_URI'] == $url){
        return "<a href=\"$url\" class=\"current\">$title</a>";
    }

    if ($css){
        $link = "<a href=\"$url\" class=\"$css\">$title</a>";
        return $link;
    }


    return "<a href=\"$url\">$title</a>";
    
}

// }}}
// {{{ function create_link($url, $title, $description)
/**
 * function for creating a link
 * @deprecated
 * @param   string  the url to create the link from
 * @param   string  the title of the link
 * @param   boolean if true we only return the url and not the html link
 * @return  string  the <code><a href='url'>title</></code> tag
 */
function create_image_link($url, $href_image, $options = null){
    $str = '';
    if (isset($options['alt'])) $str.= " alt = \"$options[alt]\" ";
    if (isset($options['title'])) $str.= " title = \"$options[title]\" ";
    if (isset($options['width'])) $str.= " width = \"$options[width]\" ";
    if (isset($options['height'])) $alt = $options['height'];
    return "<a href=\"$url\"><img $str src=\"$href_image\" /></a>";
}
/**
 * @deprecated
 * @param type $href_image
 * @param type $options
 * @return type 
 */
function create_image($href_image, $options = null){
    $str = '';
    if (isset($options['alt'])) $str.= " alt = \"$options[alt]\" ";
    if (isset($options['width'])) $str.= " width = \"$options[width]\" ";
    if (isset($options['height'])) $alt = $options['height'];
    return "<img $str src=\"$href_image\" />";
}

// }}}
// {{{ function view_drop_down_db($name, $table, $field, $id, $selected = null))
/**
 * function for creating a select dropdown from a database table.
 * @deprecated
 * @param   string  the name of the select filed
 * @param   string  the database table to select from
 * @param   string  the database field which will be used as name of the select element
 * @param   int     the database field which will be used as id of the select element
 * @param   int     the element which will be selected
 * @param   array   array of other non db options
 * @param   string  behavior e.g. onChange="this.form.submit()"
 * @return  string  the select element to be added to a form
 */
function view_drop_down_db($name, $table, $field, $id, $selected=null, $extras = null, $behav = null){
    $db = new db();
    $dropdown = "<select name=\"$name\" ";
    if (isset($behav)){
        $dropdown.= $behav;
        
    }
    $dropdown.= ">\n";
    $rows = $db->selectAll($table);
    if (isset($extras)){
        $rows = array_merge($extras, $rows);
    }
    foreach($rows as $row){
        if ($row[$id] == $selected){
            $s = ' selected';
        } else {
            $s = '';
        }

        $dropdown.= '<option value="'.$row[$id].'"' . $s . '>'.$row[$field].'</option>'."\n";
    }
    $dropdown.= '</select>'."\n";
    return $dropdown;
}

// }}}
// {{{ function view_drop_down($name, $rows, $field, $id, $selected= null)
/**
 * @deprecated
 * @param   string  the name of the select field
 * @param   array   the rows making up the ids and names of the select field
 * @param   string  the field which will be used as name of the select element
 * @param   int     the field which will be used as id of the select element
 * @param   int     the element which will be selected
 * @return  string  the select element to be added to a form
 */
function view_drop_down($name, $rows, $field, $id, $selected=null, $behav = null){
    $dropdown = "<select name=\"$name\" ";
    if (isset($behav)){
        $dropdown.= $behav;

    }
    $dropdown.= ">\n";
    foreach($rows as $row){
        if ($row[$id] == $selected){
            $s = ' selected';
        } else {
            $s = '';
        }

        $dropdown .= '<option value="'.$row[$id].'"' . $s . '>'.$row[$field].'</option>'."\n";
    }
    $dropdown .= '</select>'."\n";
    return $dropdown;
}

// }}}
// {{{ function simple_template ($file)
/**
 * @deprecated
 * simple template method for collecting a string from a file
 */
function simple_template ($file){
    ob_start();
    include $file;
    $parsed = ob_get_contents();
    ob_end_clean();
    return $parsed;
}
// }}}
// {{{ function get_profile_link (&$user)
/**
 * Gets user profile link if a profile system is in place.
 * Profile systems must be set in main config/config.ini
 * the option array can be used to setting special options for profile module
 * @deprecated
 * @param   array   user options
 * @param   array   options
 * @return  string  string showing the profile
 */
function get_profile_link ($user, $options = null){
    
    
    if (is_numeric($user)) {
        $user = get_account($user);
    }
    
    static $profile_object;

    if (!isset($profile_object)){
        $profile_system = get_main_ini('profile_module');
        if (!isset($profile_system)){
            return '';
        }

        include_module ($profile_system);

        $profile_object = moduleLoader::modulePathToClassName($profile_system);
        $profile_object = new $profile_object();        
        $link = $profile_object->createProfileLink($user, $options);
        return $link;
    }

    return $profile_object->createProfileLink($user, $options);
}
// }}}
// {{{ function get_profile_link (&$user)
/**
 * Gets user profile link if a profile system is in place.
 * Profile systems must be set in main config/config.ini
 * the option array can be used to setting special options for profile module
 * 
 * @deprecated
 * @param   array   user options
 * @param   array   options
 * @return  string  string showing the profile
 */
function get_profile_edit_link ($user_id){
    
    static $profile_object;

    if (!isset($profile_object)){
        $profile_system = get_main_ini('profile_module');
        if (!isset($profile_system)){
            return '';
        }

        include_module ($profile_system);

        $profile_object = moduleLoader::modulePathToClassName($profile_system);
        $profile_object = new $profile_object();      
        $link = $profile_object->getProfileEditLink($user_id);
        return $link;
    }

    return $profile_object->createProfileLink($user, $options);
}
// }}}


/**
 * function for getting account
 * @deprecated
 * @param int $id
 * @return array $row from account 
 */
function get_account ($id) {   
    $db = new db();
    $row = $db->selectOne('account', 'id', $id);
    return $row;
}

/**
 * @deprecated
 * @param type $user
 * @param type $text
 * @param type $date
 * @param type $date_format
 * @return type 
 */
function get_profile_link_full ($user, $text, $date, $date_format = 'date_format_long') {
        $unix_stamp = strtotime($date);
        $date = strftime(get_main_ini($date_format), $unix_stamp);
        $options = array ();
        $options['display'] = 'rows';
        $options['row'] = " $date ";
        $options['before'] = $text;
        $profile_link = get_profile_link($user, $options);
        return $profile_link;
}
// {{{ function simple_prg () 
/**
 * simple function for creating prg pattern. 
 * (Keep state when reloading browser and resends forms etc.) 
 */

function simple_prg (){
    // check to see if we should start prg
    //print_r($_SERVER); die;
    if ($_SERVER['REQUEST_METHOD'] == 'POST'){
        $uniqid = uniqid();
        $_SESSION['post'][$uniqid] = $_POST;
        $_SESSION['REQUEST_URI'] = $_SERVER['REQUEST_URI'];

        header("HTTP/1.1 303 See Other");
        $header = "Location: " . $_SERVER['REDIRECT_URL'] . '?prg=1&uniqid=' . $uniqid;
        header($header);
        die;
    }

    if (!isset($_SESSION['REQUEST_URI'])){
        @$_SESSION['post'] = null;
    } else {
        if (isset($_GET['prg'])){
            $uniqid = $_GET['uniqid'];
            $_POST = @$_SESSION['post'][$uniqid];
        } else {
            @$_SESSION['REQUEST_URI'] = null;
        }
    }
}
// }}}
// {{{ function send_cache_headers()
/**
 * method for sending cache headers when e.g. sending images from db
 */
function send_cache_headers ($expires = null){

    // one month
    if (!$expires) {
        $expires = 60*60*24*30;
    }
    header("Pragma: public");
    header("Cache-Control: maxage=".$expires);
    header('Expires: ' . gmdate('D, d M Y H:i:s', time()+$expires) . ' GMT');
    
}

// }}}
/**
 * function for checking if we need to redirect with 301
 * if param url is not equal to current url, then 
 * we redirect to url given
 * 
 * @param type $url 
 */
function send_301_headers ($url) {
    if ($_SERVER['REQUEST_URI'] != $url) {
        header("HTTP/1.1 301 Moved Permanently");
        header("Location: $url");
        exit;
    }
}

/**
 * function for redirecting to a exact serverneme.
 * e.g. you have www.example.com and example.com as servernames
 * you want only to allow example.com. 
 * call server_recirect('example.com')
 * 
 * @param string $server_redirect server_name to redirect to.  
 */
function server_redirect($server_redirect) {
    if($_SERVER['SERVER_NAME'] != $server_redirect){
        if ($_SERVER['SERVER_PORT'] == 80) {
            $scheme = "http://";
        } else {
            $scheme = "https://";
        }

        $redirect = $scheme . $server_redirect . $_SERVER['REQUEST_URI'];
        header("HTTP/1.1 301 Moved Permanently");
        header("Location: $redirect");
        die();
    }
}
/**
 * function for forcing site into SSL mode. 
 */
function server_force_ssl () {
    if ($_SERVER['SERVER_PORT'] != 443){
        $redirect = "https://" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
        header("HTTP/1.1 301 Moved Permanently");
        header("Location: $redirect");
    }
}

// Mail functions

// {{{ function mail_utf8($to, $subject, $message, $from)

/**
 * function for sending mail
 *
 * @param   string  to whom are we gonna send the email
 * @param   string  the subject of the email
 * @param   string  the message of the email
 * @param   string  from the sender of the email
 * @return  int     1 on success 0 on error
 */
function mail_utf8($to, $subject, $message, $from, $reply_to=null) {

    // prevent injection of other headers by trimming emails
    $reply_to = trim($reply_to); $from = trim ($from);

    // create headers for sending email
    $headers = 'MIME-Version: 1.0' . "\r\n";
    $headers.= 'Content-type: text/plain; charset=UTF-8' . "\r\n";

    $headers.= "From: $from\r\n";
    if (!$reply_to){
        $reply_to = $from;
    }

    $headers.= "Reply-To: $reply_to" . "\r\n";

    $bounce = get_main_ini('site_email_bounce');
    if ($bounce){
        $headers.= "Return-Path: $bounce\r\n";
    }

    $subject = '=?UTF-8?B?'.base64_encode($subject).'?=';
    $message = wordwrap($message, 70);
    
    if (get_main_ini('send_mail')){
        if (isset(register::$vars['coscms_main']['smtp_mail'])){
            $res = mail_smtp ($to, $subject, $message, $from, $reply_to);
        } else {
            if ($bounce){
                $res = mail($to, $subject, $message, $headers, "-f $bounce");
            } else {
                $res = mail($to, $subject, $message, $headers);
            }
        }

        $log = "TO: $to\n";
        $log.= "SUBJECT: $subject\n";
        $log.= "MESSAGE: $message\n";
        $log.= "HEADERS: $headers\n";
        $log.= "RESULT $res\n";

        if (isset(register::$vars['coscms_main']['debug'])){
            $log_file = _COS_PATH . '/logs/coscms.log';
            error_log($log, 3, $log_file);
        }
        return $res;
    } else {
        $log = "\nSending mail to: $to\n";
        $log.= "Subject: $subject\n";
        $log.= "Message: $message\n";
        $log.= "Header: $headers\n";
        $log_file = _COS_PATH . '/logs/coscms.log';
        error_log($log, 3, $log_file);
        return 1;
    }
}

// }}} 
// {{{ function mail_smtp($recipient, $subject, $message, $from, $reply_to)
/**
 * method for sending mails via smtp
 */
function mail_smtp ($recipient, $subject, $message, $from, $reply_to/*$headers = null*/){
    include_once('Mail.php');
    include_once('Mail/mime.php');
    
    $from = register::$vars['coscms_main']['smtp_params_sender'];                                            // Your email address
    $recipient = "<$recipient>";                               // The Recipients name and email address
    //$subject = "Another test Email";                                                // Subject for the email
    //$text = 'This is a text message.';                                      // Text version of the email
    //$html = '<html><body><p>This is a html message!</p></body></html>';      // HTML version of the email
    $crlf = "\n";


    $headers = array(
        'From'          => $from,
        'Return-Path'   => $from,
        'Reply-To'      => $reply_to,
        'Subject'       => $subject,//'=?UTF-8?B?'.base64_encode($subject).'?=',//$subject,
        //Content-type: text/plain; charset=UTF-8'
        'Content-type' => 'text/plain; charset=UTF-8'
    );

    $bounce = get_main_ini('site_email_bounce');
    if ($bounce){
        $headers['Return-Path'] = $bounce;
    }

    // Creating the Mime message
    $mime = new Mail_mime($crlf);

    // Setting the body of the email
    $mime->setTXTBody($message);
    //$mime->setHTMLBody($html);

    // Add an attachment

    //$file = "Hello World!";
    //$file_name = "Hello text.txt";
    //$content_type = "text/plain";
    //$mime->addAttachment ($file, $content_type, $file_name, 0);

    // Set body and headers ready for base mail class
    $body = $mime->get(array('text_charset' => 'utf-8'));
    $headers = $mime->headers($headers);



    // SMTP authentication params
    $smtp_params = array();
    $smtp_params["host"]     = register::$vars['coscms_main']['smtp_params_host']; //"ssl://smtp.gmail.com";
    $smtp_params["port"]     = register::$vars['coscms_main']['smtp_params_port'];
    $smtp_params["auth"]     = true; //register::$vars['coscms_main']['smtp_params_auth'];
    $smtp_params["username"] = register::$vars['coscms_main']['smtp_params_username'];
    $smtp_params["password"] = register::$vars['coscms_main']['smtp_params_password'];

// Sending the email using smtp
    $mail =& Mail::factory("smtp", $smtp_params);
    $result = $mail->send($recipient, $headers, $body);
    return $result;
}
// }}}

/**
 * class for validating most common thing: URL's and emails. 
 * 
 * @package coslib
 * class for validating email and and emailAndDomain
 */
class cosValidate {
    // {{{ public static function email ($email)
    /**
     * method for validating email with php filter_var function
     * @param   string  email
     * @return  boolean 
     */
    public static function email ($email){
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)){
            return false;
        }
        return true;
    }
    // }}}
    // {{{ public static function urlWithFilter($url)
    /**
     * method for validating email with php filter_var function
     * @param   string  email
     * @return  boolean 
     */
    public static function urlWithFilter ($url){
        require_once 'Validate.php';
        if (!filter_var($url, FILTER_VALIDATE_URL)){
            return false;
        }
        return true;
    }
    // }}} 
    // {{{ public static function url ($url)
    /**
     * method for validating url with PEAR::Validate filter 
     * @param   string  email
     * @return  boolean
     */
    public static function url ($url){
        require_once 'Validate.php';
        $schemes = array ('http', 'https');
        if (!Validate::uri($url, array('allowed_schemes' => $schemes))){
            return false;
        }
        return true;
    }
    // }}}
    // {{{ public static function validateEmailAndDomain ($email
    /**
     * method for vaildating the email the emails domain with PEAR:Validate
     * @param   string  email
     * @return  boolean 
     */
    public static function validateEmailAndDomain ($email, $options = null){
        require_once 'Validate.php';

        if (!$options){
            $options = array('check_domain' => 'true');
        }
               
        if (Validate::email($email, $options)) {
            return true;
        }
        return false;
    }
    // }}}
}
/**
 * function for sanitizing a URL
 * from http://chyrp.net/
 * 
 * @deprecated
 * @param string $string
 * @param boolean $force_lowercase 
 * @param boolean $remove_special
 * @return string
 */
function cos_sanitize_url($string, $force_lowercase = true, $remove_special = false) {
    $strip = array("~", "`", "!", "@", "#", "$", "%", "^", "&", "*", "(", ")", "_", "=", "+", "[", "{", "]",
                   "}", "\\", "|", ";", ":", "\"", "'", "&#8216;", "&#8217;", "&#8220;", "&#8221;", "&#8211;", "&#8212;",
                   "â€”", "â€“", "<", ">", "/", "?");
    return $clean = trim(str_replace($strip, "", strip_tags($string)));
    $clean = preg_replace('/\s+/', "-", $clean);
    $clean = ($remove_special) ? preg_replace("/[^a-zA-Z0-9]/", "", $clean) : $clean ;
    
    return ($force_lowercase) ?
        (function_exists('mb_strtolower')) ?
            mb_strtolower($clean, 'UTF-8') :
            strtolower($clean) :
        $clean; 
}

/**
 * simple sanitize function where only thing removed is /
 * in order to not confuse the url
 *  @deprecated
 * @param string $string string to sanitize
 * @return string $string sanitized string
 */
function cos_sanitize_simple_url($string) {
    $strip = array("/", "?", "#");
    return $clean = trim(str_replace($strip, "", htmlentities(strip_tags($string))));

}

/**
 * function for getting name of main configuration file 
 * config/config.ini. 
 * 
 * If in CLI mode the --domain options need to be set in order to fetch
 * the correcgt virtual host. E.g. config/multi/domain/config.ini
 * where domain is the domain flag. 
 * 
 * In normal mode the domain name is checked using $_SERVER['SERVER_NAME'].
 * If this name matches file config/multi/domain/config.ini then this
 * file will be used. 
 * 
 * If file not set it is the normal config/config.ini which will be included. 
 * 
 * @return string filename of config file.  
 */
function get_config_file() {
    // determine host and see if we use virtual hosting
    // where one code base can be used for more virtual hosts.
    // this is set with the domain flag in ./coscli.sh
    if (defined('_COS_CLI')){
        if (isset(register::$vars['domain']) && register::$vars['domain'] != 'default'){
            $config_file = _COS_PATH . "/config/multi/". register::$vars['domain'] . "/config.ini";
        } else {
            $config_file = _COS_PATH . "/config/config.ini";
        }
    } else {
        $virtual_host_dir = _COS_PATH . "/config/multi/$_SERVER[SERVER_NAME]";
        if (file_exists($virtual_host_dir)){
            $config_file = $virtual_host_dir . "/config.ini";
        } else {
            $config_file = _COS_PATH . "/config/config.ini";
        }
    }
    
    return $config_file;
}
/**
 * Function for loading the config file
 * In order for this to work you need to have in your config file:
 *  
 * server_name = "coscms.org"
 * 
 * In order to set settiings for development or stage: 
 * 
 * Add to the [development] or [stage] section the server_name
 * for stage or development, e.g.:
 * 
 * [stage]
 * server_name = "coscms" 
 * 
 * This will be compared to the $_SERVER['SERVER_NAME'] variable
 * and if there is a match the stage settings will override
 * the default settings. Same goes for development
 * 
 * @return void 
 */
function load_config_file () {
    $config_file = get_config_file();
    
    if (!file_exists($config_file)){
        define ("NO_CONFIG_FILE", true);
        return;
    } else {
        register::$vars['coscms_main'] = parse_ini_file($config_file, true);
        if (
            (@register::$vars['coscms_main']['stage']['server_name'] ==
                @$_SERVER['SERVER_NAME'])
                AND !defined('_COS_CLI') )
            {
                // We are on REAL server and exists without
                // adding additional settings for stage or development
                // or CLI mode. 
                return; 
        }
        
        // Test if we are on stage server. 
        // Overwrite register settings with stage settings
        // Note that ini settings for development will
        // NOT take effect on CLI ini settings
        if (isset(register::$vars['coscms_main']['stage'])){
            if (
                (register::$vars['coscms_main']['stage']['server_name'] ==
                    @$_SERVER['SERVER_NAME'])
                    AND !defined('_COS_CLI') )
                {
                
                // we are on development, merge and overwrite normal settings with
                // development settings.
                register::$vars['coscms_main'] =
                array_merge(
                    register::$vars['coscms_main'],
                    register::$vars['coscms_main']['stage']
                );
                return;
            }
        }
        // We are on development server. 
        // Overwrite register settings with development settings
        // Development settings will ALSO be added to CLI
        // ini settings
        if (isset(register::$vars['coscms_main']['development'])){
            if (
                (register::$vars['coscms_main']['development']['server_name'] ==
                    @$_SERVER['SERVER_NAME'])
                    OR defined('_COS_CLI') )
                {
                
                register::$vars['coscms_main'] =
                array_merge(
                    register::$vars['coscms_main'],
                    register::$vars['coscms_main']['development']
                );
            }
        }
    }
}

function get_files_path () {
    $domain = get_main_ini('domain');
    if ($domain == 'default') {
        $files_path = _COS_PATH . "/htdocs/files/default";
    } else {
        $files_path = _COS_PATH . "/htdocs/files/$domain";
    }
    return $files_path;
}

function get_files_web_path ($file) {
    return "/files/" . get_domain() . '/' . $file; 
}

function get_domain () {
    $domain = get_main_ini('domain');
    return $domain;
}
