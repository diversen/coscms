<?php



/**
 * File contains contains class for creating forms from a tables schema
 *
 * @package    coslib
 */

/**
 * include form class
 */

include_once "form.php";
/**
 * Class contains contains class for creating forms from a db tables schema
 *
 * @package    coslib
 */
class mySqlForm extends db {

    /**
     *
     * @var string  name of column id - change this if id of table is not 'id'
     */
    public $id = 'id';

    /**
     *
     * @var object  holding form object
     */
    public $form;

    /**
     *
     * @var string  holding table name to use with creation of the form
     */
    public $table;

    /**
     *
     * @var array   holding db fields set by mysql with "describe `table`"
     */
    public $dbFields;

    /**
     *
     * @var array   array holding fields to be used when displaying the form
     */

    public $formFields;

    /**
     *
     * @var string  crud method to use method to use: update, insert or delete
     */
    public $method;

    /**
     *
     * @var array containg fields which will be mapped to other tables.
     *            usefull when you want to display a select element with e.g.
     *            categories.
     */
    public $many = array();

    /**
     * @var array   labels containing labels for form fields in an array
     *              <code>array('title' => 'Enter title')</code>
     *              This will be mapped as the labels in the form
     */

    public $labels;

    /**
     *
     * @var array   var for containing values when updating displayed in form
     *              updating the form
     */
    public $defaultValues = array();

    /**
     * constructor which sets the table and fields to use from database when
     * creating the form.
     *
     * @param   string  the table to use when creating the form.
     * @param   array   if set the fields to use with the form.
     * @param   array   defulat init values when inserting
     */
    function __construct($table, $fields, $initValues = null){
        $this->formFields = $fields;
        $this->table = $table;
        $this->dbFields = $this->selectQuery("SHOW columns FROM $table");
        $this->unsetFormFields();
        $this->initValues = $initValues;
    }


    /**
     *
     * @param   array   labels for form elements.
     */
    public function setLabels($labels){
        $this->labels = $labels;
    }


    /**
     * method for setting crud method (no read)
     *
     * @param   string  insert, update or delete.
     */
    public function setMethod($method, $id = null){
        $this->method = $method;
        if ($method == 'insert'){
            // load default values if form has not been submitted
            if (!isset($_POST['submit'])){
                foreach($this->formFields as $key => $val){
                    $ok = array_key_exists($val['Field'], $this->initValues);
                    if ($ok) {
                        $this->defaultValues[$val['Field']] = $this->initValues[$val['Field']];
                    } else {
                        $this->defaultValues[$val['Field']] = '';
                    }
                }
            } else {
                foreach($this->formFields as $key => $val){
                    if (!isset($_POST[$val['Field']])){
                        $this->defaultValues[$val['Field']] = '';
                    }
                }
            }
        }

        if ($id){
            $this->setDefaultUpdateValues($id);
        }
    }

    /**
     * method for loading values into form. If submit has not been set we load
     * from database. Otherwise we load from the submission
     *
     * @param   int the unique id of the table to use when loading values.
     */

    private function setDefaultUpdateValues($id){
        $this->defaultValues = $this->selectOne($this->table, $this->id, $id);
        if (isset($_POST['submit'])){
            // set values to null (instead filter and validate
            // if we set defaultValues to null form will use sent vars.
            foreach($this->defaultValues as $key => $val){
                $this->defaultValues[$key] = null;
            }
        }
    }

    /**
     * method for creating a connection to another table. E.g. if we have a
     * dropdown with categories from another table and want to display this
     * category as a dropdown of options, we use setToMany
     *
     * @param string    field which field do we map to many
     * @param string    table which table do we use in db
     * @param string    id which column will act as id ('id')
     * @param string    title which column will act as title
     * @param string    type (only dropdown is supported right now)
     */
    public function setToMany($field, $table, $id, $title, $type='dropdown'){
        $this->many[$field] = array(
            'table' => $table,
            'id' => $id,
            'title' => $title,
            'type' => $type
            );
    }

    /**
     * method for unsetting fields which will not be used in form
     *
     */
    private function unsetFormFields(){
       foreach($this->dbFields as $key => $val){
           $k = array_search($val['Field'], $this->formFields);
           if ($k !== false){
               $this->formFields[$k] = $val;
           }
       }
    }

    /**
     * method for fetching an array with select values when we are mapping a
     * form element to another table (e.g. a select option with categories)
     *
     * @param   string
     * @return  array   an array containing entries to be displayed in the
     *                  select element
     */
    private function getSelectValues($field){
        $many_field = $this->many[$field];
        $rows = $this->selectAll($many_field['table']);
        $entries = array();
        foreach($rows as $key => $val){
            $entries[$val[$many_field['id']]] = $val[$many_field['title']];
        }
        return $entries;

    }

