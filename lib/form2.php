<?php

/**
 * @package coslib
 */


/**
 * ignore for now.
 * @deprecated use html
 */

define ('FORM_SIZE', 20);
define ('FORM_COLS', 60);
define ('FORM_ROWS', 14);


/**
 * @package coslib
 */
class form2 {

    public static $elements = array();
    public static $formDef = null;
    public static $formString = '';
    public static $values = array();

    /** construct
     * @param $formDef array
     */
    function __construct($formDef = null){
        self::reset();
        if (isset($formDef)){
            self::$formDef = $formDef;
        }
    }

    public static function reset(){
        self::$elements = array();
        self::$formDef = null;
        self::$formString = '';
        self::$values = array();
    }

    

    public static function addElement ($name, $params){
        self::$elements[$name] = $params;
        self::$elements[$name]['name'] = $name;
    }

    // add default values. Remember to sanitize, before using this function
    public static function loadValues($values = null){
        if (isset($_POST['submit'])){
            self::$values = $_POST;
        } else if (isset($_GET['submit'])){
            self::$values = $_GET;
        } else {
            if (isset($values)){
                self::$values = $values;
            }
        }
    }

    public static function parseForm(){
        self::$formString = self::addFormStart();
        foreach (self::$elements as $key => $val){
            switch ($val['type']) {
                case 'text':
                    self::$formString.= self::addText($val);
                    break;
                case 'textarea':
                    self::$formString.= self::addTextArea($val);
                    break;
                case 'select':
                    self::$formString.= self::addSelect($val);
                    break;
                case 'submit':
                    self::$formString.= self::addSubmit($val);
                    break;
                default:
                    break;
            }
        }
        self::$formString.= self::addFormEnd();
        return self::$formString;
    }

    public static function addLabel ($val){
        $str = '';
        $str.= "<label for=\"$val[name]\">$val[label]</label><br />\n";
        return $str;
    }

    public static function addSubmit ($val){
        $str = '';
        if (isset($val['label'])){
            $str.= self::addLabel($val);
        } else {
            $str.= "<br />\n";
        }
        $str.="<input type=\"submit\" name=\"$val[name]\" value=\"$val[value]\"><br />\n";
        return $str;
    }

    public static function addText($val){
        $str = '';
        if (isset($val['label'])){
            $str.= self::addLabel($val);
        }
        $str.= "<input type =\"text\" ";
        if (isset($val['name'])){
            $str.= "name = \"$val[name]\" ";
        }

        if (isset($val['size'])){
            $str.= " size = \"$val[size]\" ";
        } else {
            $str.= ' size = "'.FORM_SIZE.'" ';
        }
        //print_r(self::$values);
        if (isset(self::$values[$val['name']])){
            echo $val['value'] = self::$values[$val['name']];
        }

        if (isset($val['value'])){
            $str.= " value = \"$val[value]\" ";
        }

        $str.= " /><br />\n";
        return $str;
        
    }

    public static function addTextArea($val){
        $str = '';
        if (isset($val['label'])){
            $str.= self::addLabel($val);
        }
        $str.= '<textarea ';

        if (isset($val['name'])){
            $str.= "name = \"$val[name]\" ";
        }

        if (isset($val['rows'])){
            $str.= " rows = \"$val[rows]\" ";
        } else {
            $str.= ' rows = "'.FORM_ROWS.'" ';
        }

        if (isset($val['cols'])){
            $str.= " cols = \"$val[cols]\" ";
        } else {
            $str.= ' cols = "'.FORM_COLS.'" ';
        }
        $str.='>';

        if (isset(self::$values[$val['name']])){
            $val['value'] = self::$values[$val['name']];
        }

        if (isset($val['value'])){
            $str.= $val['value'];
        }
        $str.="</textarea><br />\n";
        return $str;

    }

    //function addSelect($name, $rows, $field, $id, $selected=null){
    public static function addSelect($val){
        $str = '';
        if (isset($val['label'])){
            $str.= self::addLabel($val);
        }
        $str.= '<select name="' . $val['name'] . '">'."\n";
        if (isset(self::$values[$val['name']])){
            $val['value'] = self::$values[$val['name']];
        }
        foreach($val['options'] as $option){
            if (isset($val['value']) && ( $option[$val['select_id']] == $val['value'] )){
                $s = ' selected';
            } else {
                $s = '';
            }
            $str.= '<option value="'.$option[$val['select_id']].'"' . $s . '>'.$option[$val['select_name']].'</option>'."\n";
        }
        $str.= "</select><br />\n";
        return $str;
    }

