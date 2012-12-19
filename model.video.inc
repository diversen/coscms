<?php

/**
 * model file for doing file uploads
 *
 * @package     content
 */

/**
 * @ignore
 */
include_once "coslib/upload.php";

/**
 * class content video is used for keeping track of file changes
 * in db. Uses object fileUpload
 */
class video {

    public static $errors = null;
    public static $status = null;
    public static $parent_id;
    public static $fileId;
    public static $maxsize = 2000000; // 2 mb max size
    public static $options = array();
    public static $fileTable = 'video';

    /**
     * constructor sets init vars
     */
    function __construct($options = null){
        $uri = URI::getInstance();
        self::$options = $options;
        
        if (!isset($options['maxsize'])) {
            $maxsize = config::getModuleIni('video_max_size');
            if ($maxsize) {
                self::$options['maxsize'] = $maxsize;
            }
        }
    }

    public static function setFileId ($frag){
        self::$fileId = uri::$fragments[$frag];
    }

   /**
    * method for creating a form for insert, update and deleting entries
    * in module_system module
    *
    *
    * @param string    method (update, delete or insert)
    * @param int       id (if delete or update)
    */
    public function viewFileForm($method, $id = null, $values = array(), $caption = null){
     
        $values = html::specialEncode($values);
        html::formStart('file_upload_form');
        if ($method == 'delete' && isset($id)) {
            $legend = lang::translate('file_delete_label');
            html::legend($legend);
            html::submit('submit', lang::translate('system_submit_delete'));
            html::formEnd();
            echo html::getStr();
            return;
        }
        
        $legend = '';
        if (isset($id)) {
            $values = self::getSingleFileInfo($id);
            html::init($values, 'submit'); 
            $legend = lang::translate('file_edit_legend');
            $submit = lang::system('system_submit_update');
        } else {
            $legend = lang::translate('file_add_legend');
            $submit = lang::system('system_submit_add');
        }
        
        html::legend($legend);
        html::label('abstract', lang::translate('file_abstract_label'));
        html::textareaSmall('abstract');
        
        $bytes = config::getModuleIni('video_max_size');
        html::fileWithLabel('file', $bytes);

        html::submit('submit', $submit);
        html::formEnd();
        echo html::getStr();
        return;
    }
    
    /**
     * method for inserting a module into the database
     * (access control is cheched in controller file)
     *
     * @return boolean true on success or false on failure
     */
    public function insertFile () {
        
        $values = db::prepareToPost();
        
        $db = new db();
        $res = $this->uploadVideo();
        if ($res) {
            try {
                $db->begin();
           
                $values['parent_id'] = self::$options['parent_id'];
                $values['reference'] = self::$options['reference'];
                $values['title'] = $_FILES['file']['name'];
                $values['mimetype'] = $_FILES['file']['type'];
                $values['full_path'] = self::$options['flv_file_path'];
                $values['web_path'] = self::$options['flv_web_path'];
                $db->insert(self::$fileTable, $values);
                $db->commit();

            } catch (Exception $e) {
                $db->rollback();
                echo $e->getMessage();
                return 0;
            }
        } else {
            return false;
        }
        
        return 1;
    }
    
