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

/**
 * found on:
 * 
 * http://codeaid.net/php/convert-size-in-bytes-to-a-human-readable-format-%28php%29
 * 
 * Convert bytes to human readable format
 *
 * @param integer bytes Size in bytes to convert
 * @return string
 */
function bytesToSize($bytes, $precision = 2)
{	
	$kilobyte = 1024;
	$megabyte = $kilobyte * 1024;
	$gigabyte = $megabyte * 1024;
	$terabyte = $gigabyte * 1024;
	
	if (($bytes >= 0) && ($bytes < $kilobyte)) {
		return $bytes . ' B';

	} elseif (($bytes >= $kilobyte) && ($bytes < $megabyte)) {
		return round($bytes / $kilobyte, $precision) . ' KB';

	} elseif (($bytes >= $megabyte) && ($bytes < $gigabyte)) {
		return round($bytes / $megabyte, $precision) . ' MB';

	} elseif (($bytes >= $gigabyte) && ($bytes < $terabyte)) {
		return round($bytes / $gigabyte, $precision) . ' GB';

	} elseif ($bytes >= $terabyte) {
		return round($bytes / $terabyte, $precision) . ' TB';
	} else {
		return $bytes . ' B';
	}
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
    public static $errors = array();
    /** @var array   holding status messages to use elsewhere */
    public static $status = array();
    /** @var string uploadDir */
    public static $uploadDir = '';
    //** @var string table for doing crud operations on */
    //public $dbTable = 'files';
    /** @var    boolean     use docroot DOCUMENT_ROOT or not */
    public static $useDocRoot = true;
    /** @var    int  default mode when creating a dir */
    public static $mode = 0777;
    /** @var    array   types to allow eg <code>array('zip', 'tar.gz');</code> */
    public static $allow = array('tar.gz', 'zip');
    /** @var    string  name of file saved */
    public static $saveFile = null;

    public static $createDir;
    
    public static $options = null;

    /**
     * constructor
     *
     * init and try to set put dir. Try to make it if not exists. sets options
     * @param  array  array of options
     */
    function __construct($options = null){
        if (isset($options)) {
            self::$options = $options;
        }
       
        if (isset($options['upload_dir'])){
            self::$uploadDir = $options['upload_dir'];
        }
    }
    // }}}
    public static function setOptions ($options) {
        self::$options = $options;
    }

    /**
     * method for moving uploaded file
     *
     * @param   name of file in the html forms file field
     * @return int  true on success or false on failure
     */
    public function moveFile($filename = null, $options = null){

        if (!file_exists(self::$uploadDir)){
            mkdir (self::$uploadDir, self::$mode, true);
        }

        if (isset($_FILES[$filename]) && !empty($_FILES[$filename]['name'])){
            self::$saveFile = self::$uploadDir . '/' . basename($_FILES[$filename]['name']);
            if (file_exists(self::$saveFile)){
                self::$errors[] = lang::translate('system_file_upload_file_exists') . 
                        MENU_SUB_SEPARATOR_SEC . self::$saveFile;
                return false;
            } 
            $ret = move_uploaded_file($_FILES[$filename]['tmp_name'], self::$saveFile);
            return $ret;
        } else {
            return false;
        }
    }

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
    public static $options = array();
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
                $message = lang::translate('system_file_upload_to_large');
                $message.= lang::translate('system_file_allowed_maxsize');
                $message.= bytesToSize($maxsize);
                throw new Exception($message);
            }
            // check for right content
            if (isset($allow_mime)){
                if (!in_array($type, $allow_mime)) {
                    $message = lang::translate('system_file_upload_mime_type_not allowed');
                    $message.= lang::translate('system_file_allowed_mimes');
                    $message.=self::getMimeAsString($allow_mime);
                    throw new Exception($message);
                }
            }
            return $fp;
        }
    }
    
    public static function getMimeAsString ($mimes) {
        return implode(', ', $mimes);
        
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
            $error = lang::translate('system_file_upload_to_large');
            $error.= lang::translate('system_file_allowed_maxsize') . bytesToSize($options['maxsize']);
            error_log($error);
            self::$errors[] = $error; 
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
        $maxsize = 4000000, $options = null) {
        if (isset($_FILES[$post_filename]['tmp_name'])){

            $options['filename'] =
                $_FILES[$post_filename]['tmp_name'] . "-scaled";
            if ($maxsize) {
                $options['maxsize'] = $maxsize;
            }
            /*
            else {
                $options['maxsize'] = 4000000;
            }*/

            $res = self::scaleImage(
                $_FILES[$post_filename]['tmp_name'],
                $options['filename'],
                $image_length, 
                $options
            );
            if (!$res)  {
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
    
    public static function scaleImage ($image, $thumb, $x, $options = array()){

        include_once "imagescale.php";
        if (isset($options)) {
            imagescale::$options = $options;           
        }
        $res = imagescale::byX($image, $thumb, $x);
        return $res;
        

        
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
