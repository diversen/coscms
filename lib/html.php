<?php


class HTML {

    public static $values = array();
    public static $formStr = '';
    public static $autoLoadTrigger;

    public static $br = "<br />";

    public static function getStr () {
        
        $str = self::$formStr;
        self::$formStr = '';
        return $str;
    }

    public static function init ($values = array (), $trigger = null) {
        
        if (isset($trigger)) {
            self::$autoLoadTrigger = $trigger;
        }
        
        if (!empty(self::$autoLoadTrigger)){
            $trigger = self::$autoLoadTrigger;
            if (isset($_POST[$trigger])) {
                self::$values = $_POST;
                
                //print_r($_SERVER); die;
            } else if (isset($_GET[$trigger])){
                self::$values = $_GET;
            } else {
                self::$values = $values;
            }
        } 
    }

    public static function disableBr (){
        self::$br = '';
    }

    public static function enableBr (){
        self::$br = "<br />";
    }

    public static function setValues ($values) {
        self::$values = $values;
    }

    public static function formStart (
        $name = 'form', $method ='post', $action = '',
        $enctype = "multipart/form-data") {
        
        $str = "";
        
        //$str.= '<a class="do_hide collpase">Hide</a>&nbsp;';
        //$str.= '<a class="do_show">Show</a>';
        //$str.= "<div class=\"collapse\">\n";      
        $str.= "<form action=\"$action\" method=\"$method\" name=\"$name\" enctype = \"$enctype\">\n";
        $str.= "<fieldset>\n";
        
        
        self::$formStr.= $str;
        return $str;
    }

    public static function legend ($legend, $extra = null){        
        $str = "<legend>$legend";

        $str.= "</legend>\n";
        self::$formStr.= $str;
        return $str;
    }

    public static function formEnd (){
        $str = '';
        $str.= "</fieldset>\n";
        $str.= "</form>\n";
        //$str.= "</div>\n";

        self::$formStr.= $str;
        return $str;
    }

    public static function label ($label_for, $label = '') {
        if ($label_for == 'captcha') {
            // no label for images
            $str = $label. self::$br;
        } else {
            $str = "<label for=\"$label_for\">$label</label>" . self::$br . "\n";
        }
        self::$formStr.= $str;
        return $str;
    }

    public static function setValue ($name, $value){
        if (isset(self::$values[$name])){
            return self::$values[$name];
        } else {
            return '';
        }

    }
    
    public static function hidden ($name, $value = null, $extra = array()){
        $str = self::hiddenClean($name, $value, $extra);
        /*
        if (!isset($value)) {
            $value = self::setValue($name, $value);
        }
        
        $extra = self::parseExtra($extra);
        $str = "<input type=\"hidden\" name=\"$name\" $extra value=\"$value\" />\n";
        */
        self::$formStr.= $str; 
        return $str;
    }
    
    public static function hiddenClean ($name, $value = null, $extra = array()){

        if (!isset($value)) {
            $value = self::setValue($name, $value);
        }
        
        $extra = self::parseExtra($extra);
        $str = "<input type=\"hidden\" name=\"$name\" $extra value=\"$value\" />\n";
        //self::$formStr.= $str;
        return $str;
    }
    

    public static function text ($name, $value = null, $extra = array()){
        $str = self::textClean($name, $value, $extra);
        self::$formStr.= $str;
        return $str;
    }
    
    public static function textClean ($name, $value = null, $extra = array()){
        if (!isset($extra['size'])){
            $extra['size'] = HTML_FORM_TEXT_SIZE;
        }

        if (!isset($value)) {
            $value = self::setValue($name, $value);
        }
        
        $extra = self::parseExtra($extra);
        $str = "<input type=\"text\" name=\"$name\" $extra value=\"$value\" />" . self::$br . "\n";
        return $str;
    }

    public static function simpleCaptcha ($name, $value = '', $extra = array()){
        if (!isset($extra['size'])){
            $extra['size'] = HTML_FORM_TEXT_SIZE;
        }

        $value = self::setValue($name, $value);
        $extra = self::parseExtra($extra);
        $str = "<input type=\"text\" name=\"$name\" $extra value=\"$value\" />" . self::$br . "\n";
        self::$formStr.= $str;
        return $str;
    }

    public static function password ($name, $value = '', $extra = array()){
        if (!isset($extra['size'])){
            $extra['size'] = HTML_FORM_TEXT_SIZE;
        }

        $value = self::setValue($name, $value);
        $extra = self::parseExtra($extra);
        $str = "<input type=\"password\" name=\"$name\" $extra value=\"$value\" />" . self::$br . "\n";
        self::$formStr.= $str;
        return $str;
    }

    public static function textarea ($name, $value = null, $extra = array()){
        if (!isset($extra['rows'])){
            $extra['rows'] = HTML_FORM_TEXTAREA_HT;
        }

        if (!isset($extra['cols'])){
            $extra['cols'] = HTML_FORM_TEXTAREA_WT;
        }

        if (isset($extra['filter_help'])) {
            echo $extra['title'] = get_filters_help($extra['filter_help']);            
        } 
        
        if (!isset($value)) {
            $value = self::setValue($name, $value);
        } 

        //$value = self::setValue($name, $value);
        $extra = self::parseExtra($extra);
        $str =  "<textarea name=\"$name\" $extra>$value</textarea>" . self::$br . "\n";
        self::$formStr.= $str;
        return $str;
    }
    