    public static function subModuleInlineContent($options){
        moduleLoader::includeTemplateCommon('flowplayer');
        flowplayer_include();
        $info = video::getAllVideoInfo($options);

        $str = '';
        foreach ($info as $video) {            
            $str.= flowplayer_get_html($video['web_path']);
        }
        return $str;
    }
    
    
    function uploadVideo () {
        
        $options = array ();
        $options['maxsize'] = config::getModuleIni('video_max_size');
        
        upload::setOptions($options);
        $res = upload::checkUploadNative('file');
        if (!$res) {
            self::$errors = upload::$errors;
            return false;
        }
        
        $res = upload::checkMaxSize('file');
        if (!$res) {
            self::$errors = upload::$errors;
            return false;
        }
        
        // transform to flv
        $tmp_flv = $flv_file = null;
        if (isset($_FILES['file'])) { 
            $flv_file = file::getFilename($_FILES['file']['name']) . ".flv";
            $tmp_flv = "/tmp/" . $flv_file; 
            $res = $this->transformToFlv($_FILES['file']['tmp_name'], $tmp_flv);
            if (!$res) {
                self::$errors[] = lang::translate('video_could_not_transform_to_flv');
                return false;
            } 
        }
        
        $web_dir = "/video/" . self::$options['reference'] . "/" . self::$options['parent_id'];        
        $web_dir = config::getWebFilesPath($web_dir);
        $full_path = _COS_HTDOCS . "" . $web_dir;
        if (!file_exists($full_path)) {
            mkdir($full_path, 0777, true);
        }
        
        $flv_web_path = $web_dir . "/" . $flv_file;
        $flv_full_path = $full_path . "/" . $flv_file;
        
        $res = copy($tmp_flv, $flv_full_path);
        self::$options['flv_file_path'] = $flv_full_path;
        self::$options['flv_web_path'] = $flv_web_path;
        
        unlink ($tmp_flv);
        return $res;
    }
    
    function transformToFlv ($full_filename, $full_filename_flv) { 
        set_time_limit(0);
        //$ffmpeg_command = "ffmpeg -i \"$full_filename\" -ar 44100 -ab 96 -f flv \"$full_filename_flv\";";
        $ffmpeg_command = "ffmpeg -i \"$full_filename\" -ar 22050 -qscale 10 -f flv \"$full_filename_flv\";";
        //
        $output = array();
        exec($ffmpeg_command, $output, $ret);
        if ($ret) {
            return false;
        }
        // unlink file 
        unlink($full_filename);
        return true;
    }

    /**
     * method for validating a post before insert
     */
    public function validateInsert($mode = false){
        if (empty($_FILES['file']['name'])){
            self::$errors[] = lang::translate('video_no_file_specified');
        } 
    }


    /**
     * method for delting a file
     *
     * @param   int     id of file
     * @return  boolean true on success and false on failure
     *
     */
    public function deleteFile($id){
        $row = $this->getSingleFileInfo($id);
        
        $file = _COS_HTDOCS . "" . $row['web_path'];
        if (file_exists($file)) {
            unlink($row['full_path']); //filename)
        }
        $db = new db();
        $res = $db->delete(self::$fileTable, 'id', $id);
        return $res;
    }
    

    
     /**
     * method for adding pre content when video is used as a sub module. 
     * e.g. in content or blog. 
     * @param type $options
     * @return string   content to be displayed
     */
    public static function subModuleAdminOption ($options){
        $str = "";
        $url = moduleLoader::buildReferenceURL('/video/add', $options);
        $add_str= lang::translate('video_add');
        $str.= html::createLink($url, $add_str);
        return $str;
    }
    
    /**
     * method for displaying all video. 
     * @param array $rows
     * @param array $options
     * @return string 
     */
    public static function displayAllVideo($rows, $options){
        $str = '';
       
        foreach ($rows as $key => $val){
            $title = lang::translate('video_download');
            $title.= MENU_SUB_SEPARATOR_SEC;
            $title.= $val['title'];
            
            $link_options = array ('title' => $val['abstract']); 
            $str.= html::createLink(
                       "/video/download/$val[id]/$val[title]", 
                       $title, 
                       $link_options
                   );
            
            // as a sub module the sub module can not know anything about the
            // id of individual video. That's why we will add id. 
            //print_r($options);
            $options['id'] = $val['id'];
            if (isset($options['admin'])){
                $str.= MENU_SUB_SEPARATOR_SEC;
                $url = moduleLoader::buildReferenceURL('/video/edit', $options);
                $str.= html::createLink($url, lang::translate('video_edit'));
                $str.= MENU_SUB_SEPARATOR;
                $url = moduleLoader::buildReferenceURL('/video/delete', $options);
                $str.= html::createLink($url, lang::translate('video_delete'));
            }
            $str.= "<br />\n";
        }
        return $str;
    }

