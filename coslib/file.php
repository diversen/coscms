<?php

/**
 * package contains file class for doing common file tasks
 * @package coslib
 * 
 */

/**
 * class for doing common file tasks
 * @package coslib
 */
class file {
    
    /**
     * function for getting a file list of a directory (. and .. will not be
     * collected)
     *
     * @param   string  the path to the directory where we want to create a filelist
     * @param   array   if <code>$options['dir_only']</code> isset only return directories.
     *                  if <code>$options['search']</code> isset then only files containing
     *                  search string will be returned
     * @return  array   entries of all files <code>array (0 => 'file.txt', 1 => 'test.php');</code>
     */
    public static function getFileList ($dir, $options = null) {
        return get_file_list($dir, $options);
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
     * method for getting extension of a file
     * @param string $filename
     * @return string $extension
     */
    public static function getExtension ($filename) {
        return $ext = substr($filename, strrpos($filename, '.') + 1);
    }
    
    public static function getFilename ($file)  {
        $info = pathinfo($file);
        //$file_name =  basename($file,'.'.$info['extension']);
        return $info['filename'];
        //return $file_name;
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
    
    public static function mkdir ($dir) {
        $full_path = config::getFullFilesPath();
        $dir = $full_path . "$dir";
        
        if (file_exists($dir)) {
            return false;
        }
        $res = @mkdir($dir, 0777, true);
        return $res;
    }
}
/*
 * @deprecated
 * @see file::getFileList
 */

function get_file_list($dir, $options = null){
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
            $entries[] = $entry;
        }
    }
    $d->close();
    return $entries;
}

/**
 * @deprecated use file::getFileListRecursive($start_dir)
 * 
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

// Found on stackoverflow. From kohana. 
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