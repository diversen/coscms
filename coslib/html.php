<?php



/**
 * File containing class for building forms and common methods used 
 * when building forms.
 * 
 * @package html 
 */

/**
 * @ignore
 */
include_once "coslib/upload.php";

/**
 * Class used when building forms and various methods used when creating forms
 * for escaping in different ways. 
 * @package html
 */

class html {

    /**
     * array holding values used when creating forms
     * @var array $values the array of values
     */
    public static $values = array();
    
    /**
     * string holding a form  being built. 
     * @var string $formStr the form string
     */
    public static $formStr = '';
    
    /**
     * string holding a submit action which will trigger form to
     * use values being submitted
     * @var string  $autoLoadTrigger the autoLoadTrigger string
     */
    public static $autoLoadTrigger;

    /**
     * default string to seperate form fields with
     * @var string $br break string
     */
    public static $br = "<br />";
    
    /**
     * var holding all fields of a form
     * @var array $fields
     */
    public static $fields = array();
    
    /**
     * var holding internal setting, e.g. about MAX_FILE_SIZE
     * to be used across fields
     */
    public static $internal = array();
    
    /**
     * flag to indicate if we auto encode special chars
     * @var boolean  
     */
    public static $autoEncode = false;
    
    /**
     * flag to indicate if we do uploads
     * @var boolean 
     */
    public static $doUpload = false;
    
    /**
     * method for getting form string build. 
     * @return string $str the form build
     */
    public static function getStr () {
        $str = '';
        foreach (self::$fields as $key => $value) {
            $str.= $value['value'];
        }
        self::$fields = array();
        return $str;
    }

    /**
     * method for initing a form
     * @param array $values initial values for the form e.g. 
     *              array ('name' => 'test')
     * @param string $trigger the trigger value which tells the object to
     *               stop using the initial set values. Normal 'submit' as
     *               this is normal the name of submit buttom
     */
    public static function init ($values = array (), $trigger = null) {        
        
        if (isset($trigger)) {
            self::$autoLoadTrigger = $trigger;
        }
        
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
        
        if (self::$autoEncode) {
            self::$values = html::specialEncode(self::$values);
        }       
    }
    

    
    /**
     * sets auto encode to a value
     * @param boolean $val true or false 
     */
    public function setAutoEncode($val = true) {
        self::$autoEncode = $val;
    }

    /**
     * method for disabling break between form elements
     */
    public static function disableBr (){
        self::$br = '';
    }

    /**
     * method for enabling breaks between form elements
     */
    public static function enableBr (){
        self::$br = "<br />";
    }
    
    /**
     * method for setting form values
     * @param array $values 
     */
    public static function setValues ($values) {
        self::$values = $values;
    }
    
    /**
     * method for starting a html form with options as an array
     * @param array $options array ('name' => 'test_fomr', 'enctype'=> ...
     *                              'class => 'this form', 'id => 'bla');
     * @return string $str the form start 
     */
    public static function formStartAry (
        $options = array ()) {
        if (!isset($options['enctype'])) {
            $options['enctype'] = 'multipart/form-data';
        } 
        
        if (!isset($options['name'])) {
            $options['name'] = 'form';
        }
        
        if (!isset($options['id'])) {
            $options['id'] = 'form';
        }
        
        if (!isset($options['accept-charset'])) {
            $options['accept-charset'] = 'utf-8';
        }
        
        //accept-charset
        
        self::$internal['form_id'] = $options['id'];

        if (!isset($options['method'])) {
            $options['method'] = 'post';
        }
        
        // get all extra attr except action
        $extra = self::parseExtra($options);

        // get action
        $extra.= self::getActionFromAry($options);
        
        $str = '';  
        $str.= "<form $extra>\n";
        $str.= "<fieldset>\n";
        
        self::$fields[] = array ('value' => $str);
        return $str;
    }

    
    /**
     * get default action attr
     * @param array|string|null $options
     * @return string $action
     */
    public static function getActionFromAry ($options = null) {
        
        // check any special storage
        $storage = config::getMainIni('file_storage');
        
        // google cloud storage
        if ($storage == 'gcs' && self::$doUpload == true) {
            $gcs = new upload_gcs();
            $action = $gcs->createUploadUrl($options);
            return "action =\"$action\"";
            
        }
        
        if (!isset($options['action'])) {
            $action = "action =\"#!\"";
        } else {
            $action = "action =\"$options[action]\"";
        }
        
        return $action;
    }

   
    /**
     * method for starting a html form
     * @param string $name name of the form
     * @param string $method method of the form
     * @param string $action action of the form
     * @param string $enctype enctype of the form
     * @param array $options
     * @return string $str the form start 
     */
    public static function formStart (
        $name = 'form', $method ='post', $action = '#!',
        $enctype = "multipart/form-data", $options = array()) {
        
        if (!$name) {
            $name = 'form';
        }
        
        if (!$method) {
            $method = 'post';
        }
        
        if (!$enctype) {
            $enctype = 'multipart/form-data';
        }
        
        if (!$options) {
            $options = array ();
        }
        
        $str = self::formStartClean(
                $name, 
                $method, 
                $action, 
                $enctype, 
                $options);
        
        self::$fields[] = array ('value' => $str);
    }
    
