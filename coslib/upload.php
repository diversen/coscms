<?php

/**
 * file with class for doing uploads and a couple of helper functions
 *
 * @package     upload
 */

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
 * @param int $bytes Size in bytes to convert
 * @param int $precision 
 * @return string $bytes as string
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

/**
 * get a human error message on failed upload
 * @param int $error_code error code returned by bad file upload
 * @return string $message translations of the error codes.
 */
function file_upload_error_message($error_code) {
    switch ($error_code) {
        case UPLOAD_ERR_INI_SIZE:
            return lang::system('system_file_exceeds_php_ini');
        case UPLOAD_ERR_FORM_SIZE:
            return lang::system('system_file_exceeds_max_file_size');
        case UPLOAD_ERR_PARTIAL:
            return lang::system('system_file_partially_uploaded');
        case UPLOAD_ERR_NO_FILE:
            return lang::system('system_file_no_file_uploaded');
        case UPLOAD_ERR_NO_TMP_DIR:
            return lang::system('system_file_missing_tmp_folder');
        case UPLOAD_ERR_CANT_WRITE:
            return lang::system('system_file_no_write_to_disk');
        case UPLOAD_ERR_EXTENSION:
            return lang::system ('system_file_wrong_ext');
        case UPLOAD_ERR_OK;
            return 0;
        default:
            return lang::system('system_file_unknown_error');
    }
}

/**
 * class for doing uploads
 * @package upload
 */
class upload {
    
    /**
     * var for holding errors
     * @var array 
     */
    public static $errors = array();

    /**
     * var for the mode new directories will be created in. 
     * @var int $mode
     */
    public static $mode = 0777;

    /**
     * var options 
     * @var array $options 
     */
    public static $options = array();

    /**
     * var saveBasename
     * @var string $saveBasename 
     */
    public static $saveBasename = array();
    
    /**
     * var holding confirm messages
     * @var array $confirm
     */
    public static $confirm = array ();
    
    /**
     * constructor
     * init and try to set put dir. Try to make it if not exists. sets options
     * @param array $options array of options
     */
    function __construct($options = null){
        self::$errors = array();
        if (isset($options)) {
            self::$options = $options;
        }
    }

    /**
     * method for setting options
     * @param array $options 
     */
    public static function setOptions ($options) {
        self::$options = $options;
    }
    
    

    /**
     * method for moving uploaded file
     * @param  string $filename name of file in the html forms file field
     *                 e.g. 'file'
     * @param array  $options
     * @return boolean $res true on success or false on failure
     */
    public static function moveFile($filename = null, $options = null){
        if (isset($options)) self::$options = $options;
        
        // create dir. 
        if (!file_exists(self::$options['upload_dir'])){
            if (!strstr(self::$options['upload_dir'], _COS_PATH)) {
                self::$options ['upload_dir'] = _COS_PATH . self::$options['upload_dir'];
            } 
            mkdir (self::$options['upload_dir'], self::$mode, true);
        }

        // check if an upload were performed
        if (isset($_FILES[$filename])){
                      
            // check native
            $res = self::checkUploadNative($filename);
            if (!$res) return false;
            
            // check mime
            if (isset(self::$options['allow_mime'])) {
                $res = self::checkAllowedMime($filename);
                if (!$res) return false;                
            }
            
            // check maxsize. Note: Will overrule php ini settings
            if (isset(self::$options['maxsize'])) {
                $res = self::checkMaxSize($filename);
                if (!$res) return false;
            }
            
            // sets a new filename to save the file as or use the 
            // name of the uploaded file. 
            if (isset(self::$options['save_basename'])) {
                $save_basename = self::$options['save_basename'];
            } else {
                $save_basename = basename($_FILES[$filename]['name']);
            }
            
            self::$confirm['save_basename'] = $save_basename;
            
            $savefile = self::$options['upload_dir'] . '/' . $save_basename;
            
            // check if file exists. 
            if (file_exists($savefile)){
                if (isset(self::$options['only_unique'])) {
                    self::$errors[] = lang::translate('system_file_upload_file_exists') . 
                        MENU_SUB_SEPARATOR_SEC . $savefile;
                    return false;
                    
                } else {      
                   // this call will also set self::$info['save_filename']
                   $savefile = self::newFileName($savefile);
                }
            } else {
                self::$saveBasename = $save_basename;  
            }
                        
            $ret = move_uploaded_file($_FILES[$filename]['tmp_name'], $savefile);
            if (!$ret) {
                self::$errors[] = 'Could not move file. Doh!';
                return false;
            }
            $savefile = str_replace(_COS_HTDOCS, '', $savefile);
            return $savefile;
            
        } 
        cos_error_log('No file to move in ' . __FILE__ . ' ' . __LINE__, false);        
        return false;
    }
    
    
    /**
     *
     * create a new filename from a file
     * @param string $file the file to give a new filename
     * @return string $filename the new filename 
     */
    public static function newFilename ($file) {
        $info = pathinfo($file);
        $path = $info['dirname'];
        
        $new_filename = $info['filename'] . '-' . md5(time()) . '.' . $info['extension'];
        $full_save_path = $path . '/' . $new_filename;
        
        self::$saveBasename = $new_filename;
        return $full_save_path;
    }
    
