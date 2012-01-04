<?php

/**
 * File contains helper functions. 
 * 
 *
 * @package    coslib
 */

/**
 * puts a string in logs/coscms.log
 * @param string $message
 */
function cos_error_log ($message, $write_file = true) {
    $message = strftime('%c', time()) . ": " . $message;
    $message.="\n";
    
    if ($write_file) {
        $destination = _COS_PATH . "/logs/coscms.log";
        error_log($message, 3, $destination);
    }
}

// Variable function

/**
 * function for checking if var is an int larger and than zero
 * you can also set a max limit for the integer. 
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

/**
 * trims a string
 * @param string $value 
 */
function trim_value(&$value){ 
    $value = trim($value); 
}

/**
 * trims an array
 * @param array $ary the array to be trimmed
 * @return array $ary the trimmed array 
 */
function trim_array ($ary) {
    array_walk($ary, 'trim_value');
    return $ary;
}

/**
 * checks if a var is set and if it is equal to another var
 * @param mixed $var the var to check
 * @param mixed $val the value you want to check for
 * @return boolean $res true on success and false on failure
 */
function isset_and_equal ($var, $val) {
    if (isset($var)) {
        if ($var == $val) {
            return true;
        }
    }
    return false;
}

/**
 * function for rewriting htmlentities for safe display on screen.
 *
 * @param   array|string    $value value(s) to transform
 * @return  array|string    $value value(s) transformed
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
 * function for decoding htmlentities
 *
 * @param   array|string   $values value(s) to transform
 * @return  array|string   $values value(s) transformed
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

/**
 * function for encoding htmlspecialchars for safe display on screen
 *
 * @param   array|string    $values value(s) to encode
 * @return  array|string    $values value(s) encoded
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

/**
 * function used for checking if something has isset and at the same 
 * time is not empty
 *
 * @param   mixed $var the var to check
 * @return  boolean $res boolean true on success and false on failure
 */
function isvalue($var){
    if (isset($var) && !empty($var)){
        return true;
    }
    return false;
}

/**
 * function for get the value of a print_r statement whithout printing 
 * to the screen. 
 * @param mixed $var the var to run print_r on
 * @return strng $var the vaiable as a string
 */
function print_r_str ($var){
    ob_start();
    print_r($var);
    $var = ob_get_contents();
    ob_end_clean();
    return $var;
}

/** 
 * Substring without losing word meaning and
 * tiny words (length 3 by default) are included on the result.
 *  "..." is added if result do not reach original string length
 * Found on php.net
 *
 * @param   string  $str string to operate on
 * @param   int     $length the maxsize of the string to return
 * @param   int     $minword minimum size of word to cut from
 * @return  string  $str the substringed string
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

/**
 * function for removing extra white space, and only have 'one space' left
 * @param string $str the string to operate on
 * @return string $str the transformed string 
 */
function cos_remove_extra_ws ($str) {
    $str = preg_replace('/\s\s+/', ' ', $str);
    return $str;
}

/**
 * simple method for saving $_POST vars to session
 * @param   string  $id the id of the saved <code>$_POST</code> 
 *                  used when retriving the <code>$_POST</code>
 */
function save_post ($id){
     $_SESSION[$id] = $_POST;
}

/**
 * method for loading <code>$_POST</code> vars from session
 * @param   string  $id id of the post to load. 
 * @return  boolean $res true on success and false if no session var was 
 *                  found with the given id
 */
function load_post($id){
    if (!isset($_SESSION[$id])) {
        return false;
    }
    $_POST = $_SESSION[$id];
    return true;
}

/**
 * get a session var from id. 
 * @param mixed $id the id of the session var to fetch
 * @return mixed $res the var which was set or false 
 */
function get_post($id) {
    if (!isset($_SESSION[$id])) {
        return false;
    }
    return $_SESSION[$id];
}

/**
 * function for unsetting a session var
 * @param type $id the id of the session var
 */
function unset_post ($id) {
    unset($_SESSION[$id]);
}

/**
 * function for urlencoding a utf8 encoding a string
 * @param   string  $string the utf8 string to encode
 * @return  string  $string the utf8 encoded string
 */
function cos_url_encode($string){
    return urlencode(utf8_encode($string));
}

/**
 * function for urldecoding a utf8 decodeding a string
 * @param   string  $string the string to decode
 * @return  string  $string the urldecoded and utf8 decoded string
 */
function cos_url_decode($string){
    return utf8_decode(urldecode($string));
}
// }}}

/**
 * function for getting a file list of a directory (. and .. will not be
 * collected)
 *
 * @param   string  the path to the directory where we want to create a filelist
 * @param   array   if <code>$options['dir_only']</code> isset only return directories.
 *                  if <code>$options['search']</code> isset then only files containing
 *                  search string will be returned
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

/**
 * function for getting a file list recursive
 * @param string $start_dir the directory where we start
 * @param string $pattern a given fnmatch() pattern
 * return array $ary an array with the files found. 
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

/**
 * function for including a templates function file, which is always placed in
 * /templates/template_name/common.inc
 * @param string $template the template name which we want to load.  
 */