    /**
     * Method that will create the actual form
     *
     * Same documentation as in PEAR::HTML_Form
     *
     * @param string the string naming file or URI to which the form should be submitted
     * @param string a string indicating the submission metho 'get' or 'post')
     * @param string a string used in the <form>'s 'name' attribute
     * @param string a string used in the <form>'s 'target' attribute
     * @param string a string indicating the submission's encoding
     * @param string a string of additional attributes to be put in the element (example: 'id="foo"')
     * @return void
     *
     * @access public
     */
    public function createForm($action, $method = 'post', $name = '', $target = '',
                       $enctype = '', $caption = ''){
        $this->form = new HTML_Form($action, $method, $name, $target,
                       $enctype);

        // form for deleting
        if ($this->method == 'delete'){
            if (empty($caption)){
                $caption = lang::translate('Delete selected item') . '?';
            }
            //$this->form->addHidden($this->id, $this->defaultValues[$val['Field']]);
            $this->form->addSubmit('submit', lang::translate('Delete'));
            $this->form->display('', $caption);
            return;
        }

        foreach($this->formFields as $key => $val){
            // first we check for special elements
            // field for file.
            if ($val == 'file'){
                 $this->form->addFile('filename', lang::translate('Add File'));
            } else if ($val == 'captcha'){
                if (!isset($_POST['captcha'])) $_POST['captcha'] = '';
                $this->form->addText(
                    'captcha',
                    //$this->labels[$val['Field']],
                    captcha::createCaptcha(),
                    htmlspecialchars($_POST['captcha']));
                   
            } else {
                //
            }

            $type = $this->getType($val['Type']);
            $length = $this->getLength($val['Type']);

            if (isset($_POST) && $this->method == 'insert'){
                if (!isset($this->defaultValues[$val['Field']])){
                    $this->defaultValues[$val['Field']] = $_POST[$val['Field']];
                }
            }
            
            if ($val['Field'] == $this->id){
                // do nothing for now
            } else if (array_key_exists($val['Field'], $this->many)){
                if ($this->many[$val['Field']]['type'] == 'dropdown'){
                    $entries = $this->getSelectValues($val['Field']);
                    $this->form->addSelect(
                        $val['Field'],
                        $this->labels[$val['Field']],
                        $entries,
                        $this->defaultValues[$val['Field']]);
                }
            } else if ($type == 'varchar'){
                $this->form->addText(
                    $val['Field'],
                    $this->labels[$val['Field']],
                    $this->defaultValues[$val['Field']]);

            } else if ($type == 'int'){
                $this->form->addText(
                    $val['Field'],
                    $this->labels[$val['Field']],
                    $this->defaultValues[$val['Field']]);
            } else if ($type == 'float'){
                $this->form->addText(
                    $val['Field'],
                    $this->labels[$val['Field']],
                    $this->defaultValues[$val['Field']]);
            } else if ($type == 'text'){
                $this->form->addTextarea(
                    $val['Field'],
                    $this->labels[$val['Field']],
                    $this->defaultValues[$val['Field']]);
            } else if ($type == 'tinyint'){
                // we use tinyint as boolean (same as mysql does)
                $this->form->addCheckbox(
                    $val['Field'],
                    $this->labels[$val['Field']] ,
                    $this->defaultValues[$val['Field']]);

            } else if ($type == 'mediumblob'){
                // we use tinyint as boolean (same as mysql does)

                $this->form->addFile(
                     $val['Field'],
                     $this->labels[$val['Field']]);
                     //$maxsize = HTML_FORM_MAX_FILE_SIZE,
                     //$size = HTML_FORM_TEXT_SIZE
            } else if ($type == 'timestamp'){
                $this->form->addDatepicker(
                    $val['Field'],
                    $this->labels[$val['Field']] ,
                    $this->defaultValues[$val['Field']]);

            } else {
                //
            }


        }

        if ($this->method == 'insert'){
            $this->form->addSubmit('submit', lang::translate('Insert'));
        } else {
            $this->form->addSubmit('submit', lang::translate('Update'));
        }
        $this->form->display('', $caption);
    }

    /**
     *
     * @param  string $field the field of table to get type of
     * @return string $type the field type.
     */
    private static function getType($field){
        $match = array();
        $pattern = "/[a-z]+/";
        preg_match($pattern, $field, $match);

        if (isset($match[0])){
            return $match[0];
        }
    }

    /**
     *
     * @param   string      the field to get the length from
     * @return  int|void    length of field as an int or nothing if field
     *                      does not have a length (e.g. text)
     */
    private static function getLength($field){
        $match = array();
        $pattern ="/[0-9]+/";
        preg_match($pattern, $field, $match);
        if (isset($match[0])){
            return $match[0];
        }
    }
}