    /**
     * method for checking allowed mime types
     * @param string $filename the filename to check
     * @return boolean $res
     */
    public static function checkAllowedMime ($filename) {
        // if (isset($allow_mime)){
        $type = mime_content_type($_FILES[$filename]['tmp_name']);

        if (!in_array($type, self::$options['allow_mime'])) {
            $message = lang::translate('system_file_upload_mime_type_not allowed');
            $message.= lang::translate('system_file_allowed_mimes');
            $message.=self::getMimeAsString(self::$options['allow_mime']);
            self::$errors[] = $message;
            return false;
        }
        return true;
    }
    
    /**
     * checks php internal upload error codes
     * @param string $filename
     * @return boolean $res 
     */
    public static function checkUploadNative ($filename) {        
        $upload_return_code = $_FILES[$filename]['error'];
        if ($upload_return_code != 0) {
            self::$errors[] = file_upload_error_message($upload_return_code);
            return false;
        }
        return true;
    }
    
    /**
     * checks if if size is allowed
     * @param string $filename the name of the file
     * @param int $maxsize bytes
     * @return boolean $res
     */
    public static function checkMaxSize ($filename, $maxsize = null) {
        if (!$maxsize) {
            $maxsize = self::$options['maxsize'];
        }
        if($_FILES[$filename]['size'] > $maxsize ){
            $message = lang::translate('system_file_upload_to_large');
            $message.= lang::translate('system_file_allowed_maxsize');
            $message.= bytesToSize($maxsize);
            self::$errors[] = $message;
            return false;
        }
        return true;
    }
    
    /**
     * get mime type as a string
     * @param array $mimes
     * @return string $mime types as a string 
     */
    public static function getMimeAsString ($mimes) {
        return implode(', ', $mimes);        
    }
    
    /**
     * method for unlinking a file
     * @param string $filename the file to unlink
     * @return boolean  $res true on success or false on failure
     */
    public function unlinkFile($filename){

        if (file_exists($filename)){
            return unlink($filename);
        } else {
            return false;
        }
    }
    
    /**
     * 
     * wrapper method for uploading an image from a id, destination folder,
     * and a post field, e.g. image
     * @param int     $id the id of the path to save image to
     *                e.g. 10 for /files/default/campaign/10
     * @param string  $post_field name of $_POST element to upload, e.g campaign
     * @return mixed  $res false on failure.
     *                String containing base filname on success
     *                e.g. /files/default/campagin/10/file.jpg or false
     *                If false then errors can be found in upload::$errors  
     */
    public function uploadFromPost ($id, $folder = null, $post_field = 'image') {
        if (!$folder) {
            die('Developer: Set a folder when uploading');
        }
        
        $domain = config::getDomain();
        $options = array (
            'upload_dir' => "/htdocs/files/$domain/$folder/$id",
        );
        $this->setOptions($options);
        $res = $this->moveFile($post_field);
        return $res;
    }   
}

/**
 * class for doing a upload of a blob to db
 * @package     upload
 */
class uploadBlob extends upload {

    /**
     * 
     *gets file pointer
     * @param array $filename
     * @param array $options
     * @return mixed $fp file pointer | true | false
     */
    public static function getFP($filename, $options = array()){
        if (!empty($options)) {
            self::$options = $options;
        }
        

        if(isset($_FILES[$filename])) {
            
            // check native
            $res = self::checkUploadNative($filename);
            if (!$res) return false;
            
            // check mime
            if (isset(self::$options['allow_mime'])) {
                $res = self::checkAllowedMime($filename);
                if (!$res) return false;                
            }

            // check maxsize. Note: Will overrule php ini settings
            if (isset(self::$options['maxsize'])) {
                
                $res = self::checkMaxSize($filename);
                if (!$res) return false;
            }

            $fp = fopen($_FILES[$filename]['tmp_name'], 'rb');
            return $fp;
        }
        // no files
        return true;
    }

    /**
     * gets a file pointer from a specified file
     * @param   string $filename
     * @param array $options
     * @return  mixed $res file pointer | true | false
     */
    public static function getFPFromFile($filename, $options = array()){

        if (isset($options)) self::$options = $options;
        if (!file_exists($filename)) {
            self::$errors[] = 
            lang::translate('system_upload_get_fp_file_does_not_exists')
            . ' : ' . $options['filename'];
            return false;
        }
        
        if (isset($options['maxsize'])) {
            $size = filesize($options['filename']);

            //  check the file is less than the maximum file size
            if($size > $options['maxsize'] ){
                $error = lang::translate('system_file_upload_to_large');
                $error.= lang::translate('system_file_allowed_maxsize') . bytesToSize($options['maxsize']);
                error_log($error);
                self::$errors[] = $error; 
                return false;
            }
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
        $fp = fopen($filename, 'rb');
        return $fp;
    }
}