    public static function textareaSmall ($name, $value = null, $extra = array()){
        if (!isset($extra['rows'])){
            $extra['rows'] = (int)(HTML_FORM_TEXTAREA_HT / 6);
        }

        if (!isset($extra['cols'])){
            $extra['cols'] = HTML_FORM_TEXTAREA_WT;
        }

        if (isset($extra['filter_help'])) {
            echo $extra['title'] = get_filters_help($extra['filter_help']);            
        } 
        
        if (!isset($value)) {
            $value = self::setValue($name, $value);
        } 

        //$value = self::setValue($name, $value);
        $extra = self::parseExtra($extra);
        $str =  "<textarea name=\"$name\" $extra>$value</textarea>" . self::$br . "\n";
        self::$formStr.= $str;
        return $str;
    }

    public static function file ($name, $extra = array()) {
        if (!isset($extra['size'])){
            $extra['size'] = HTML_FORM_TEXT_SIZE;
        }

        //$value = self::setValue($name, $value);
        $extra = self::parseExtra($extra);
        $str = "<input type=\"file\" name=\"$name\" size=\"30\" $extra />\n"  . self::$br . "\n";
        self::$formStr.= $str;
        return $str;
    }

    public static function checkbox ($name, $value = '1', $extra = array ()) {
        $extra = self::parseExtra($extra);
        
        $value = self::setValue($name, $value);
        if ($value){
            $extra.= " checked=\"yes\" ";
        }

        $str = "<input type=\"checkbox\" name=\"$name\" value=\"$value\" $extra />" . self::$br . "\n";
        self::$formStr.= $str ;
        return $str;
    }

    public static function submit ($name, $value, $extra = array ()) {
        $extra = self::parseExtra($extra);
        $str =  "<input type=\"submit\" $extra name=\"$name\" value=\"$value\" />" . self::$br . "";
        self::$formStr.= $str ;
        return $str;
    }
    
    public static function addHtml ($str) {
        self::$formStr.= $str;
    }

    public static function parseExtra ($extra = array()) {
        $str = '';
        if (empty($extra)) return '';
        
        foreach ($extra as $key => $val){
            $str.= " $key = \"$val\" ";
        }
        return $str;
    }


    /**
     * method for making a drop down box.
     * 
     * @param   string  $name the name of the select field
     * @param   array   $rows the rows making up the ids and names of the select field
     * @param   string  $field array field which will be used as name of the select element
     * @param   int     $id the array field which will be used as id of the select element
     * @param   int     $selected the element which will be selected
     * @return  string  $extras to be added to a form
     */
    public static function select($name, $rows, $field, $id, $value=null, $extra = array(), $init = array()){        
        $dropdown = self::selectClean($name, $rows, $field, $id, $value, $extra, $init);
        /*
        $extra = self::parseExtra($extra);
        $dropdown = "<select name=\"$name\" $extra";

        if (!isset($value)) {
            $value = self::setValue($name, $value);
        }
        $dropdown.= ">\n";
        if (!empty($init)) {
            $dropdown.= '<option value="'.$init[$id].'"' . '' . '>'.$init[$field].'</option>'."\n";
        }
        
        foreach($rows as $row){
            if ($row[$id] == $value){
                $s = ' selected';
            } else {
                $s = '';
            }

            $dropdown .= '<option value="'.$row[$id].'"' . $s . '>'.$row[$field].'</option>'."\n";
        }
         * 
         */
        //$dropdown .= '</select>'. self::$br . "\n";
        self::$formStr.= $dropdown . self::$br . "\n" ;
        return $dropdown;
        //return $dropdown;
    }
    
    /**
     * method for making a drop down box.
     * 
     * @param   string  $name the name of the select field
     * @param   array   $rows the rows making up the ids and names of the select field
     * @param   string  $field array field which will be used as name of the select element
     * @param   int     $id the array field which will be used as id of the select element
     * @param   int     $selected the element which will be selected
     * @return  string  $extras to be added to a form
     */
    public static function selectClean($name, $rows, $field, $id, $value=null, $extra = array(), $init = array()){        
        $extra = self::parseExtra($extra);
        $dropdown = "<select name=\"$name\" $extra";

        if (!isset($value)) {
            $value = self::setValue($name, $value);
        }
        $dropdown.= ">\n";
        if (!empty($init)) {
            $dropdown.= '<option value="'.$init[$id].'"' . '' . '>'.$init[$field].'</option>'."\n";
        }
        
        foreach($rows as $row){
            if ($row[$id] == $value){
                $s = ' selected';
            } else {
                $s = '';
            }

            $dropdown .= '<option value="'.$row[$id].'"' . $s . '>'.$row[$field].'</option>'."\n";
        }
        $dropdown .= "</select>\n";
        //self::$formStr.= $dropdown ;
        return $dropdown;
        //return $dropdown;
    }