function include_template_inc ($template){
    include_once _COS_PATH . "/htdocs/templates/$template/common.inc";
}

/**
 * function for including a compleate module
 * with configuration, view, language, and model file
 *
 * @param   string  $module the name of the module to include
 */
function include_module($module){

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

/**
 * function for including the model file only
 * @param   string   $module the module where the model file exists 
 *                   e.g. (content/article)
 */
function include_model($module){
    $module_path = 'modules/' . $module;
    $ary = explode('/', $module);
    $last = array_pop($ary);
    $model_file = $module_path . '/' . "model.$last.inc";
    include_once $model_file;
}

/**
 * function for including a view file.
 * Maps to module (e.g. 'tags' and 'view file' e.g. 'add')
 * we presume that views are placed in modules views folder
 * e.g. tags/views And we presume that views always has a .inc
 * postfix
 *
 * @param string $module the module where our view exists
 * @param string $file the view file we want to use
 * @param mixed $vars vars to substitue in view
 * @param boolean $return if true we will return the content of the view
 *                        if false we echo the view
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

/**
 * function for including a controller
 * @param string    $controller the controller to include (e.g. content/article/add)
 */
function include_controller($controller){
    $module_path = register::$vars['coscms_base']  . '/modules/' . $controller;
    $controller_file = $module_path . '.php';
    include_once $controller_file;
}

/**
 * function for including a filter module
 * @param   array|string   $filter string or array of string with 
 *                         filters to include
 *
 */
function include_filters ($filter){
    static $loaded = array();

    if (!is_array($filter)){
        $class_path = _COS_PATH . "/modules/filter_$filter/$filter.inc";
        include_once $class_path;
        moduleLoader::setModuleIniSettings("filter_$filter");
        $loaded[$filter] = true;
    }

    if (is_array ($filter)){
        foreach($filter as $key => $val){
            if (isset($loaded[$val])) continue;
            $class_path = _COS_PATH . "/modules/filter_$val/$val.inc";
            include_once $class_path;
            moduleLoader::setModuleIniSettings("filter_$val");
            $loaded[$val] = true;
        }
    }
}

/**
 * function for getting filters help string
 * @param string|array $filters the filter or filters from were we wnat to get
 *                     help strings
 * @return string $string the help strings of all filters. 
 */
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

/**
 * function for filtering content
 * @param  string|array    $filters the string or array of filters to use
 * @param  string|array    $content the string or array (to use filters on)
 * @return string|array    $content the filtered string or array of strings
 */
function get_filtered_content ($filter, $content){   
    if (!is_array($filter)){
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

/**
 * method for getting a module ini settings
 * @param string $key the key of the ini settng to get 
 * @return mixed $value the value of the setting or null if no value was found
 */
function get_module_ini($key){
    if (!isset(register::$vars['coscms_main']['module'][$key])){
        return null;
    }
    if (register::$vars['coscms_main']['module'][$key] == '0'){
        return null;
    }
    return register::$vars['coscms_main']['module'][$key];
}

/**
 * method for getting a main ini setting found in config/config.ini
 * @param   string  $key the ini setting key to get
 * @return  mixed   $val the value of the setting or null if not found. 
 *                       If 0 is found we also reutnr null
 */
function get_main_ini($key){
    if (!isset(register::$vars['coscms_main'][$key])){
        return null;
    }
    if (register::$vars['coscms_main'][$key] == '0'){
        return null;
    }
    return register::$vars['coscms_main'][$key];
}

/**
 * function for getting content from a file
 * used as a very simple template function
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

/**
 * method for getting a path to a module
 *
 * @param   string  $module the module
 * @return  string  $path the module path
 */
function get_module_path ($module){
    return _COS_PATH . '/modules/' . $module;
}

// HTML and HTTP function

// {{{ function create_link($url, $title, $description)
/**
 * function for creating a link
 * @deprecated use html::createLink 
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
 * @deprecated see html::createHrefImage()
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
 * @deprecated see html::createImage($src)
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

/**
 * function for creating a select dropdown from a database table.
 * @deprecated see html::select()
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

/**
 * @deprecated see html::select()
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

/**
 * Gets user profile link if a profile system is in place.
 * Profile systems must be set in main config/config.ini
 * the option array can be used to setting special options for profile module
 * @param   array|int   $user user_id or full account row 
 * @param   array   $options options to use with profile system
 * @return  string  $str string with html showing the profile
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

/**
 * Gets user profile link if a profile system is in place.
 * Profile systems must be set in main config/config.ini
 * the option array can be used to setting special options for profile module
 * 
 * @param   array   $user_id the user in question
 * @return  string  $string string showing the profile
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
 * @param int $id user_id 
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

/**
 * simple function for creating prg pattern. 
 * (Keep state when reloading browser and resends forms etc.) 
 */
function simple_prg (){
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

/**
 * 
 * method for sending cache headers when e.g. sending images from db
 * @param int $expires the expire time in seconds
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

/**
 * send a location header
 * @param type $location the location, e.g. /content/view/article/3
 * @param type $message an action message 
 * @param type $post_id if an post id is set we save the post in a session.
 */
function send_location_header ($location, $message = null, $post_id = null) {
    if (isset($message)) {
        session::setActionMessage($message);
    }
    
    if (isset($post_id)) {
        save_post($post_id);
    }
    
    $header = "Location: $location";
    header($header);
    exit;
}

/**
 * function for checking if we need to redirect with 301
 * if param url is not equal to current url, then 
 * we redirect to url given
 * 
 * @param string $url the rul to check against and redirect to.  
 */
function send_301_headers ($url, $options = array()) {
    if (isset($options['message'])) {
        session::setActionMessage($options['message']);
    }
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
        die();
    }
}

// Mail functions

/**
 * function for sending utf8 mails with native mail function
 *
 * @param   string  $recipient to whom are we gonna send the email
 * @param   string  $subject the subject of the email
 * @param   string  $message the message of the email
 * @param   string  $from from the sender of the email
 * @param   string  $reply_to email to reply to
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
            cos_error_log($log, 3, $log_file);
        }
        return $res;
    } else {
        $log = "\nSending mail to: $to\n";
        $log.= "Subject: $subject\n";
        $log.= "Message: $message\n";
        $log.= "Header: $headers\n";
        $log_file = _COS_PATH . '/logs/coscms.log';
        cos_error_log($log, 3, $log_file);
        return 1;
    }
}

/**
 * method for sending mails via smtp
 * @param   string  $recipient to whom are we gonna send the email
 * @param   string  $subject the subject of the email
 * @param   string  $message the message of the email
 * @param   string  $from from the sender of the email
 * @param   string  $reply_to email to reply to
 * @return  int     $res 1 on success 0 on error
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

/**
 * class for validating most common thing: URL's and emails. 
 * 
 * @package coslib
 * class for validating email and and emailAndDomain
 */
class cosValidate {
    /**
     * method for validating email with php filter_var function
     * @param   string  $email email
     * @return  boolean $res true on success and false on failure 
     */
    public static function email ($email){
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)){
            return false;
        }
        return true;
    }

    /**
     * method for validating email with php filter_var function
     * @param   string  $url the url to validate
     * @return  boolean $res true on success and false on failure
     */
    public static function urlWithFilter ($url){
        require_once 'Validate.php';
        if (!filter_var($url, FILTER_VALIDATE_URL)){
            return false;
        }
        return true;
    }

    /**
     * method for validating url with PEAR::Validate filter 
     * @param   string  $url the url to validate
     * @return  boolean $res true on success and false on failure
     */
    public static function url ($url){
        require_once 'Validate.php';
        $schemes = array ('http', 'https');
        if (!Validate::uri($url, array('allowed_schemes' => $schemes))){
            return false;
        }
        return true;
    }

    /**
     * method for vaildating email and an emails domain with PEAR:Validate
     * @param   string  $email the email to validate email
     * @param array $options set some options
     * @return  boolean $res true on success and false on failure 
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
}
/**
 * function for sanitizing a URL
 * from http://chyrp.net/
 * 
 * @deprecated
 * @param string $string
 * @param boolean $force_lowercase 
 * @param boolean $remove_special
 * @return string $str the sanitized string 
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
 * @param string $string string to sanitize
 * @return string $string sanitized string
 */
function cos_sanitize_simple_url($string) {
    $strip = array("/", "?", "#");
    return $clean = trim(str_replace($strip, "", htmlspecialchars(strip_tags($string))));

}

function parse_ini_file_ext ($file, $sections = null) {
    ob_start();
    include $file;
    $str = ob_get_contents();
    ob_end_clean();
    return parse_ini_string($str, $sections);
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
 * @return string $filename the filname of the config file.  
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
 */
function load_config_file () {
    $config_file = get_config_file();
    
    if (!file_exists($config_file)){
        define ("NO_CONFIG_FILE", true);
        return;
    } else {
        register::$vars['coscms_main'] = parse_ini_file_ext($config_file, true);
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

/**
 * function for getting a full path to public files folder when doing e.g. uploads
 * @return string $files_path the full file path 
 */
function get_files_path () {
    $domain = get_main_ini('domain');
    if ($domain == 'default') {
        $files_path = _COS_PATH . "/htdocs/files/default";
    } else {
        $files_path = _COS_PATH . "/htdocs/files/$domain";
    }
    return $files_path;
}

/**
 * method for getting the web path to files folder. 
 * @param string $file the file to get path from
 * @return string $path the web path to the file
 */
function get_files_web_path ($file) {
    return "/files/" . get_domain() . $file; 
}

/**
 * method for getting domain. 
 * @return string $domain the current domain
 */
function get_domain () {
    $domain = get_main_ini('domain');
    return $domain;
}
