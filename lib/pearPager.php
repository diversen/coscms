<?php

/**
 * File contains contains class for doing paging
 *
 * @package    coslib
 */

/**
 * defining PAGER_PER_PAGE, can be overriden in common.inc in a template dir
 */
if (!defined('PAGER_PER_PAGE')){
    define ('PAGER_PER_PAGE', 10);
}


/**
 * class for doing paging
 *
 * @package    coslib
 */
class pearPager {
    // {{{ public $total
    /**
     *
     * @var int total number of links to be paged.
     */
    public $total;

    // }}}
    // {{{ public $from = 0
    /**
     *
     * @var int where from do we start paging
     */
    public $from = 0;

    // }}}
    
    // {{{ function __construct($total)
    /**
     *
     * @param int  sum of all links to be paged.
     */
    public function __construct($total){
        $this->total = $total;
        $this->validate();
    }

    // }}}
    //

    public function pearPage ($options = null){

        require_once 'Pager/Pager.php';

        

        

        //first, we use Pager to create the links
        $num_items = $this->total;
        $uri_ary = explode('?', $_SERVER['REQUEST_URI']);
        $uri = $uri_ary[0];

        if (class_exists('rewrite_manip')) {
            $alt_uri = rewrite_manip::getRowFromRequest($uri);
            if (isset($alt_uri)){
                $uri = $alt_uri; //$row['rewrite_uri'];
            }
        }

        $filename = $uri . '?from=' . '%d';

        if (isset($options['add_extra'])){
            $filename.=$options['add_extra'];
        }
        
        
        // set options
        
        $pager_options = array(
            'altPrev' => lang::translate('pager_prev_page'),
            'altNext' => lang::translate('pager_next_page'),
            'altPage' => lang::translate('pager_page'),
            'mode'       => 'Sliding',
            'perPage'    => PAGER_PER_PAGE,
            'delta'      => 2,
            'urlVar'    => 'from', 
            'append'   => false,
            'path'     =>  '',
            'fileName' => $filename,
            'totalItems' => $num_items,
        );

        $pager = Pager::factory($pager_options);
        echo "<hr />\n";
        echo "<div id =\"pager\"" . $pager->links . "</div>\n" ;

    }

    // {{{ function validate()

    /**
     * validation of pager data, only zero or positive int is allowed
     */
    public function validate(){
        if (!isset($_GET['from'])) $_GET['from'] = 0;
        $this->from = get_zero_or_positive($_GET['from'], $this->total);
        if ($this->from > 0){
            $this->from = ($this->from - 1) * PAGER_PER_PAGE;
        }
    }
    // }}}
}