    /**
     * method for starting a clean html form where we get the string
     * @param string $name name of the form
     * @param string $method method of the form
     * @param string $action action of the form
     * @param string $enctype enctype of the form
     * @param array $options
     * @return string $str the form start 
     */
    public static function formStartClean (
        $name = 'form', $method ='post', $action = '#!',
        $enctype = "multipart/form-data", $options = array()) {
        
        if (!isset($options['id'])) {
            $options['id'] = $name;
        }
        self::$internal['form_id'] = $options['id'];
        $extra = self::parseExtra($options);

        
        $str = "";   
        $str.= "<form action=\"$action\" method=\"$method\" name=\"$name\" $extra enctype = \"$enctype\">\n";
        $str.= "<fieldset>\n";

        return $str;
    }
    
    /**
     * method for setting a legend on the form
     * @param string $legend the title of the legend
     * @param array $extra extra options to add to the legend e.g. 
     *             array ('class' => 'table-top and-more')
     * @return string $str the legend string. 
     */
    public static function legend ($legend, $extra = null){        
        $str = self::legendClean($legend, $extra);
        self::$fields[] = array ('value' => $str);
        //return $str;
    }
    
    /**
     * method for setting a legend on the form
     * @param string $legend the title of the legend
     * @param array $extra extra options to add to the legend e.g. 
     *             array ('class' => 'table-top and-more')
     * @return string $str the legend string. 
     */
    public static function legendClean ($legend, $extra = null){        
        $str = "<legend>$legend";
        $str.= "</legend>\n";
        self::$fields[] = array ('value' => $str);
        //return $str;
    }

    /**
     * method for ending a form with the </form> tag
     * @return string $str the form end element
     */
    public static function formEnd (){
        $str = self::formEndClean();
        self::$fields[] = array('value' => $str);
    }
    
    /**
     * method for ending a form with the </form> tag
     * @return string $str the form end element
     */
    public static function formEndClean (){
        $str = '';
        $str.= "</fieldset>\n";
        $str.= "</form>\n";
        return $str;
    }

    /**
     * sets a label for a form element
     * @param string $label_for the field to set the label for
     * @param string $label the label text
     * @param array $options
     * @return string $str the label
     */
    public static function label ($label_for, $label = '', $options = array()) {
        $str = self::labelClean($label_for, $label, $options);
        self::$fields[] = array('value' => $str);
    }
    
    /**
     * method for getting label as a string
     * @param type $label_for
     * @param string $label
     * @param type $options
     * @return string 
     */
    public static function labelClean ($label_for, $label = '', $options = array()) {
        if (isset($options['required'])) {
            $label = "* " . $label;
            unset($options['required']);
        }
        
        $options = self::parseExtra($options);
        if ($label_for == 'captcha') {
            // no label for captcha images
            $str = $label. self::$br;
        } else {
            $str = "<label for=\"$label_for\" $options>$label</label>" . self::$br . "\n";
        }
        
        
        return $str;
    }

    /** 
     * method for setting a value in a field
     * @access private
     * @param string $name the form field
     * @param string $value the value of the form field
     * @return string 
     */
    public static function setValue ($name, $value){
        // submission. Use submitted vlaue
        if (isset(self::$values[$name]) ){
            return self::$values[$name];
        }    
        
        // checkboxes. 
        $trigger = self::$autoLoadTrigger;
        if (!isset(self::$values[$name]) && isset($_POST[$trigger])) {
            return null;
        }
        if (!isset(self::$values[$name]) && isset($_GET[$trigger])) {
            return null;
        }
        
        // return initial
        return $value;
    }
    
     
    /**
     * sets a hidden element in a form. 
     * @param string $name name of the field
     * @param string $value value of the field
     * @param array  $extra elements of the field e.g. 
     *               array ('class' => 'css-test and-more')
     * @return string the hidden element (adds the element to the static form str.)  
     */
    public static function hidden ($name, $value = null, $extra = array()){        
        $str = self::hiddenClean($name, $value, $extra);
        self::$fields[] = array ('value' => $str);
    }
    
