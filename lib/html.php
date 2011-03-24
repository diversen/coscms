<?php


class HTML {

    public static $values = array();
    public static $formStr = '';
    public static $autoLoadTrigger;

    public static $br = "<br />";

    public static function init ($values = array ()) {

        if (!empty(self::$autoLoadTrigger)){
            $trigger = self::$autoLoadTrigger;
            if (isset($_POST[$trigger])) {
                self::$values = $_POST;
            } else if (isset($_GET[$trigger])){
                self::$values = $_GET;
            } else {
                self::$values = $values;
            }
        } 
    }

    public static function disableBr (){
        echo self::$br = '';
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
        $str = "<form action=\"$action\" method=\"$method\" name=\"$name\" enctype = \"$enctype\">\n";
        $str.= "<fieldset>\n";
        self::$formStr.= $str;
        return $str;
    }

    public static function legend ($legend){
        $str = "<legend>$legend</legend>\n";
        self::$formStr.= $str;
        return $str;
    }

    public static function formEnd (){
        $str = "</fieldset></form>\n";
        self::$formStr.= $str;
        return $str;
    }

    public static function label ($label_for, $label) {
        $str = "<label for=\"$label_for\">$label</label>" . self::$br . "\n";
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

    public static function text ($name, $value = '', $extra = array()){
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

    public static function textarea ($name, $value = '', $extra = array()){
        if (!isset($extra['rows'])){
            $extra['rows'] = HTML_FORM_TEXTAREA_HT;
        }

        if (!isset($extra['cols'])){
            $extra['cols'] = HTML_FORM_TEXTAREA_WT;
        }

        $value = self::setValue($name, $value);
        $extra = self::parseExtra($extra);
        $str =  "<textarea name=\"$name\" $extra>$value</textarea>" . self::$br . "\n";
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

    public static function parseExtra ($extra) {
        $str = '';
        
        foreach ($extra as $key => $val){
            $str.= " $key = \"$val\" ";
        }
        return $str;
    }

    public static function createLink ($url, $title, $options = array()) {
        $options = self::parseExtra($options);
        $str = "<a href=\"$url\" $options>$title</a>";
        return $str;

    }

    public static function widget ($class, $method){
        include_module ($class);
        $str = $class::$method();
        self::$formStr.= $str ;
        return $str;
    }
}
