<?php

namespace diversen\pagination;
use diversen\html;

/**
 * file contains class for creating simple paging 
 * for e.g. images with prev next
 * @package paginate
 */

/**
 * class for creating simple paging 
 * for e.g. images with prev next
 * @package paginate
 */
class sets {
    
    /**
     * var holding options
     * @var array $options
     */
    public $options = array ();
    
    /**
     * constructor
     * @param array $options
     */
    public function __construct($options = array ()) {
        $this->options = $options;
    }
    
    /**
     * get prev next from db table rows
     * @param array $rows
     * @param array $options
     * @return string $html string
     */
    public function getPrevNext (&$rows, $options = null) {

        $i = 0;
        $current = $_SERVER['REQUEST_URI'];
        $num_rows = count($rows);
        $str = '';
        $next_text = $this->getNextText();
        $prev_text = $this->getPrevText();
        foreach ($rows as $key => $val) {
            if ($num_rows == 1) return '';
            
            if ($current == $val && $key == 0) {
                // current is first
                $next_url = $rows[$key+1];
                $str.= $this->getTitle();
                $str.= $this->getLink($next_url, $next_text); 
                return $str;
            } 
            
            if ($current == $val && $key == ($num_rows -1) ) {
                // current is last
                $prev_url = $rows[$key - 1];
                
                $str.= $this->getLink($prev_url, $prev_text);
                return $str;
            } 
            
            if ($current == $val) {
                $prev_url = $rows[$i-1];
                $str.= $this->getLink($prev_url, $prev_text);
                $str.= $this->getTitle();
                
                $next_url = $rows[$i+1];
                
                $str.= $this->getLink($next_url, $next_text);
                return $str;
            }
            $i++;
        }
    }
    
    /**
     * gets title
     * @return string $title 
     */
    public function getTitle () {
        $str = '';
        if (isset($this->options['current_title'])) {
            $str.= ' ' . $this->options['current_title'] . ' ';
        } else {
            $str.= ' ';
        }
        return $str;
    }
    
    /**
     * gets link
     * @param string $url
     * @param string $text
     * @return string $str html 
     */
    public function getLink ($url, $text) {
        if (isset($this->options['attach'])) {
            $url.= $this->options['attach'];
        }
        $str = html::createLink($url, $text);
        return $str;
    }
    
    /**
     * get prev text
     * @return string $prev text 
     */
    public function getPrevText ()  {
        if (isset($this->options['prev_text'])) {
            return $this->options['prev_text'];
        } else {
            return '&lt;&lt;';
        }
    }
    
    /**
     * get next text
     * @return string $next text 
     */
    public function getNextText () {
        if (isset($this->options['next_text'])) {
            return $this->options['next_text'];
        } else {
            return '&gt;&gt;';
        }
    }
}