    /**
     * gets a hidden field
     * @param string $name name of the field
     * @param string $value value of the field
     * @param array $extra
     * @return string $str the hidden form field 
     */
    public static function hiddenClean ($name, $value = null, $extra = array()){

        if (!isset($value)) {
            $value = self::setValue($name, $value);
        }
        
        if ($name == 'MAX_FILE_SIZE') {
            self::$internal['max_file_size'] = $value;
        }
        
        $extra = self::parseExtra($extra);
        $str = "<input type=\"hidden\" name=\"$name\" $extra value=\"$value\" />\n";
        
        return $str;
    }
    
    /**
     * sets a text field in a form
     * @param string $name the name
     * @param string $value the value
     * @param string $extra extra e.g. array ('class' => 'css and-more')
     * @return string the text field (sets the text field in static form string)
     */
    public static function text ($name, $value = null, $extra = array()){
        $str = self::textClean($name, $value, $extra);
        self::$fields[] = array ('value' => $str);
    }
    
    /**
     * gets a text field as string without adding to the static form str
     * @param string $name name of the element
     * @param string $value value of the element
     * @param array $extra e.g. array ('class' => 'css-type and-more') 
     * @return string the tet field 
     */
    public static function textClean ($name, $value = null, $extra = array()){
        if (!isset($extra['size'])){
            $extra['size'] = HTML_FORM_TEXT_SIZE;
        }

        if (!isset($value)) {
            $value = self::setValue($name, $value);
        }
        
        $extra = self::parseExtra($extra);
        $str = "<input type=\"text\" name=\"$name\" id=\"$name\" $extra value=\"$value\" />" . self::$br . "\n";
        return $str;
    }

    /**
     * creates a simple captcha string (or image) 
     * @see captcha.php
     * @param string $name name of the element
     * @param string $value vlaue of the element
     * @param array $extra extras e.g. array ('class' => 'css and-more')
     * @return string $str the simple captcha input string
     */
    public static function simpleCaptcha ($name, $value = '', $extra = array()){
        $str = self::simpleCaptchaClean($name, $value, $extra);
        self::$fields[] = array ('value' => $str);
    }
    
    /**
     * creates a simple captcha string (or image) 
     * @see captcha.php
     * @param string $name name of the element
     * @param string $value vlaue of the element
     * @param array $extra extras e.g. array ('class' => 'css and-more')
     * @return string $str the simple captcha input string
     */
    public static function simpleCaptchaClean ($name, $value = '', $extra = array()){
        if (!isset($extra['size'])){
            $extra['size'] = HTML_FORM_TEXT_SIZE;
        }

        $value = self::setValue($name, $value);
        $extra = self::parseExtra($extra);
        $str = "<input type=\"text\" name=\"$name\" $extra value=\"$value\" />" . self::$br . "\n";
        return $str;
    }

    /**
     * sets form string with a password field
     * @param type $name name of the form field
     * @param type $value value of the form field. 
     * @param type $extra e.g. array ('class' => 'css and-more');
     * @return string $str the password input string
     */
    public static function password ($name, $value = '', $extra = array()){
        if (!isset($extra['size'])){
            $extra['size'] = HTML_FORM_TEXT_SIZE;
        }

        $value = self::setValue($name, $value);
        $extra = self::parseExtra($extra);
        $str = "<input type=\"password\" name=\"$name\" id=\"$name\" $extra value=\"$value\" />" . self::$br . "\n";
        self::$fields[] = array ('value' => $str);
    }
    
        /**
     * sets form string with a password field
     * @param type $name name of the form field
     * @param type $value value of the form field. 
     * @param type $extra e.g. array ('class' => 'css and-more');
     * @return string $str the password input string
     */
    public static function passwordClean ($name, $value = '', $extra = array()){
        if (!isset($extra['size'])){
            $extra['size'] = HTML_FORM_TEXT_SIZE;
        }

        $value = self::setValue($name, $value);
        $extra = self::parseExtra($extra);
        $str = "<input type=\"password\" name=\"$name\" id=\"$name\" $extra value=\"$value\" />" . self::$br . "\n";
        return $str;
    }

    /**
     * method for setting textarea
     * @param string $name the name of the textarea
     * @param string $value the initial value of the textarea
     * @param array $extra e.g. css array ('class' => 'required');  
     */
    public static function textarea ($name, $value = null, $extra = array()){
        $str = self::textareaClean($name, $value, $extra);
        self::$fields[] = array ('value' => $str);
    }
    
