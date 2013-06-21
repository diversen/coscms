<?php

class moduleloader_reference extends moduleloader {
            
    /**
     * method for including a reference module
     * @param int $frag_reference_id
     * @param int $frag_id
     * @param string $frag_reference_name
     */
    public static function includeRefrenceModule (
            $frag_reference_id = 2, 
            
            // reserved. Will be set by the module in reference
            // e.g. will be set in files when used in content.
            
            $frag_id = 3,
            $frag_reference_name = 4) {    
        
        $reference = uri::$fragments[$frag_reference_name];  
        $id = uri::$fragments[$frag_id]; 
        $extra =  uri::getInstance()->fragment($frag_reference_name +1); 
        
        if (isset($extra) && !empty($extra)) {
            $reference.= "/$extra";
        }
        
        // normal this will not be set. 
        // because imagine this situation
        // $id = uri::$fragments[$frag_id];
        $reference_id = uri::$fragments[$frag_reference_id];

        if (!isset($reference)){
            return false;
        }
        
        $res = moduleloader::includeModule($reference);
        if ($res) {
            // transform a reference (e.g. content/article) into a class name
            // (content_article_module)
            $class = moduleloader::modulePathToClassName($reference);
            self::$reference = $reference;
            self::$id = $id;
            self::$referenceId = $reference_id;
            
            // we only need this if we just need a link to point to 'parent' module
            if (method_exists($class, 'getLinkFromId')) {            
                self::$referenceLink = $class::getLinkFromId(
                    self::$referenceId, self::$referenceOptions);
            }
            
            // we need this if we want to redirect if a submission was valid
            if (method_exists($class, 'getRedirect')) {
                self::$referenceRedirect = $class::getRedirect(
                    self::$referenceId, self::$referenceOptions);
                
                // check if url is a rewritten one
                self::$referenceRedirect = html::getUrl(self::$referenceRedirect);
            }
            return true;
        }
        return false;
    }
    
    /** 
     * return all set reference info as an array 
     * @return array $ary array 
     *                      (parent_id, inline_parent_id, reference, link, redirect)
     */
    public static function getReferenceInfo () {
        $ary = array ();
        $ary['parent_id'] = self::$referenceId;
        $ary['inline_parent_id'] = self::$id;
        $ary['reference'] = self::$reference;
        $ary['link'] = self::$referenceLink;
        $ary['redirect'] = self::$referenceRedirect;
        $ary['options'] = self::$referenceOptions;
        return $ary;
    }
}