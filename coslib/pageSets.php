<?php

class pageSets {
    
    public $options = array ();
    public function __construct($options = array ()) {
        $this->options = $options;
    }
    public function getPrevNext (&$rows, $options = null) {

        $i = 0;
        $current = $_SERVER['REQUEST_URI'];
        $num_rows = count($rows);
        $ary = array();
        $str = '';
        $next_text = $this->getNextText();
        $prev_text = $this->getPrevText();
        foreach ($rows as $key => $val) {
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
    
    public function getTitle () {
        $str = '';
        if (isset($this->options['current_title'])) {
            $str.= ' ' . $this->options['current_title'] . ' ';
        } else {
            $str.= ' ';
        }
        return $str;
    }
    
    public function getLink ($url, $text) {
        if (isset($this->options['attach'])) {
            $url.= $this->options['attach'];
        }
        $str = html::createLink($url, $text);
        return $str;
    }
    
    public function getPrevText ()  {
        if (isset($this->options['prev_text'])) {
            return $this->options['prev_text'];
        } else {
            return '&lt;&lt;';
        }
    }
    
    public function getNextText () {
        if (isset($this->options['next_text'])) {
            return $this->options['next_text'];
        } else {
            return '&gt;&gt;';
        }
    }
}