    /**
     * method for getting a textarea 
     * @param string $name the name of the textarea
     * @param string $value the initial value of the textarea
     * @param array $extra e.g. css array ('class' => 'required'); 
     * @return string $str the textarea 
     */
    public static function textareaClean ($name, $value = null, $extra = array()){
        if (!isset($extra['rows'])){
            $extra['rows'] = HTML_FORM_TEXTAREA_HT;
        }

        if (!isset($extra['cols'])){
            $extra['cols'] = HTML_FORM_TEXTAREA_WT;
        }

        if (isset($extra['filter_help'])) {
            echo $extra['title'] = moduleloader::getFiltersHelp($extra['filter_help']);            
        } 
        
        if (!isset($value)) {
            $value = self::setValue($name, $value);
        } 

        $extra = self::parseExtra($extra);
        $str =  "<textarea name=\"$name\" id=\"$name\" $extra>$value</textarea>" . self::$br . "\n";
        return $str;
    }
    
    /**
     * method for getting a small textarea (~1/6 of normal textarea) 
     * @param string $name the name of the textarea
     * @param string $value the initial value of the textarea
     * @param array $extra e.g. css array ('class' => 'required'); 
     * @return string $str the textarea 
     */
    public static function textareaSmall ($name, $value = null, $extra = array()){
        $str = self::textareaSmallClean($name, $value, $extra);
        self::$fields[] = array ('value' => $str);
    }
    

    
    /**
     * method for getting a medium textarea ~1/2 of normal textarea size 
     * @param string $name the name of the textarea
     * @param string $value the initial value of the textarea
     * @param array $extra e.g. css array ('class' => 'required'); 
     * @return string $str the textarea 
     */
    public static function textareaMed ($name, $value = null, $extra = array()){
        $str = self::textareaMedClean($name, $value, $extra);
        self::$fields[] = array ('value' => $str);
    }
    
    /**
     * method for getting a small textarea (~1/6 of normal textarea) 
     * @param string $name the name of the textarea
     * @param string $value the initial value of the textarea
     * @param array $extra e.g. css array ('class' => 'required'); 
     * @return string $str the textarea 
     */
    public static function textareaSmallClean ($name, $value = null, $extra = array()){
        if (!isset($extra['rows'])){
            $extra['rows'] = (int)(HTML_FORM_TEXTAREA_HT / 6);
        }

        return self::textareaClean($name, $value, $extra);
    }
    
        /**
     * method for getting a medium textarea ~1/2 of normal textarea size 
     * @param string $name the name of the textarea
     * @param string $value the initial value of the textarea
     * @param array $extra e.g. css array ('class' => 'required'); 
     * @return string $str the textarea 
     */
    public static function textareaMedClean ($name, $value = null, $extra = array()){
        if (!isset($extra['rows'])){
            $extra['rows'] = (int)(HTML_FORM_TEXTAREA_HT / 2);
        }

        return self::textareaClean($name, $value, $extra);
    }

    /**
     * method for getting a file input field
     * @param string $name the name of the file field
     * @param array $extra e.g. css array ('class' => 'popup'); 
     * @return string $str the file input field 
     */
    public static function file ($name, $extra = array()) {
        //get unique id
        $up_id = uniqid();
        $js = self::apcJs($up_id);
        
        template::setStringJs($js);
        if (!isset($extra['size'])){
            $extra['size'] = HTML_FORM_TEXT_SIZE;
        }

        //$value = self::setValue($name, $value);
        $extra = self::parseExtra($extra);
        // Progress bar from: 
        // http://www.johnboy.com/php-upload-progress-bar/
        $str = <<<EOF
    <!--APC hidden field-->
    <input type="hidden" name="APC_UPLOAD_PROGRESS" id="progress_key" value="$up_id"/>
    <!---->
EOF;
        
        
        $str.= "<input type=\"file\" name=\"$name\" id=\"$name\" $extra />\n"  . self::$br . "\n";      
        $str.= <<<EOF
    <!--Include the iframe-->
    <iframe id="upload_frame" height = 40 name="upload_frame" frameborder="0" border="0" src="" scrolling="no" scrollbar="no" > </iframe>
    <br />
    <!---->
EOF;
        self::$fields[] = array ('value' => $str);
        return $str;
    }
    
    /**
     * sets a file field with info about max size
     * @param string $filename 
     * @param int $max_bytes
     * @param array $options
     */
    public static function fileWithLabel ($filename, $max_bytes, $options = array()) { 
    
        
        
        
        $bytes = upload::getNativeMaxUpload();
        if ($max_bytes < $bytes) {
            $bytes = $max_bytes;
        } 
        
        html::hidden('MAX_FILE_SIZE', $bytes);
        
        
        $label = lang::system('system_form_label_file') . ". ";
        $label.= lang::system('system_file_allowed_maxsize');
        $size = upload::bytesToGreek($bytes);
        $label.= $size;
        
        html::label($filename, $label );
        html::file($filename, $options);
    }
    