    public static function createLink ($url, $title, $options = array()) {
        $rewritten_url = self::getUrl($url);

        // if rewritten
        if ($rewritten_url != $url) {
            $orginal = self::getUrl($rewritten_url);
            if ($orginal == $_SERVER['REQUEST_URI']){
                if (!isset($options['class'])){
                    $options['class'] = 'current';
                }
            }
        }

        $url = $rewritten_url;
        if ($_SERVER['REQUEST_URI'] == $url){
            if (!isset($options['class'])){
                $options['class'] = 'current';
            }
        } 

        $options = self::parseExtra($options);
        $str = "<a href=\"$url\" $options>$title</a>";
        return $str;
    }

    /**
     *
     * @param  string $url
     * @return string $url rewritten is rewrite url exists
     */
    public static function getUrl ($url) {
       if (class_exists('rewrite_manip')) {
            $alt_uri = rewrite_manip::getRowFromRequest(html::entitiesDecode(rawurldecode($url)));
            if (isset($alt_uri)){
                $url = $alt_uri;
            }
        }
        return $url;
    }

    public static function createImage ($src, $options = array()) {
        $options = self::parseExtra($options);
        $str = "<img src=\"$src\" $options />";
        return $str;
    }
    
    public static function createHrefImage($url, $options = array(), $href = null){
        $str = self::createImage($src, $options);
        return "<a href=\"$href\">$str</a>";
    }



    public static function specialEncode(&$values){
        if (is_array($values)){
            foreach($values as $key => $val){
                if (is_array($val)) {
                    $values[$key] = self::specialEncode($val);
                } else {
                    $values[$key] = htmlspecialchars($val, ENT_COMPAT);
                }
            }
        } else if (is_string($values)) {
            $values =  htmlspecialchars($values, ENT_COMPAT);
        } else {
            $values = '';
        }
        return $values;
    }

    public static function specialDecode(&$values){
        if (is_array($values)){
            foreach($values as $key => $val){
                if (is_array($val)) {
                    $values[$key] = self::specialDecode($val);
                } else {
                    $values[$key] = htmlspecialchars_decode($val, ENT_COMPAT);
                }
            }
        } else if (is_string($values)) {
            $values =  htmlspecialchars_decode($values, ENT_COMPAT);
        } else {
            $values = '';
        }
        return $values;
    }


    public static function entitiesEncode(&$values){
        if (is_array($values)){
            foreach($values as $key => $val){
                if (is_array($val)) {
                    $values[$key] = self::entitiesEncode($val);
                } else {
                    $values[$key] = htmlentities($val, ENT_COMPAT, 'UTF-8');
                }
            }
        } else if (is_string($values)) {
            $values =  htmlentities($values, ENT_COMPAT, 'UTF-8');
        } else {
            $values = '';
        }
        return $values;
    }

    public static function entitiesDecode(&$values){
        if (is_array($values)){
            foreach($values as $key => $val){
                if (is_array($val)) {
                    $values[$key] = self::entitiesDecode($val);
                } else {
                    $values[$key] = html_entity_decode($val, ENT_COMPAT, 'UTF-8');
                }
            }
        } else if (is_string($values)) {
            $values =  html_entity_decode($values, ENT_COMPAT, 'UTF-8');
        } else {
            $values = '';
        }
        return $values;
    }

    public static function widget ($class, $method, $name = null, $value = null){
        include_module ($class);
        $value = self::setValue($name, $value);
        $str = $class::$method($name, $value);
        self::$formStr.= $str ;
        return $str;
    }

    public static function errors ($errors) {
            if (function_exists('template_view_errors')) {
                template_view_errors($errors);
                return;
            }
            if (is_string($errors)){
                echo "<!-- view_error -->\n";
                echo "<div class=\"form_error\">\n";
                echo "<p>$message</p></div>\n";
                return;
            }
            echo "<!-- view_form_errors -->\n";
            echo "<div class=\"form_error\"><ul>\n";
            foreach($errors as $error){
                echo "<li>$error</li>\n";
            }
            echo "</ul></div>\n";
            echo "<!-- / end form_error -->\n";
            return;
    }
    
    /**
     * method for sanitizing a url real simple
     * remove / ? # - add entites for displaying the url in a link
     * without any dangers
     * @param string $url
     * @return string $url
     */
    public static function sanitizeUrlSimple ($string) {
        $strip = array('/', '?', '#');
        $sub = array ('', '', '');
        $clean = trim(str_replace($strip, $sub, strip_tags($string)));
        return $clean;
        
    }
    /*
    public static function getUl ($elements, $value_field, $options = null){
        if ($options) {
            $options = self::parseExtra($options);
        } else {
            $options = '';
        }
        $str = "<ul" . $options . ">\n";
        foreach ($elements as $key => $val) {
            $str.="<li>$val[$value_field]</li>\n";
        }
        $str.= "</ul>\n";
        return $str;
    }*/
}