    public static function addFormStart($formDef = null){
        if (isset($formDef)){
            self::$formDef = $formDef;
        }
        $str = '';
        $str.= "<form ";
        if (isset(self::$formDef['id'])){
            $str.= 'id ="' . self::$formDef['id'] . '" ';
        }
        if (isset(self::$formDef['method'])){
            $str.= 'method = "' . self::$formDef['method'] . '" ' ;
        } else {
            $str.= 'method = "post" ';
        }
        if (isset(self::$formDef['enctype'])) {
            $str.= 'enctype="' . self::$formDef['enctype'] . '" ';
        } else {
            $str.= 'enctype="multipart/form-data" ';
        }
        if (isset(self::$formDef['action'])) {
            $str.= 'action="' . self::$formDef['action'] . '" ';
        } else {
            $str.= 'action="" ';
        }

        $str.= ">\n";
        $str.= "<fieldset>\n";
        if (isset(self::$formDef['legend'])){
            $str.= "<legend>" . self::$formDef['legend'] . "</legend>\n";
        }
        return $str;

    }

    public static function addFormEnd(){
        $str = '';
        $str.= "</fieldset>\n";
        $str.= "</form>\n";
        return $str;
    }


    public function test(){

        $user_form = array(
            'id' => 'userform',
            'legend' => 'Create User',
            'method' => 'post',
            'enctype' => 'multipart/form-data');
        $form = new form($user_form);

        $username = array (
            'type' => 'text',
            'label' => 'Enter Username');
        $form->addElement('username', $username);

        include_once "ant/lib/captcha.php";

        $captcha = array (
            'type' => 'text',
            'label' => captcha::createCaptcha());
        $form->addElement('captcha', $captcha);

        $user_notes = array (
            'label' => 'Enter Notes about user',
            'type' => 'textarea');
        $form->addElement('user_notes', $user_notes);

        $options = array(
            array('id' => 3, 'name' => 'test'),
            array('id' => 7, 'name' => 'blabla')
        );

        $form->addElement('select_thing', array (
            'label' => 'Select something',
            'type' => 'select',
            'options' => $options,
            'select_id' => 'id',
            'select_name' => 'name',
            'value' => 7
            )
        );

        $val = array ('value' => 'Send', 'type' => 'submit');
        $form->addElement('submit' , $val);

        $values = array ('username' => 'Anders', 'user_notes' => 'blablalbla sdflsd');
        $form->loadValues($values);

        print $form->parseForm();
    }
}


/**
 * @package coslib
 */
class dbForm extends form2 {

    static $fields = array();
    static $describe = array();
    static $method = null;
    static $table = null;

    function __construct($formDef){
        parent::__construct($formDef);
    }

    // fields to use when creating form
    public static function setFields ($fields){
        self::$fields = $ary;
    }

    // describe table
    public static function useTable($table){
        $db = new db2 ();
        $row = $db->selectQuery("SHOW columns FROM $table");
        $ary = array();
        foreach($row as $key => $val){
            $ary[$val['Field']] = $val;
        }
        self::$describe = $ary;
        self::$table = $table;
    }

    public static function useMethod ($method) {
        self::$method = $method;
    }

    public static function loadValues ($search){
        if (self::$method == 'update'){
            $db = new db2();
            $row = $db->selectOne(self::$table, $search);
            self::$values = $row;
        }
    }

    // get db field as a form field
    public static function addFormDbElement ($field, $params){
        $element = self::$describe[$field];
        $type = self::getType($element['Type']);
        $length = self::getLength($element['Type']);
        if (isset($params['special'])){
            switch ($params['special']) {
                case 'dropdown':
                    $db = new db2();
                    $rows = $db->selectAll($params['table']);
                    $params['options'] = $rows;
                    $params['type'] = 'select';
                    self::addElement($field, $params);
                return;
                break;
            default:
                break;
            }
        }

        switch ($type) {
            case 'varchar':
                $params['type'] = 'text';
                self::addElement($field, $params);
                break;
            case 'text':
                $params['type'] = 'textarea';
                self::addElement($field, $params);
                break;
            case 'int' && $length > 1:
                $params['type'] = 'text';
                self::addElement($field, $params);
                break;
            default:
                break;
        }
    }

    // get db field type
    public static function getType($field){
        $match = array();
        $pattern = "/[a-z]+/";
        preg_match($pattern, $field, $match);

        if (isset($match[0])){
            return $match[0];
        }
    }

    // get db field length
    public static function getLength($field){
        $match = array();
        $pattern ="/[0-9]+/";
        preg_match($pattern, $field, $match);
        if (isset($match[0])){
            return $match[0];
        }
    }


    public function formDbTest (){
        $formDef = array(
            'id' => 'dbForm',
            'legend' => 'Modules',
        );

        $form = new dbForm($formDef);
        $form->useTable('modules');
        $form->useMethod('insert');
        $form->addFormDbElement('module_name', 'edit module name');
        print $form->parseForm();
    }
}

