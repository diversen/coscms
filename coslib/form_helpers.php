<?php

class formHelpers {
    public static function getAdminOptions ($url, $id, $options = null) {

        $str = '';
        if (session::isAdmin()) {
            $str.= html::createLink("$url/edit/$id", lang::system('edit'));
            $str.= MENU_SUB_SEPARATOR;
            $str.= html::createLink("$url/delete/$id",  lang::system('delete'));
        }
        
        if (isset($options['view'])) {
            $str.= MENU_SUB_SEPARATOR;
            $str.= html::createLink("$url/view/$id",  lang::system('view'));
        }
        return $str;
    }
    
    public static function confirmDeleteForm ($legend) {
        
        $html = new HTML ();
        $html->setAutoEncode(true);
        $html->formStart('custom_delete_form');
        $html->legend($legend);
        $html->submit('submit', lang::translate('delete'));
        $html->formEnd(); 
        return $html->getStr();
    }
    
   public static function confirmForm ($legend, $submit = null) {
        
        $html = new HTML ();
        $html->setAutoEncode(true);
        $html->formStart('custom_delete_form');
        $html->legend($legend);
        if (!$submit) {
            $submit = lang::translate('submit');
        }
        $html->submit('submit', $submit);
        $html->formEnd(); 
        return $html->getStr();
    }
}