    // Progress bar from: 
    // http://www.johnboy.com/php-upload-progress-bar/
    /**
     * sets a apc js string
     * @param string $apc_id
     * @return string $apc_string
     */
    public static function apcJs ($apc_id) {
            
        $form_id = self::$internal['form_id'];
        $str = <<<EOF
<!--display bar only if file is chosen-->

$(document).ready(function() { 
//

//show the progress bar only if a file field was clicked
	var show_bar = 0;
    $('input[type="file"]').click(function(){
		show_bar = 1;
    });

//show iframe on form submit
    $("#$form_id").submit(function(){

		if (show_bar === 1) { 
			$('#upload_frame').show();
			function set () {
				$('#upload_frame').attr('src','/upload.php?up_id=$apc_id');
			}
			setTimeout(set);
		}
    });
//

});

EOF;
        return $str;
    }

    /**
     * method for getting a checkbox
     * @param string $name the name of the input
     * @param string $value the value of the input
     * @param array $extra set extras e.g. array ('class' => 'action')
     * @return string 
     */
    public static function checkbox ($name, $value = 0, $extra = array ()) {        
        $str = self::checkboxClean($name, $value, $extra) ;
        self::$fields[] = array ('value' => $str, 'type' => 'checkbox');
        return $str;
    }
    
    /**
     * method for getting a checkbox
     * @param string $name the name of the input
     * @param string $value the value of the input
     * @param array $extra set extras e.g. array ('class' => 'action')
     * @return string 
     */
    public static function checkboxClean ($name, $value = 0, $extra = array ()) {        
        $value = self::setValue($name, $value);
        if ($value){
            $extra['checked'] = "yes";
        } 
        
        $extra = self::parseExtra($extra);
        $str = "<input type=\"checkbox\" name=\"$name\" id=\"$name\" value=\"1\" $extra />" . self::$br . "\n";
        return $str;
    }
    
    /**
     * sets a radio button
     * @param string $name
     * @param array $options
     * @param int $checked
     * @param array $extra
     */
    public static function radio ($name, $options, $checked, $extra = array()) {
        $radio = self::radioClean($name, $options, $checked, $extra = array ());
        $str = $radio . self::$br . "\n" ;
        self::$fields[] = array ('value' => $str);
        return $str;
    }
    
    /**
     * returns a radio button set
     * @param string $name 
     * @param array $selects assoc array of key and values. 
     *                      key is the value of the element and value is
     *                      the human name e.g. 
     *                      array (0 => 'Female', 1 => 'Male');
     *                      If female is checked then the value 0 
     *                      is submitted
     * @param string $checked the value to be pre set
     * @param array $extra to add to the field. 
     * @return string 
     */
    public static function radioClean ($name, $selects, $checked, $extra = array()) {
        $str = '';
        if (isset($extra['seperator'])) {
            $sep = $extra['seperator'];
            unset ($extra['seperator']);
        } else {
            $sep = "<br />\n";
        }
        
        $checked = self::setValue($name, $checked);
        $extra = self::parseExtra($extra);
        
        foreach ($selects as $key => $select) {
            $add_checked = '';
            if ($key == $checked) $add_checked = " checked ";
            $str.= "<input type=\"radio\" name= \"$name\" value=\"$key\" $extra $add_checked />$select\n";
           
            
        }
        return $str;
    }

    /** 
     * method for getting a submit button
     * @param string $name the name of the button
     * @param string $value the value of the button
     * @param array $extra e.g. for setting css array ('class' => 'onPress')
     * @return string $str the submit button 
     */
    public static function submit ($name, $value, $extra = array ()) {
        $extra = self::parseExtra($extra);
        $str =  "<input type=\"submit\" $extra name=\"$name\" id=\"$name\" value=\"$value\" />" . self::$br . "\n";
        self::$fields[] = array ('value' => $str);
        return $str;
    }
    
    /** 
     * method for getting a submit button
     * @param string $name the name of the button
     * @param string $value the value of the button
     * @param array $extra e.g. for setting css array ('class' => 'onPress')
     * @return string $str the submit button 
     */
    public static function submitClean ($name, $value, $extra = array ()) {
        $extra = self::parseExtra($extra);
        $str =  "<input type=\"submit\" $extra name=\"$name\" id=\"$name\" value=\"$value\" />" . self::$br . "\n";
        self::$fields[] = array ('value' => $str);
        return $str;
    }
    
