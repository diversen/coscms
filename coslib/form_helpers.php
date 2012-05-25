<?php

class formHelpers {
    public static function getAdminOptions ($url, $id, $options = null) {

        $str = '';
        if (session::isAdmin()) {
            $str.= html::createLink("$url/edit/$id", lang::translate('edit'));
            $str.= MENU_SUB_SEPARATOR;
            $str.= html::createLink("$url/delete/$id",  lang::translate('delete'));
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
}