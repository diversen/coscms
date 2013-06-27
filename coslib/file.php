<?php

/**
 * package contains file class for doing common file tasks
 * @package file
 * 
 */

/**
 * class for doing common file tasks
 * @package file
 */
class file {
    
    /**
     * function for getting a file list of a directory (. and .. will not be
     * collected)
     *
     * @param   string  the path to the directory where we want to create a filelist
     * @param   array   if $options['dir_only'] isset only return directories.
     *                  if $options['search'] isset then only files containing
     *                  search string will be returned. Superficial as we will 
     *                  use strstr
     * @return  array   entries of all files array (0 => 'file.txt', 1 => 'test.php')
     */
    public static function getFileList ($dir, $options = null) {
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
                if (isset($options['search'])){
                    if (strstr($entry, $options['search'])){
                        $entries[] = $entry;
                    }
                } else {
                    $entries[] = $entry;
                }
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
    public static function getFileListRecursive ($start_dir, $pattern = null) {
        return get_file_list_recursive($start_dir, $pattern);
    }
    
    /**
     * remove single file or array of files
     * @param string|array $files
     */
    public static function remove ($files) {
        if (is_string($files)) {
            unlink($files);
        }
        if (is_array ($files)) {
            foreach ($files as $val) {
                $res = unlink($val);
                if (!$res) {
                    log::error("Could not remove file: $val");
                }
            }
        }
    }
    
    /**
     * method for getting extension of a file
     * @param string $filename
     * @return string $extension
     */
    public static function getExtension ($filename) {
        return $ext = substr($filename, strrpos($filename, '.') + 1);
    }
    
    /**
     * gets a filename from a path string
     * @param string $file full path of file
     * @param array $options you can set 'utf8' => true and the filename will
     *              be utf8
     * @return string $filename the filename     
     */
    public static function getFilename ($file, $options = array())  {
        if (isset($options['utf8'])) {
            $info = file_path::utf8($file);
        } else {
            $info = pathinfo($file);
        }
        return $info['filename'];
    }
    
    /**
     * method for getting mime type of a file
     * @param string $path
     * @return string $mime_type 
     */
    public static function getMime($path) {
        $result = false;
        if (is_file($path) === true) {
            if (function_exists('finfo_open') === true) {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                if (is_resource($finfo) === true) {
                    $result = finfo_file($finfo, $path);
                }
                finfo_close($finfo);
            } else if (function_exists('mime_content_type') === true) {
                $result = preg_replace('~^(.+);.*$~', '$1', mime_content_type($path));
            } else if (function_exists('exif_imagetype') === true) {
                $result = image_type_to_mime_type(exif_imagetype($path));
            }
        }
        return $result;
    }
    
    /**
     * method for getting first path were coslib exists
     * @return string $path the full coslib path
     */
    public static function getFirstCoslibPath() {
        $ps = explode(":", ini_get('include_path'));
        foreach($ps as $path) {
            $coslib = $path . "/coslib";
            if(file_exists($coslib)) {
                return $coslib;
            }
        }
    }
    
    /**
     * method for creating a directory in the _COS_HTDOCS/files directory
     * It will know if we are using a multi domain setup
     * @param string $dir
     */
    public static function mkdir ($dir) {
        $full_path = config::getFullFilesPath();
        $dir = $full_path . "$dir";
        
        if (file_exists($dir)) {
            return false;
        }
        $res = @mkdir($dir, 0777, true);
        return $res;
    }
    
    /**
     * get a cached file using APC
     * @param string $file
     * @return string $content content of the file 
     */
    public static function getCachedFile ($file) {
        ob_start();
        readfile( $file);
        
        $str = ob_get_contents();
        ob_end_clean();
        return $str;
    }
    
    /**
     * get dirs in path using glob function
     * @param string $path
     * @param array $options you can set a basename which has to be correct
     *              'basename' => '/path/to/exists'
     * @return array $dirs 
     */
    public static function getDirsGlob ($path, $options = array()) {
        $dirs = glob($path.'*', GLOB_ONLYDIR); 
        if (isset($options['basename'])) {
            foreach ($dirs as $key => $dir) {
                $dirs[$key] = basename($dir);
            }
        }
        return $dirs;              
    }
    
    /**
     * remove directory recursively
     * @param string $path 
     */
    public static function rrmdir ($dir) {
        $fp = opendir($dir);
        if ( $fp ) {
            while ($f = readdir($fp)) {
                $file = $dir . "/" . $f;
                if ($f == "." || $f == "..") {
                    continue;
                } else if (is_dir($file) && !is_link($file)) {
                    file::rrmdir($file);
                } else {
                    unlink($file);
                }
            }
            closedir($fp);
            rmdir($dir);
       }
    }
    
    public static function scandirRecursive ($dir) {
        $files = scandir($dir);
        static $ary = array ();
        foreach ($files as $file) {
            if ($file == '.' || $file == '..') {
                continue;
            }
            $file = $dir . '/' . $file;
            if (is_dir($file)) {
                self::scandirRecursive($file);
            } else {
                $ary[] = $file;
            }
        }
        return $ary;
        
    }
}

/**
 * @ignore
 * @see file::getFileList
 */

function get_file_list($dir, $options = null){
    return file::getFileList($dir, $options);
}



/**
 * @ignore
 * @deprecated use file::getFileListRecursive($start_dir)
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
                $files = array_merge($files, file::getFileListRecursive($filepath, $pattern));
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
 * transforms bytes into human readable
 * Found on stackoverflow. From kohana.
 * @param int $bytes
 * @param boolean $force_unit
 * @param boolean $format
 * @param type $si
 * @return string $str human readable
 */
function transform_bytes($bytes, $force_unit = NULL, $format = NULL, $si = TRUE)
{
    // Format string
    $format = ($format === NULL) ? '%01.2f %s' : (string) $format;

    // IEC prefixes (binary)
    if ($si == FALSE OR strpos($force_unit, 'i') !== FALSE)
    {
        $units = array('B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB');
        $mod   = 1024;
    }
    // SI prefixes (decimal)
    else
    {
        $units = array('B', 'kB', 'MB', 'GB', 'TB', 'PB');
        $mod   = 1000;
    }

    // Determine unit to use
    if (($power = array_search((string) $force_unit, $units)) === FALSE)
    {
        $power = ($bytes > 0) ? floor(log($bytes, $mod)) : 0;
    }

    return sprintf($format, $bytes / pow($mod, $power), $units[$power]);
}

/**
 * rm dir recursively
 * @param string $dir 
 */
function rrmdir($dir) {
    file::rrmdir($dir);
}