    /**
     * add html as a field inside the form
     * @param string $str the string to add 
     */
    public static function addHtml ($str) {
        self::$fields[] = array('value'=> $str);
    }

    /**
     * method for passing extras to all form fields
     * @param array $extra e.g. array ('class' => 'required')
     * @return string $the extras parsed to a string e.g. class = "required"  
     */
    public static function parseExtra ($extra = array()) {
        $str = '';
        if (empty($extra)) return '';
        
        foreach ($extra as $key => $val){
            if ($key == 'checked') {
                $str = ' checked ';
                continue;
            }
            
            // action is special - e.g. for cloud storage, so this is parsed in 
            // getAction
            if ($key == 'action') {
                continue;
            }
            $str.= " $key = \"$val\" ";
        }
        return $str;
    }


    /**
     * method for setting a drop down box. This will be colleted in the HTML::fields[]
     * If you just want a select box use selectClean
     * @param   string  $name the name of the select field
     * @param   array   $rows the rows making up the ids and names of the select field
     * @param   string  $field array field which will be used as name of the select element
     * @param   string  $id the array field which will be used as id of the select element
     * @param   string  $value inital value
     * @param   array   $extra extra elements to be added
     * @param  array   $init inital array
     */
    public static function select(
            $name, 
            $rows, 
            $field = 'title', 
            $id = 'id', 
            $value=null, 
            $extra = array(), 
            $init = array()){        
        $dropdown = self::selectClean($name, $rows, $field, $id, $value, $extra, $init);
        $str = $dropdown . self::$br . "\n" ;
        self::$fields[] = array ('value' => $str);
        return $str;
    }
    
    /**
     * method for getting a drop down box. This will be colleted in the HTML::fields[]
     * If you just want a select box use selectClean
     * @param   string  $name the name of the select field
     * @param   array   $rows the rows making up the ids and names of the select field
     * @param   string  $field array field which will be used as name of the select element
     * @param   string  $id the array field which will be used as id of the select element
     * @param   string  $value inital value
     * @param   array   $extra extra elements to be added
     * @param  array  $init inital array
     * @return string $str select as a string
     * 
     */
    public static function selectClean($name, $rows, $field, $id, $value = null, $extra = array(), $init = array()){        
        if (!isset($extra['id'])) {
            $extra['id'] = $name;
        }
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
        return $dropdown;
    }
    
    /**
     * method for setting a optgroup dropdown box. This will be colleted in the HTML::fields[]
     * If you just want a select box use selectOptgroupClean
     * @param   string  $name the name of the select field
     * @param   array   $rows the rows making up the ids and names of the select field
     * @param   string  $field array field which will be used as name of the select element
     * @param   string  $id the array field which will be used as id of the select element
     * @param   string  $value inital value
     * @param   array   $extra extra elements to be added
     */
    public static function selectOptgroup(
            $name, 
            $rows, 
            $field = 'title', 
            $id = 'id', 
            $value=null, 
            $extra = array() ){        
        $dropdown = self::selectOptgroupClean($name, $rows, $field, $id, $value, $extra);
        $str = $dropdown . self::$br . "\n" ;
        self::$fields[] = array ('value' => $str);
        return $str;
    }
    
    /** method for getting a optgroup dropdown select. 
     * @param   string  $name the name of the select field
     * @param   array   $rows the rows making up the ids and names of the select field
     * @param   string  $field array field which will be used as name of the select element
     * @param   string  $id the array field which will be used as id of the select element
     * @param   string  $value inital value
     * @param   array   $extra extra elements to be added
     * @return  string $str select as a string
     * 
     */
    public static function selectOptgroupClean($name, $rows, $field, $id, $value = null, $extra = array()){        
        if (!isset($extra['id'])) {
            $extra['id'] = $name;
        }
        $extra = self::parseExtra($extra);
        
        $dropdown = "<select name=\"$name\" $extra";
        if (!isset($value)) {
            $value = self::setValue($name, $value);
        }
        
        $dropdown.= ">\n";
        foreach ($rows as $group) {
            
            $title = $group['optgroup'];
            $dropdown.= "<optgroup label=\"$title\">\n";

            
            foreach($group['options'] as $row){
                if ($row[$id] == $value){
                    $s = ' selected';
                } else {
                    $s = '';
                }

                $dropdown .= '<option value="'.$row[$id].'"' . $s . '>'.$row[$field].'</option>'."\n";
            }
            $dropdown.= "</optgroup>\n";
        }
        $dropdown .= "</select>\n";
        return $dropdown;
    }
    
