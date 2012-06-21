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
    
    public static function birthdayDropdown () {
        for ($i = 1; $i <= 31; $i++) {
            $days[$i] = array ('id' => $i, 'value' => $i);
        }
        
        for ($i= 1; $i <= 12; $i++) {
            $months[$i] = array (
                'id' => $i, 
                'value' => dateGetMonthName($i)
            );
        }
        $currentYear = dateGetCurrentYear();
        $goBack = 120;
        //for ($i = $goBack; $goBack < $currentYear;  $currentYear-- ) {
        while($goBack) {
            $years[$currentYear] =  array (
                'id' => $currentYear,
                'value' => $currentYear);
            
            $currentYear--;
            $goBack--;
        }  
        
        $day = html::selectClean(
                'birth_day', 
                $days, 'id', 'value', null);
        
        $month = html::selectClean(
                'birth_month', 
                $months, 'id', 'value', null);
        
        $year = html::selectClean(
                'birth_year',
                $years, 'id', 'value', null);
        
        $ret = array ('day' => $day, 'month' => $month, 'year' => $year);
        return $ret;
    }
}
