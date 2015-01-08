<?php

namespace diversen\cli;

/**
 * class for validating options
 */
class optValid {

    /**
     * splits string with -value and --values
     * @param string $str opt string
     * @return array $opts
     */
    public function split($str) {
        $str = trim($str);
        $str = " " . $str;

        // get opts raw which means space- or space--
        $opts = array_filter(preg_split("/[\s+][-]{1,2}/", $str));
        return $opts;
    }

    /**
     * from all opts we get a array of arrays with values of
     * '0' the command '1' the value
     * @param array $opts
     * @return array $ret
     */
    public function getAry($opts) {

        $ret = array();
        foreach ($opts as $opt) {
            $opt = trim($opt);
            // space args, e.g. -V test=7
            $ary = preg_split("/[\s+]/", $opt);
            if (!isset($ary[1])) {
                // equal args e.g. --chapters=7
                $ret[] = preg_split("/[=]/", $opt);
            } else {
                $ret[] = $ary;
            }
        }
        return $ret;
    }
    
    /**
     * sets an array with sub commands, e.g.
     * -V val=test
     * @param type $ary
     * @return type
     */
    public function setSubVal ($ary) {
        foreach ($ary as $key => $opt) {
            if (isset($opt[1])) {
                $val = preg_split("/[=]/", $opt[1]);
                if (isset($val[1])) {
                    $ary[$key][2] = $val;
                }
            }
        }
        return $ary;
    }
    
    public $errors = array ();
    /**
     * checks if all commands are valid
     * @param array $ary
     * @param array $allow
     */
    public function isValid($ary, $allow) {
        foreach ($ary as $key => $val) {
            $opt = $val[0];
            
            // check if option main option if OK
            if (!array_key_exists($opt, $allow)) {
                $this->errors[] = $opt;
            }
            
            // sub option
            if (isset($val[2])) {
                $sub_opt = $val[2][0];
                if (!in_array($sub_opt, $allow[$opt])) {
                    $this->errors[] = $sub_opt;
                }
            }
        }

        if (empty($this->errors)) {
            return true;
        }
        return false;
    }
}
