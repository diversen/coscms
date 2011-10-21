<?php

/**
 * file with class for doing uploads and a couple of helper functions
 *
 * @package     coslib
 */


// {{{ int of bytes function return_bytes ($2M)
/**
 * return php.ini ini file size in bytes.
 *
 * @param   string  e.g. 2M as size set in php.ini
 * @return  string  e.g. e.g. bytes size of 2M
 */
function return_bytes($val) {
    $val = trim($val);
    $last = strtolower($val[strlen($val)-1]);
    switch($last) {
        // The 'G' modifier is available since PHP 5.1.0
        case 'g':
            $val *= 1024;
        case 'm':
            $val *= 1024;
        case 'k':
            $val *= 1024;
    }

    return $val;
}

// }}}
// {{{ string function file_upload_error_message($error_code)
/**
 *
 * @param  constant   error code returned by bad file upload
 * @return  string    translations of the error codes.
 */
function file_upload_error_message($error_code) {
    switch ($error_code) {
        case UPLOAD_ERR_INI_SIZE:
            return 'The uploaded file exceeds the upload_max_filesize directive in php.ini';
        case UPLOAD_ERR_FORM_SIZE:
            return 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form';
        case UPLOAD_ERR_PARTIAL:
            return 'The uploaded file was only partially uploaded';
        case UPLOAD_ERR_NO_FILE:
            return 'No file was uploaded';
        case UPLOAD_ERR_NO_TMP_DIR:
            return 'Missing a temporary folder';
        case UPLOAD_ERR_CANT_WRITE:
            return 'Failed to write file to disk';
        case UPLOAD_ERR_EXTENSION:
            return 'File upload stopped by extension';
        case UPLOAD_ERR_OK;
            return 0;
        default:
            return 'Unknown upload error';
    }
}
// }}}
/**
 * class for doing uploads
 *
 * @package     coslib
 */
class upload {
    /** @var array   holding errors */
    public $error = array();
    /** @var array   holding status messages to use elsewhere */
    public $status = array();
    /** @var string uploadDir */
    public $uploadDir = '/files/content';
    /** @var string table for doing crud operations on */
    public $dbTable = 'files';
    /** @var    boolean     use docroot DOCUMENT_ROOT or not */
    public $useDocRoot = true;
    /** @var    int  default mode when creating a dir */
    public $mode = 0775;
    /** @var    array   types to allow eg <code>array('zip', 'tar.gz');</code> */
    public $allow = array('tar.gz', 'zip');
    /** @var    string  name of file saved */
    public $saveFile = null;

    public $createDir;
    // {{{ method __constructor (array $options)
    /**
     * constructor
     *
     * init and try to set put dir. Try to make it if not exists. sets options
     * @param  array  array of options
     */
    function __construct($options = null, $createDir = false){
        //$this->uploadDir = get_domain();
        $domain = get_domain();
        
        
        if ($domain == 'default') {
            $this->uploadDir = "/files/$domain/content";
        } else {
            $this->uploadDir = "/files/content";
        }
        
        if (isset($options['upload_dir'])){
            $this->uploadDir = $options['upload_dir'];
        }
        if (isset($options['db_table'])){
            $this->dbTable = $options['db_table'];
        }
        if ($this->useDocRoot){
            //$files_path = get_files_path();
            $this->uploadDir = _COS_PATH . "/htdocs" . $this->uploadDir;
        }
        $this->createDir = $createDir;

    }
    // }}}
    // {{{ method moveFile ($filename = null, $options = null)