    /**
     * method for getting all info connected to modules.
     *
     * @return array assoc rows of modules belonging to user
     */
    public static function getAllVideoInfo($options){
        $db = new db();
        $search = array (
            'parent_id' => $options['parent_id'],
            'reference' => $options['reference']
        );

        $fields = array ('id', 'full_path', 'web_path', 'parent_id', 'title', 'abstract', 'published', 'created');
        $rows = $db->selectAll(self::$fileTable, $fields, $search, null, null, 'created', false);
        return $rows;
    }
    
    /**
     * method for getting a single video info. 
     * @param int $id
     * @return array $row with info 
     */
    public static function getSingleFileInfo($id = null){
        if (!$id) $id = self::$fileId;
        $db = new db();
        $search = array (
            'id' => $id
        );

        $fields = array ('id', 'parent_id', 'full_path', 'web_path', 'title', 'abstract', 'published', 'created', 'reference');
        $row = $db->selectOne(self::$fileTable, null, $search, $fields, null, 'created', false);
        return $row;
    }


    /**
     * method for fetching one file
     *
     * @return array row with selected video info
     */
    public static function getFile(){
        $db = new db();
        $row = $db->selectOne(self::$fileTable, 'id', self::$fileId);
        return $row;
    }

    /**
     * method for updating a module in database
     * (access control is cheched in controller file)
     *
     * @return boolean  true on success or false on failure
     */
    public function updateFile () {
        
        $bind = array();
        $values['abstract'] = html::specialDecode($_POST['abstract']);

        $db = new db();
        if (!empty($_video['file']['name']) ){
            
            $options = array ();
            $options['filename'] = 'file';
            $options['maxsize'] = self::$options['maxsize'];
            
            $fp = uploadBlob::getFP('file', $options);
            if (!$fp) {
                self::$errors = uploadBlob::$errors;
                return false;
            }
            $values['file'] = $fp;
            $values['title'] = $_video['file']['name'];
            $values['mimetype'] = $_video['file']['type'];

            $bind = array('file' => PDO::PARAM_LOB);
        }
        $res = $db->update(self::$fileTable, $values, self::$fileId, $bind);
        return $res;
    }
    
    /**
     * method to be used in a insert controller
     */
    public function viewFileFormInsert(){
        
        $redirect = moduleLoader::buildReferenceURL('/video/add', self::$options);
        if (isset($_POST['submit'])){
            $this->validateInsert();
            if (!isset(self::$errors)){
                $res = $this->insertFile();
                if ($res){
                    session::setActionMessage(lang::translate('video_file_added'));
                    $header = "Location: " . $redirect;
                    header($header);
                    exit;
                } else {
                    view_form_errors(self::$errors);
                }
            } else {
                view_form_errors(self::$errors);
            }
        }
        $this->viewFileForm('insert');
    }

    /**
     * method to be used in a delete controller
     */
    public function viewFileFormDelete(){
        $redirect = moduleLoader::buildReferenceURL('/video/add', self::$options);
        if (isset($_POST['submit'])){
            if (!isset(self::$errors)){
                $res = $this->deleteFile(self::$fileId);
                if ($res){
                    session::setActionMessage(lang::translate('video_file_deleted'));
                    $header = "Location: " . $redirect;
                    header($header);
                    exit;
                }
            } else {
                view_form_errors(self::$errors);
            }
        }
        $this->viewFileForm('delete', self::$fileId);
    }

    /**
     * merhod to be used in an update controller 
     */
    public function viewFileFormUpdate(){
        $redirect = moduleLoader::buildReferenceURL('/video/add', self::$options);
        if (isset($_POST['submit'])){
            if (!isset(self::$errors)){
                $res = $this->updateFile();
                if ($res){
                    session::setActionMessage(lang::translate('video_file_edited'));
                    $header = "Location: " . $redirect;
                    header($header);
                    exit;
                } else {
                    view_form_errors(self::$errors);
                }
            } else {
                view_form_errors(self::$errors);
            }
        }
        $this->viewFileForm('update', self::$fileId);
    }
}
