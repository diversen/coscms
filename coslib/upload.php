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
    
    /**
     * for holding errors
     * @var array 
     */
    public static $errors = array();

    /**
     * the mode new directories will be created in. 
     * @var int
     */
    public static $mode = 0777;

    /**
     * options 
     * @var array 
     */
    public static $options = array();

    /**
     * constructor
     *
     * init and try to set put dir. Try to make it if not exists. sets options
     * @param  array  array of options
     */
    function __construct($options = null){
        self::$errors = array();
        if (isset($options)) {
            self::$options = $options;
        }
       
        //if (isset($options['upload_dir'])){
        //    self::$uploadDir = $options['upload_dir'];
        //}
    }
    // }}}
    public static function setOptions ($options) {
        self::$options = $options;
    }
    
    

    /**
     * method for moving uploaded file
     *
     * @param   name of file in the html forms file field, e.g. file
     * @return int  true on success or false on failure
     */
    public static function moveFile($filename = null, $options = null){
        if (isset($options)) self::$options = $options;
        
        // create dir. 
        if (!file_exists(self::$options['upload_dir'])){
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
            }
            return $ret;
            
        } 
        cos_error_log('No file to move in ' . __FILE__ . ' ' . __LINE__, false);        
        return false;
    }
    
    public static $saveBasename = array();
    
    public static function newFilename ($file) {
        $info = pathinfo($file);
        $path = $info['dirname'];
        
        $new_filename = $info['filename'] . '-' . md5(time()) . '.' . $info['extension'];
        $full_save_path = $path . '/' . $new_filename;
        
        self::$saveBasename = $new_filename;
        
        return $full_save_path;
    }
    
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
    
    public static function checkUploadNative ($filename) {
        //$post_max_size = ini_get('post_max_size');
        //$size = return_bytes($post_max_size);
        
        $upload_return_code = $_FILES[$filename]['error'];
        
        
        
        if ($upload_return_code != 0) {
            self::$errors[] = file_upload_error_message($upload_return_code);
            return false;
        }
        return true;
    }
    
    public static function checkMaxSize ($filename) {
        if($_FILES[$filename]['size'] > self::$options['maxsize'] ){
            $message = lang::translate('system_file_upload_to_large');
            $message.= lang::translate('system_file_allowed_maxsize');
            $message.= bytesToSize(self::$options['maxsize']);
            self::$errors[] = $message;
            return false;
        }
        return true;
    }
    
        
    public static function getMimeAsString ($mimes) {
        return implode(', ', $mimes);        
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
class uploadBlob extends upload {

    /**
     * the upload function
     *
     * @param   array   $options
     * @return  void
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

    
    // }}}
    // {{{ getFP($options
    /**
     * the upload function
     *
     * @param   array   $options
     * @return  void
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