    /**
     * method for moving uploaded file
     *
     * @param   name of file in the html forms file field
     * @return int  true on success or false on failure
     */
    public function moveFile($filename = null, $options = null){

        // check if dir exists
        if (!file_exists($this->uploadDir) && $this->createDir){
            $status['dir_not_exists'] = "Dir: " . $this->uploadDir . " does not exists";
            //echo $this->uploadDir; die;
            //die;
            $ret = mkdir ($this->uploadDir, $this->mode, true);
            if ($ret){
                $this->status[] = lang::translate('Created dir') . ' ' . $this->uploadDir;
            } else {
                $this->error[] = lang::translate("Error. Could not make") . '' . $this->uploadDir . "!";
            }
        }
        if (isset($_FILES[$filename]) && !empty($_FILES[$filename]['name'])){
            $this->savefile = $this->uploadDir . '/' . basename($_FILES[$filename]['name']);
            if (file_exists($this->savefile)){
                $this->error[] = "There is already a file with that name: " . $this->savefile . " You can only have one filename per directory";
                return false;
            } 
            $ret = move_uploaded_file($_FILES[$filename]['tmp_name'], $this->savefile);
            return $ret;
        } else {
            return false;
        }
    }
    // }}}
    // {{{ method unlinkFile (string $filename)
    /**
     * method for unlinking a file
     *
     * @return boolean  true on success or false on failure
     */
    public function unlinkFile($filename){

        if (file_exists($filename)){
            return unlink($filename);
        } else {
            return false;
        }
    }
    // }}}
}

/**
 * class for doing a upload of a blob to db
 *
 * @package     coslib
 */
class uploadBlob {

    /**
     * @var string  name of the form element that holds the file 
     */
    var $options = array();
    public static $errors = array();
    /**
     * @var array   allowed extensions (the mime type of the allowed extensions)
     *              e.g. "application/x-gzip"
     */

    /**
     * constructor
     */
    function __construct($options){
        $this->options = $options;
    }
    
    // {{{ getFP($options
    /**
     * the upload function
     *
     * @param   array   $options
     * @return  void
     */
    public static function getFP($options = array()){

        //check if a file was uploaded
        $userfile = $options['filename'];
        $maxsize = $options['maxsize'];
        /*
        if (isset($options['scaled_filename'])) {
            $scaled_filename = $options['scaled_filename'];
        }*/

        if (isset($options['allow_mime'])){
            $allow_mime = $options['allow_mime'];
        }
        if(is_uploaded_file($_FILES[$userfile]['tmp_name']) &&
                filesize($_FILES[$userfile]['tmp_name']) != false){
            $size = filesize($_FILES[$userfile]['tmp_name']);
            $type = mime_content_type($_FILES[$userfile]['tmp_name']);
            $fp = fopen($_FILES[$userfile]['tmp_name'], 'rb');
            $name = $_FILES[$userfile]['name'];

            //  check the file is less than the maximum file size
            if($_FILES[$userfile]['size'] > $maxsize ){
                throw new Exception("File to large");
            }
            // check for right content
            if (isset($allow_mime)){
                if (!in_array($type, $allow_mime)) {
                    throw new Exception("File format not allowed");
                }
            }
            return $fp;
        }
    }
    // }}}
    // {{{ getFP($options
    /**
     * the upload function
     *
     * @param   array   $options
     * @return  void
     */
    public static function getFPFromFile($options = array()){

        if (!file_exists($options['filename'])) {
            self::$errors[] = 
            lang::translate('system_class_upload_get_fp_file_does_not_exists')
            . ' : ' . $options['filename'];
            return false;
        }
        $size = filesize($options['filename']);

        //  check the file is less than the maximum file size
        if($size > $options['maxsize'] ){
            self::$errors[] = lang::translate('system_class_upload_file_too_large') .
            ": " . $options['filename'];
            return false;
        }

        // check for right content
        if (isset($options['allow_mime'])){
            $type = mime_content_type($options['filename']);
            if (!in_array($type, $options['allow_mime'])) {
                self::$errors[] = lang::translate('system_class_upload_file_format_not_allowed') .
                ": " . $type;
                return false;
                
            }
        }
        $fp = fopen($options['filename'], 'rb');
        return $fp;
    }
    // }}}
    /**
     * method for scaling from a file input field
     * into a database table
     *
     * @param string $post_filename (name of the file input)
     * @param string $db_table (database table to use)
     *               Note the table will by default
     *               scale into a blob field called 'file'
     * @param int    unique id of the row to scale into
     *               it is an update operation
     * @param int    length of the scaled image
     * @param int    max size in bytes.
     * @return boolean result of operation
     *                  may throw an exception
     */
    public static function scaleImageToBlob (
        $post_filename,
        $db_table,
        $field = 'file',
        $id = 0,
        $image_length = '400',
        $maxsize = null, $options = null) {
        if (isset($_FILES[$post_filename]['tmp_name'])){

            $options['filename'] =
                $_FILES[$post_filename]['tmp_name'] . "-scaled";
            if ($maxsize) {
                $options['maxsize'] = $maxsize;
            } else {
                $options['maxsize'] = 4000000;
            }

            $res = self::scaleImage(
                $_FILES[$post_filename]['tmp_name'],
                $options['filename'],
                $image_length, 
                $options
            );
            if (!$res)  {
                // self::$errors[] = 'could not scale image';
                return false;
            }
            
            $res = uploadBlob::getFPFromFile($options);
            if (!$res) {
                return false;
            }
            
            $values[$field] = $res;
            $values['content_type'] = $_FILES[$post_filename]['type'];

            $db = new db();
            $bind = array($field => PDO::PARAM_LOB);
            $res = $db->update($db_table, $values, $id, $bind);
            return $res;
        }
    }

