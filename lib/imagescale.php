<?php

/**
 * File for class imagescale
 * @package coslib
 */

/**
 * class imagescale is a simple wrapper around pear::Image_Transform
 * 
 */

include_once 'Image/Transform.php';
class imagescale {
    
    /**
     * 
     * @var array   $options options given to pear::Image_Transform 
     */
    public static $options = null;
    
    /**
     * var for holding errors. In must cases they are just uaed
     * to give end users info about what was done wrong. 
     * All errors are logged
     * @var array   $errors the array holding the errors
     */
    public static $errors = array();
    
    /**
     * 
     * method for settings options
     * @param type $_options can be a array like this: $options = array(
     *  'quality'     => 75,
     *  'scaleMethod' => 'smooth',
     *  'canvasColor' => array(255, 255, 255),
     *  'pencilColor' => array(0, 0, 0),
     *  'textColor'   => array(0, 0, 0)
     *  ); 
     * 
     */
    
    public static function setOptions($options) {
        self::$options = $options;
    }
    
    public static $validMime = 
        array ('image/gif', 'image/jpeg', 'image/pjpeg', 'image/png');

    /**
     *
     * @param string $image image file to scale
     * @param string $thumb destination file to scale to
     * @param int    $x the x factor of the scaled image. 
     * @return boolean true on success and false on failure
     *                 human errors will be set in self::$errors 
     */
    public static function byX ($image, $thumb, $x){
        //create transform driver object
        $it = Image_Transform::factory('GD');
        if (isset(self::$options)) {
            $it->_options = self::$options;           
        }
        
        if (PEAR::isError($it)) {
            self::$errors[] = lang::translate('system_scaleimage_transform_factory_exception');
            cos_error_log($it->getMessage());
            return false;
        }

        //load the original file
        $ret = $it->load($image);
        if (PEAR::isError($ret)) {
            self::$errors[] = lang::translate('system_scaleimage_transform_load_exception');
            cos_error_log($ret->getMessage());
            return false;
        }

        $ret = $it->scaleByX($x);
        if (PEAR::isError($ret)) {
            self::$errors[] = lang::translate('system_scaleimage_image_transform_scale_exception');
            cos_error_log($ret->getMessage());
            return false;
        }

        $ret = $it->save($thumb);
        if (PEAR::isError($ret)) {
            self::$errors[] = lang::translate('system_scaleimage_factory_transform_save_exception');
            cos_error_log($ret->getMessage());
            return false;
        }
        return true;
    }
}