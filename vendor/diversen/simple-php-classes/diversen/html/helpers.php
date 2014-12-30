<?php

namespace diversen\html;
use diversen\date; 
use diversen\session;
use diversen\html;
use diversen\lang;
/**
 * File containing class for html helpers
 * @package html 
 */

/**
 * Class for html helpers
 * @package html
 */

class helpers {
        
    /**
     * method for getting admin options
     * @param string $url base url
     * @param string $id the item
     * @param string $options
     * @return string $str menu options
     */
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
    
    /**
     * method for generating a delete confirmform
     * @param string $name the name of the submit button
     * @param string $legend the legend of the form
     * @return string $form the html form.
     */
    public static function confirmDeleteForm ($name = 'submit', $legend = 'delete') {
        
        $html = new html ();
        $html->setAutoEncode(true);
        $html->formStart('custom_delete_form');
        $html->legend($legend);
        $html->submit($name, lang::translate('delete'));
        $html->formEnd(); 
        return $html->getStr();
    }
   
    /**
     * method for creating a confirm form
     * @param string $legend text of the legend
     * @param string $submit text of the submit
     * @return string $form the confirm form.
     */
    public static function confirmForm ($legend, $submit_value = null, $submit_name = 'submit') {
        
        $html = new html();
        $html->setAutoEncode(true);
        $html->formStart('custom_delete_form');
        $html->legend($legend);
        if (!$submit_value) {
            $submit_value = lang::translate('submit');
        }
        $html->submit($submit_name, $submit_value);
        $html->formEnd(); 
        return $html->getStr();
    }   
    
    /**
     * method that creates birthday dropdown
     * access of the submitted data can be found in the _POST['birth_day'],
     * $_POST['birth_month'], $_POST['birth_year']
     * @param string $name name of the form element
     * @param array $init the init array 
     * @return array $ary array with select elements in array ('day', 'month', 'year')
     */
    public static function birthdayDropdown ($name = 'birth', $init = array ()) {
        for ($i = 1; $i <= 31; $i++) {
            $days[$i] = array ('id' => $i, 'value' => $i);
        }
        
        for ($i= 1; $i <= 12; $i++) {
            $months[$i] = array (
                'id' => $i,
                'value' =>  dateGetMonthName($i)
            );
        }
        $currentYear = date::getCurrentYear();
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
                $days, 'id', 'value');
        
        $month = html::selectClean(
                'birth_month', 
                $months, 'id', 'value');
        
        $year = html::selectClean(
                'birth_year',
                $years, 'id', 'value');
        
        $ret = array (
            'day' => $day, 'month' => $month, 'year' => $year,
            'day_options' => $days, 'month_options' => $months, 'year_options' => $years);
        return $ret;
    }
    
    /**
     * get birthday form _REQUEST
     * @return string $date
     */
    public static function getBirthdayAsDate () {
        if (isset($_REQUEST['birth_day'])) {
            $date = $_REQUEST['birth_year'] . '-' .
                    $_REQUEST['birth_month'] . '-' .
                    $_REQUEST['birth_day'];
            return $date;
        }
        return null;        
    }
}
