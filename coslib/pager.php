<?php

/**
 * File contains contains class for doing paging
 *
 * @deprecated Use pearPager instead which is wrapper around PEAR::Pager. 
 * @package    coslib
 */

/**
 * @ignore
 * defining PAGER_PER_PAGE, can be overriden in common.inc in a template dir
 */
if (!defined('PAGER_PER_PAGE')){
    define ('PAGER_PER_PAGE', 10);
}
if (!defined('PAGER_PISPLAY_LINKS')){
    define ('PAGER_DISPLAY_LINKS', 10);
}

/**
 * class for doing paging
 * @deprecated
 * @package    coslib
 */
class pager {
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
    // {{{ public $numPages

    /**
     *
     * @var int  sum of all pages
     */
    public $numPages;

    // }}}
    // {{{ public $url
    /**
     *
     * @var string  base url to be used when showing pager.
     */
    public $url;

    // }}}
    // {{{ public $pagerData = array()

    /**
     *
     * @var array   array with pager data, to be used when generating html
     */
    public $pagerData = array();

    // }}}
    // {{{ public $endUrl = null
    
    /**
     *
     * @var string additions to url e.g. a search term.
     */
    public $urlEnd = null;
    
    // }}}
    // {{{ function __construct($total)
    /**
     *
     * @param int  sum of all links to be paged.
     */
    public function __construct($total){
        $this->total = $total;
        $this->validate();
        if (isset($_GET['query'])){
            $this->url = '/' . $_GET['query'];
        }
        $this->getNumPages();
        $this->setPagerData();
    }

    // }}}
    // {{{ function validate()

    /**
     * validation of pager data, only zero or positive int is allowed
     */
    public function validate(){
        if (!isset($_GET['from'])) $_GET['from'] = 0;
        $this->from = get_zero_or_positive($_GET['from'], $this->total);
    }

    // }}}
    // {{{ function setUrl($url)
    /**
     *
     * @param string the base url to use.
     */
    private function setUrl($url){
        $this->url = $url;
    }

    // }}}
    // {{{ function setUrlEnd($str)

    /**
     *
     * @param string addition to url string e.g. foo=bar
     */
    public function setUrlEnd($str){
        $this->urlEnd = $str;
    }

    // }}}
    // {{{ function getNumPages()

    /**
     * method for calculating sum of pages.
     * calculates number of pages and set $this->numPages.
     */
    private function getNumPages () {
        (int)$this->numPages = $this->total / PAGER_PER_PAGE;

    }

    // }}}
    // {{{ function setPagerData();

    /**
     * method for setting pagerData array
     * fills pagerData array with number of links.
     */
    private function setPagerData(){
        for ($i=1; $i < $this->numPages +1; $i++){
            $this->pagerData[$i] = $i;
        }
    }

    // }}}
    // {{{ function getPagerHTML()

    /**
     * method for getting pager data as html
     *
     * @return string $html containing pager html.
     */
    public function getPagerHTML (){
        $html = '';
        $html.= $this->getHTMLStart();
        if (1 == count($this->pagerData)){
            return '';
        }
        foreach ($this->pagerData as $key => $val){
            $html.= $this->getLink($val);
        }
        $html.= $this->getHTMLEnd();
        return $html;
    }
    
    // }}}
    // {{{ private function getLink($val)
    /**
     * method for getting one link of 1 2 3 4 ...
     *
     * @param   int     containg a link of the paged data
     * @return  string  a link of the pager
     */
    private function getLink($val){
        $urlVal = $val - 1;

        $from = $urlVal * PAGER_PER_PAGE;
        if ($from == $this->from){
            $link = "&nbsp;" . $val;
            return $link;
        }

        $link = "&nbsp;<a href=\"" . $this->url;
        $link.= "?from=" . $from;
        if (isset($this->urlEnd)){
            $link.= '&' . $this->urlEnd;
        }
        $link.= '">' . $val . "</a>";
        return $link;

    }

    // }}}
    // {{{ private function getHTMLStart()

    /**
     * method for getting the html start as string
     *
     * @return string html start of pager
     */
    private function getHTMLStart () {
        $str = '<div class ="pager">' . "\n";
        return $str;
    }

    // }}}
    // {{{ private function getHTMLEnd()

    /**
     * method for getting closing html tags for pager.
     *
     * @return   string    end of pager html.
     */
    private function getHTMLEnd(){
        $str = '</div>' . "\n";
        return $str;
    }

    // }}}
}
