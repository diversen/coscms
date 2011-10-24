<?php


/**
 * File contains contains class for creating forms from a tables schema
 *
 * @deprecated You can use it but not really used. 
 * @package    coslib
 */

/**
 * @ignore
 */




/**
 * if PAGER_PER_PAGE is not already defined we set it to 10
 * in order to override this define PAGER_PER_PAGE in common.inc in your
 * template dir
 * @ignore
 */
if (!defined('PAGER_PER_PAGE')){   
    define('PAGER_PER_PAGE', 10);
}

/**
 * Class contains contains class for creating forms from a db tables schema
 *
 * @package    coslib
 */
class grid extends db {

    /**
     *
     * @var int the from part of the paging data
     */
    public $from;
    /**
     *
     * @var array array containing headers for grid
     */
    public $headers = array();

    /**
     *
     * @var array  containing table field of grid.
     */
    public $fields = array();

    /**
     *
     * @var array   fieldslinks containg default links array for grid
     *              (e.g. edit or delete) to be added
     */
    public $fieldLinks = array();

    /**
     *
     * @var string the table in database to use
     */
    public $table;

    /**
     *
     * @var string  base url of grid. Used with all links.
     */
    public $baseUrl;

    /**
     *
     * @var     string  orderBy used to set ordering of grid
     */
    public $orderBy = null;

    /**
     *
     * @var int     asc is 1 and  desc is 0
     */
    public $asc = null;

    /**
     *
     * @var object   holding a pager object.
     */
    public $pager;

    /**
     *
     * @var int     number of rows per page (used with pager).
     */
    public $numRows;

    /**
     * @var boolean Hide id field or not
     */
    public $hideId = true;

    /**
     * constructer. Here we set the table to use
     *
     * @param string the table of database to use
     */
    function __construct($table = null){
        if ($table){
            $this->table = $table;
            $this->numRows = $this->getNumRows($this->table);
        }
        $this->baseUrl = '/' . $_GET['query'];
        
        
    }

    /**
     * method for validating the values pased to the pager object.
     * we validate $_GET[from], $_GET[order_by], $_GET[asc]
     */
    function validate(){
        // validate from
        if (!isset($_GET['from'])) $_GET['from'] = 0;
        $this->from = get_zero_or_positive($_GET['from'], $this->numRows);

        // validate order_by
        if (!empty($_GET['order_by'])){
            if (!in_array($_GET['order_by'], $this->fields)){
                // just take first field if not in fields array
                $this->orderBy  = $this->fields[0];
            } else {
                $this->orderBy = $_GET['order_by'];
            }
        } else {
            $this->orderBy = $this->fields[0];
        }
        
        // validate asc
        if (!isset($_GET['asc'])){
            $this->asc = 1;
        } else if (!empty($_GET['asc'])){
            $this->asc = 1;
        } else {
            $this->asc = 0;
        }
    }

    /**
     * method for setting the grids headers
     *
     * @param array headers of the grid
     */
    public function setHeaders($headers){
        $this->headers = $headers;
    }

    /**
     * method for setting the fields we want to display in our grid.
     *
     * @param   array   fields of the database table to use in grid
     */
    public function setFields($fields){
        $this->fields = $fields;
    }

    /**
     * method for setting field link to be added to the grid
     * (e.g. a edit and delete link)
     *
     * @param array     the link to be added  e.g. <code>
     *                  array ('base' => '/grid/edit/',
     *                       'name' => lang::translate('Edit'),
     *                       'id' => 'id' );</code>
     */
    public function setFieldLink($link){
        $this->fieldLinks[] = $link;
    }

    /**
     * method for setting the base url
     *
     * @param   string   base url to use with all links in grid
     *                   (e.g. if we use it as amodule '/grid/list'
     */
    public function setBaseUrl($url){
        $this->baseUrl = $url;
    }

    /**
     * method for getting a string all field links with <code><td>link</td></code>
     * will return it as html
     *
     * @param   array   assoc row from database
     * @return  string  containing a field link e.g. a edit or delete link
     */
    private function getFieldLink($row){
        if (empty($this->fieldLinks)){
            return '';
        }
        $str = '';
        foreach ($this->fieldLinks as $key => $val){
            $str.= "<td><a href=\"$val[base]" . $row[$val['id']] . "\">$val[name]</a>\n";
            $str.= "</td>";
        }
        return $str;
    }

    /**
     * method for getting a single row of html containing grid html
     *
     * @param   array   assoc row from database query
     * @return  string  string containing a row in the grid
     */
    private function getRowHTML($row){
        $str = '';
        if (!empty($row)){
            $str.= "<tr>\n";
            foreach($row as $key => $val){
                if ($this->hideId && $key == 'id') continue;
                $str.= "<td>" . $val . "</td>\n";
                
            }
            // getFieldLink (e.g. edit or delete) for current row
            $str.= $this->getFieldLink($row);
            $str.= "</tr>\n";
        }
        return $str;
    }

    /**
     * method for getting the headers of the table
     *
     * @param   array   array with the headers
     * @return  string  the row header html
     */
    private function getHeaderRowHTML($headers){
        $str = '';
        if (!empty($headers)){
            $str.= "<tr>\n";
            foreach($headers as $key => $val){
                if ($this->hideId && $key == 'id') continue;
                $str.= "<th>" . $this->setHeaderLink($key, $val) . "</th>\n";
            }
            foreach($this->fieldLinks as $key => $val){
                $str.= "<th>&nbsp;</th>\n";
            }

            $str.= "</tr>\n";
        }
        return $str;
    }

    /**
     * method
     *
     * @param   string  key of the header link to set
     * @param   string  value of the header link (unused for now)
     * @return  string  str containg header link to be used in grid
     */
    private function setHeaderLink($key, $val){
        if (empty($this->asc)){
            $asc = 1;
        } else {
            $asc = 0;
        }
        $str = "";
        $str.= "<a href=\"$this->baseUrl";
        $str.= "?order_by=" . $this->fields[$key];
        $str.= "&asc=$asc&from=$this->from";
        $str.= '">';
        $str.= $this->headers[$key];
        $str.= "</a>\n";
        return $str;

    }

    /**
     * method for getting grid data from database
     *
     * @return  string  containing html
     */
    private function getGridData(){
        $str = '';      
        $rows = $this->selectAll($this->table, $this->fields,  null, $this->from, PAGER_PER_PAGE, $this->orderBy, $this->asc);
        foreach ($rows as $key => $val){
            $str.= $this->getRowHTML($val);
        }
        return $str;
    }
    /**
     * method for getting the grid html
     *
     * @return string   containing grid html
     */
    public function getGridHTML(){
        // page grid
        $this->validate();
        $this->pager = new pager($this->numRows);
        $str = "order_by=$this->orderBy&asc=$this->asc";
        $this->pager->setUrlEnd($str);

        $str = '';
        $str = $this->getHTMLStart();
        if (!empty($this->headers)){
            $str .= $this->getHeaderRowHTML($this->headers);
        }
        if (!empty($this->fields)){
            $str .= $this->getGridData();
        }
        $str.= $this->getHTMLEnd();
        $str.= $this->pager->getPagerHTML();
        return $str;
    }



    /**
     * method for getting start of html table with div
     *
     * @return  string  containing start of grid
     */
    private function getHTMLStart () {
        $str = '<div id ="tabledebug">' . "\n";
        $str.= '<table width="100%">';
        return $str;
    }

    /**
     * method for getting end of html table
     *
     * @return  string  containing end of grid
     */
    private function getHTMLEnd(){
        $str = '';
        $str.= "</table>\n";
        $str.= "</div>\n";
        return $str;
    }
}