    /**
     * method for setting drop down for birthday
     * with element for day, month and year
     */
    public static function selectBirthday () {        
        $date = html_helpers::birthdayDropdown();
        self::$fields[] = array ('value' => $date['day']);
        self::$fields[] = array ('value' => $date['month']);
        $year = $date['year'] . self::$br . "\n";
        self::$fields[] = array ('value' => $year);
    }
    
    /**
     * method for adding a drop down box to the form with multiple selects.
     * 
     * @param   string  $name the name of the select field
     * @param   array   $rows the rows making up the ids and names of the select field
     * @param   string  $field array field which will be used as name of the select element
     * @param   string  $id the array field which will be used as id of the select element
     * @param   array   $value to be selected
     * @param   string  $extra to be added to this form field, e.g. css (array ('class' => 'required'));
     * @param   array   $init 
     * @return  string  $select the multiple select string.
     * 
     */
    public static function selectMultipleClean(
            $name, 
            $rows, 
            $field, 
            $id, 
            $value = null, 
            $extra = array(), 
            $init = array()){        
        $extra = self::parseExtra($extra);
        $name = $name . "[]";
        $dropdown = "<select multiple=\"multiple\" name=\"$name\" $extra";

        if (!isset($value)) {
            $value = self::setValue($name, $value);
            if (!is_array($value)) {
                $value = array ();
            }
        }
        
        $dropdown.= ">\n";
        if (!empty($init)) {
            $dropdown.= '<option value="'.$init[$id].'"' . '' . '>'.$init[$field].'</option>'."\n";
        }
        
        foreach($rows as $row){
            if (in_array($row[$id], $value)){
                $s = ' selected';
            } else {
                $s = '';
            }

            $dropdown .= '<option value="'.$row[$id].'"' . $s . '>'.$row[$field].'</option>'."\n";
        }
        $dropdown .= "</select>\n";
        return $dropdown;
    }

    /**
     * method for creating a html link. It will check with the rewrite module
     * to see if the url has been rewritten (if the module is installed). Use
     * createLinkSimple if you just need a link without any side effects. 
     * @param string $url the a href attribute
     * @param string $title the value of the link
     * @param array  $options e.g. css class or javascript actions, e.g. ('class' => 'error')
     * @return string $link the html link
     */
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
        
        if (isset($options['anchor_part'])) {
            $url.= $options['anchor_part'];
            unset($options['anchor_part']);
        }

