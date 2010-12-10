<?php

/**
 * Some helper functions to use with shell.php
 *
 * @package     shell
 */

// {{{ function cos_readline($line)
/**
 * funtion for wrapping the readline function
 * found on php.net 
 *
 * @param string ouput to print to screen
 * @return string the input which readline reads
 */
function cos_readline($str){
    if (function_exists('readline') ){
        $out = readline($str);
        return $out;
    } else {
        // if not readline we use this
        // we could actually just use this .)
        // found at php.net
        print $str;
        $out = "";
        $key = "";
        $key = fgetc(STDIN);        //read from standard input (keyboard)
        while ($key!="\n") {       //if the newline character has not yet arrived read another
            $out.= $key;
        $key = fread(STDIN, 1);
        }
    }
    return $out;
}

// }}}
// {{{ function confirm_readline()
/**
 *
 * @return int 1 on y or 0 on anything else.
 */

function confirm_readline($line = null, $setSilence = null){
    static $silence = null;
    if (isset($setSilence)){
        $silence = 1;
    }
    if ($silence == 1){
        return 1;
    }
    $str = $line;
    $str.= " Sure you want to continue? [Y/n]";
    $res = cos_readline($str);
    if (strtolower($res) == 'y'){
        return 1;
    } else {
        return 0;
    }
}

// }}}
// {{{ function cos_cli_abort
/**
 * command for aborting a script and printing info about abort
 * @param   string  string to be printed on abort
 * @return  int     16 (positive int)
 */
function cos_cli_abort($str = null){
    if (isset($str)){
        $str = $str . "\nAborting!";
    } else {
        $str = "Aborting!";
    }
    cos_cli_print($str);
    exit(16);
}
// }}}
// {{{ get_db_info()
/*
 *
 * @return <array> $database containing array('name' => database, 'host' => 'host')
 */
function get_db_info(){
    $url = parse_url(register::$vars['coscms_main']['url']);
    $ary = explode (';', $url['path']);
    $db = explode ("=", $ary[0]);
    $database['name'] = $db[1];
    $host = explode ("=", $ary[1]);
    $database['host'] = $host[1];
    return $database;
}

// }}}
// {{{ cos_exec($command)
/**
 * function for executing commands with php built-in command exec
 * @param <string> $command to execute
 * @return <int>   $ret the value returned by the shell script being
 *                 executed through exec()
 */
function cos_exec($command, $options = array()){
    $output = array();
    exec($command.' 2>&1', $output, $ret);
    if ($ret == 0){
        if (!isset($options['silence'])){
            echo "Command [ok]    " . wordwrap($command) . "\n";
        }
    } else {
        if (!isset($options['silence'])){
            echo "Command [error] ";
            $end_output = '';
            foreach($output as $key => $val){
                $end_output.= $val;
            }
            $end_output = wordwrap($end_output) . "\n";
            print $end_output;
        }
    }
    return $ret;
}
// }}}
// {{{ cos_exec($command)
/**
 * function for executing commands with php built-in command exec
 * @param <string> $command to execute
 * @return <int>   $ret the value returned by the shell script being
 *                 executed through exec()
 */
function cos_system($command){
    $output = array();
    system($command.' 2>&1', $ret);
    if ($ret == 0){
        echo "Command [ok]    " . wordwrap($command) . "\n";
    } else {
        echo "Command [error] ";
        $end_output = '';
        foreach($output as $key => $val){
            $end_output.= $val . "\n";
        }
        $end_output = wordwrap($end_output);
        print $end_output;
    }
    return $ret;
}

// }}}
// {{{ cos_cli_print($mes)

/**
 * simple function for printing a message
 * @param <string> $mes
 */
function cos_cli_print($mes){
    print $mes . "\n";
}

// }}}

function cos_is_cli (){
    if (isset($_SERVER['SERVER_NAME'])){
        return false;
    }
    return true;
}