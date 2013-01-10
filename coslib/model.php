<?php

/**
 * file contains common elements to models
 * So far only a var for holding errors
 */

class model {   
    
    public $unique = '';
    public $errors = array ();
    public $postFields = array ();
    public $postOptions = array ();
    public $encoded = false;
    public function __construct() {
        $this->postEncode();
    }
    
    public function postEncode () {
        if (!empty($_POST) && !$this->encoded) {
            $_POST = html::specialEncode($_POST);
            $this->encoded = true;
        }
    }
}
