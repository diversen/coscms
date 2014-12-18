<?php

namespace diversen;
use diversen\lang;
/**
 * File contains contains class for doing paging
 * @package    paginate
 */

/**
 * @ignore
 * defining PAGER_PER_PAGE, can be overriden in common.inc in a template dir
 */

if (!defined('PAGER_PER_PAGE')){
    define ('PAGER_PER_PAGE', 10);
}

include_once "Pager.php";
use Pager as PearPager;


/**
 * class for doing paging. It is a wrapper around PEAR::Pager
 *
 * @package    paginate
 */
class pagination {
    /**
     * var holding total number of links to be paged.
     * @var int $total
     */
    public $total;

    /**
     * var holding from where we start paging
     * @var int $from
     */
    public $from = 0;
    
    /**
     * var holding who many items per page
     * @var int $perPage
     */
    public $perPage = null;

    /**
     * constructor
     * @param int $total sum of all items to be paged.
     * @param int $per_page
     */
    public function __construct($total, $per_page = null){
        
        if (isset($per_page)){
            $this->perPage = $per_page;
        } else {
            $this->perPage = PAGER_PER_PAGE;
        }
        $this->total = $total;
        $this->validate();

    }

    /**
     * wrapper around the pear pager. 
     * you can supply your own options
     * @param array $options 
     */
    public function getPagerHTML ($options = null){

        //require_once 'Pager/Pager.php';
     
        //first, we use Pager to create the links
        $num_items = $this->total;
        $uri_ary = explode('?', $_SERVER['REQUEST_URI']);
        $uri = $uri_ary[0];

        /*
        if (moduleloader::isInstalledModule('rewrite_manip')) {
            $alt_uri = rewrite::getRowFromRequest($uri);
            if (isset($alt_uri)){
                $uri = $alt_uri; //$row['rewrite_uri'];
            }
        }*/

        $filename = $uri . '?from=' . '%d' . '&';

        if (isset($options['add_extra'])){
            $filename.=$options['add_extra'];
        }
        
        $filename.= $this->getGetParams();

        if (isset($options['pager_per_page'])){
            $this->perPage = $options['pager_per_page'];
        } 
        
        // set options       
        $pager_options = array(
            'altPrev' => lang::translate('pager_prev_page'),
            'altNext' => lang::translate('pager_next_page'),
            'altPage' => lang::translate('pager_page'),
            'separator' => '',
            'mode'       => 'Sliding',
            'perPage'    => $this->perPage,
            'delta'      => 5,
            'urlVar'    => 'from', 
            'append'   => false,
            'path'     =>  '',
            'fileName' => $filename,
            'totalItems' => $num_items,
        );

        //$p = new Pager();
        //$p = new PearPager();
        $pager =  @PearPager::factory($pager_options);
        $str = '';
        $str.='<div class="pager_wrap">';
        $str.= "<div class =\"pager\">" . $pager->links . "</div>\n" ;
        $str.='</div>';
        return $str;

    }
    
    /**
     * wrapper around the pear pager. 
     * you can supply your own options
     * @param array $options 
     */
    public function pearPage ($options = null){
        echo $this->getPagerHTML($options);
    }
    
    /**
     * gets a max int or zero from an int and a max int. 
     * @param int $val the var to get max int from
     * @param int $max max int to return
     * @return int $val
     */
    public function getPositiveInt ($val, $max) {
        $val = filter_var($val, FILTER_VALIDATE_INT, array(
            'options' => array('min_range' => 0, 'max_range' => $max)
        ));
        if (!$val){
            $val = 0;
        }
        return $val;
    }
    
    /**
     * method for adding $_GET params to final query string
     */
    public function getGetParams () {
        $str= '';
        foreach ($_GET as $key => $val) {
            if ($key == 'from') continue;
            if ($key == 'q') continue;
            if (is_array($val)) {
                foreach ($val as $k => $v) {
                    $str.= $key . '[]='. $v . '&';
                }
                continue;
            }
            $str.= "$key=$val&";
        }
        return $str;
    }

    /**
     * validation of pager data, only zero or positive int is allowed
     */
    public function validate(){
        if (!isset($_GET['from'])) { 
            $this->from = 0; 
        } else {
            $this->from = $this->getPositiveInt($_GET['from'], $this->total);
        }

        if ($this->from > 0){
            $this->from = ($this->from - 1) * $this->perPage;
        }
    }
    
    /**
     * return pagination page
     * @return int $from 
     */
    public static function getPageNum () {
        if (!isset($_GET['from'])) { 
            $from = 1;
        } else if ($_GET['from'] == 0) {
            $from = 1;
        } else if ($_GET['from'] > 0) {
            $from = $_GET['from'];
        } else {
            $from = 0;
        }
        return (int)$from;
        
    }
}