    /**
     *
     * @param type $image
     * @param type $thumb
     * @param type $length
     * @param type $options
     * $_options = array(
        'quality'     => 75,
        'scaleMethod' => 'smooth',
        'canvasColor' => array(255, 255, 255),
        'pencilColor' => array(0, 0, 0),
        'textColor'   => array(0, 0, 0)
        );

     */
    
    static function scaleImage ($image, $thumb, $length, $options = array()){
        require_once 'Image/Transform.php';

        //create transform driver object
        $it = Image_Transform::factory('GD');
        if (isset($options)) {
            //foreach ($options as $key => $val) {
            //    $it->setOption
            //}
            // cos_error_log('log image scale' . $options['quality']);
            
            $it->_options = $options;
            
         }
        
        if (PEAR::isError($it)) {
            self::$errors[] = lang::translate('system_upload_image_transform_factory_exception');
            cos_error_log($it->getMessage());
            return false;
        }

        //load the original file
        $ret = $it->load($image);
        if (PEAR::isError($ret)) {
            self::$errors[] = lang::translate('system_upload_image_transform_load_exception');
            cos_error_log($ret->getMessage());
            return false;
        }

        $ret = $it->scaleByX($length);
        if (PEAR::isError($ret)) {
            self::$errors[] = lang::translate('system_upload_factory_image_transform_scale_exception');
            cos_error_log($ret->getMessage());
            return false;
        }

        $ret = $it->save($thumb);
        if (PEAR::isError($ret)) {
            self::$errors[] = lang::translate('system_upload_factory_image_transform_save_exception');
            cos_error_log($ret->getMessage());
            return false;
        }
        return true;
    }
}

class scaleBlobSimple {
    // id of the $_FILE element to move to blob, e.g. 'file'
    public static $filename = 'file';
    // the db table which recieve the file
    public static $dbTable;
    // the db field which contains the blob to scale into
    public static $dbField = 'file';
    // the id of the field to scale image into. 
    public static $fieldId;    
    // width (x) of the image
    public static $scaleWidth = '400';
    // maxsize of the file in bytes
    public static $maxsize = 4000000;
    // extra options
    public static $options = null;
    
    public static function scaleImageToBlob () {
        return uploadBlob::scaleImageToBlob(
                self::$filename, 
                self::$dbTable, 
                self::$dbField, 
                self::$fieldId, 
                self::$scaleWidth, 
                self::$maxsize, 
                self::$options);     
    }
    
}
