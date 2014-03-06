<?php

use google\appengine\api\cloud_storage\CloudStorageTools;

class upload_gcs {

    public function createUploadUrl() {
        

        //$action = $this->getActionDefault($options);
        
        $options = [ 'gs_bucket_name' => 'my_bucket' ];
        $action = CloudStorageTools::createUploadUrl($_SERVER['REQUEST_URI'], $options);
        return $action;
        
    }
    
}