        $options = self::parseExtra($options);
        $link = "<a href=\"$url\" $options>$title</a>";
        return $link;
    }
    
    public static function createLinkSimple ($url, $title, $options = array ()) {
        $options = self::parseExtra($options);
        $link = "<a href=\"$url\" $options>$title</a>";
        return $link;
    }

    /**
     * method for getting url fro e.g. creating a link. If rewrite manip is enabled
     * we will fetch the rewritten link from database
     * @param  string $url
     * @return string $url rewritten if rewrite url entered url exists
     */
    public static function getUrl ($url) {
       if (moduleloader::isInstalledModule('rewrite')) {
           moduleloader::includeModule('rewrite');
            $alt_uri = rewrite::getRowFromRequest(html::specialDecode(rawurldecode($url)));
            if (isset($alt_uri)){
                $url = $alt_uri;
            }
        }
        return $url;
    }

    /**
     * method for creating an image html tag
     * @param string $src the source of the image
     * @param array  $options for setting e.g. css class array ('class' => 'popup')
     * @return string $str the image tag 
     */
    public static function createImage ($src, $options = array()) {
        $options = self::parseExtra($options);
        $str = "<img src=\"$src\" $options />";
        return $str;
    }
    
    /**
     * creates an image working as a html link
     * @param string $href the href part of the link
     * @param string $image_src the image source
     * @param array $options for setting e.g. css class array ('class' => 'popup')
     * @return string $str the a href displaying an image
     */
    public static function createHrefImage($href = null, $image_src = null, $options = array()){
        $str = self::createImage($image_src, $options);
        return "<a href=\"$href\">$str</a>";
    }
    
    /**
     * special encodes an array or string 
     * @param array|string $values
     * @return array|string $values
     */
    public static function specialEncode($values, $negative = array ()){
        if (is_array($values)){
            foreach($values as $key => $val){
                if (in_array($key, $negative)) continue;
                
                if (is_array($val) ) {
                    $values[$key] = self::specialEncode($val);
                } else {
                    $values[$key] = htmlspecialchars($val, ENT_COMPAT, 'UTF-8');
                }
            }
        } else if (is_string($values)) {
            $values =  htmlspecialchars($values, ENT_COMPAT, 'UTF-8');
        } else {
            $values = '';
        }
        return $values;
    }

    /**
     * special decodes array or string 
     * @param array|string $values
     * @return array|string $values
     */
    public static function specialDecode($values){
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

    /**
     * encodes entites in array or string for secure display
     * @param string|array $values
     * @return string|array $values
     */
    public static function entitiesEncode($values){
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

    /**
     * decodes entites in array or string
     * @param string|array $values
     * @return string|array $values
     */
    public static function entitiesDecode($values){
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

    /**
     * method for creating a form widget, e.g. a tag dropdown
     * @param string $class the class to generate the widget
     * @param string $method the method to use for the widget
     * @param string $name the name of the form field
     * @param string $value the init value of the form field
     * @return string $str the widget string 
     */
    public static function widget ($class, $method, $name = null, $value = null){
        moduleloader::includeModule ($class);
        $value = self::setValue($name, $value);
        $str = $class::$method($name, $value);
        self::$fields[] = array ('value' => $str);
        return $str;
    }
    
    /**
     * method for sanitizing a url real simple
     * remove / ? # - add entites for displaying the url in a link
     * without any dangers
     * @ignore
     * @deprecated
     * @param string $url
     * @return string $url
     */
    public static function sanitizeUrlSimple ($string) {
        $strip = array('/', '?', '#');
        $sub = array ('', '', '');
        $clean = trim(str_replace($strip, $sub, strip_tags($string)));
        return $clean;
    }
    
    /**
     * echo a html headline. Defaults to h3. 
     * can be overridden in template. 
     * @param type $message
     * @param type $tag
     */
    public static function headline ($message, $tag = 'h3') {
        if (method_exists('mainTemplate', 'headline')) {
            mainTemplate::headline($message, $tag);
            return;
        }
        echo self::getHeadline($message, $tag);
    }
    
    /**
     * gets a html headline message. Default to h3
     * @param string $message
     * @param string $tag
     * @return string $html
     */
    public static function getHeadline ($message, $tag = 'h3') {
        if (method_exists('mainTemplate', 'getHeadline')) {
            return mainTemplate::getHeadline($message, $tag);
        }
        $str = "<!-- headline_message -->\n";
        $str.= "<div class=\"headline\">\n";
        $str.= "<$tag>$message</$tag>\n";
        $str.= "</div>\n";
        return $str;
    }

    /**
     * template function for printing form errors
     * @param  array $errors 
     */
    public static function errors($errors){
        // if method exists in loaded template we use that.    
        if (method_exists('mainTemplate', 'errors')) {
            mainTemplate::errors($errors);
            return;
        } 
        
        echo self::getErrors($errors);
    }
    
     /**
     * gets html error messages from array or string. can be overridden in template
     * @param  string|array $html
     */
    public static function getErrors($errors){

        if (method_exists('mainTemplate', 'getErrors')) {
            return mainTemplate::getErrors($errors);
        } 
        
        if (is_string($errors)){
            return html::getError($errors);
        }
        $str = "<!-- view_form_errors -->\n";
        $str.= "<div class=\"form_error\"><ul>\n";
        foreach($errors as $error){
            $str.= "<li>$error</li>\n";
        }
        $str.= "</ul></div>\n";
        $str.= "<!-- / end form_error -->\n";
        return $str;
    }

    /**
     * echos a confirm message. Can be overridden in template. 
     * @param  string  $message positive confirmation on correct filled form
     */
    public static function confirm($message){
        if (method_exists('mainTemplate', 'confirm')) {
            mainTemplate::confirm($message);
        } 
        echo self::getConfirm($message);
    }
    
    /**
     * gets a html confirm message. Can be overridden in template
     * @param type $message
     * @return string $html
     */
    public static function getConfirm ($message) {
        if (method_exists('mainTemplate', 'getConfirm')) {
            return mainTemplate::getConfirm($message);
        }
        $str = "<!-- view_confirm -->\n";
        $str.= "<div class=\"form_confirm\">\n";
        $str.= "<ul><li>$message</li></ul>\n";
        $str.= "</div>\n";
        return $str;
    }

    /**
     * echo an html error message. This can be overridden in template
     * @param string $error message
     */
    public static function error($message){
        if (method_exists('mainTemplate', 'error')) {
            mainTemplate::error($message);
        }
        echo self::getError($message);
    }
    
    /**
     * gets a error message. This can be overridden in template
     * @param string $message
     * @return string $html error message
     */
    public static function getError($message) {
        if (method_exists('mainTemplate', 'getError')) {
            return mainTemplate::getError($message);
        }
        $str = "<!-- view_error -->\n";
        $str.= "<div class=\"form_error\">\n";
        $str.= "<ul><li>$message</li></ul>\n";
        $str.= "</div>\n";
        return $str;
    }
}
