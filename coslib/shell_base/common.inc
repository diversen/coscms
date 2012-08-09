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
    echo $str;
    //if (function_exists('readline') ){
    //    $out = readline($str);
    //    return $out;
    //} else {
        // if not readline we use this
        // we could actually just use this .)
        // found at php.net
        //print $str;
        $out = "";
        $key = "";
        $key = fgetc(STDIN);        //read from standard input (keyboard)
        while ($key!="\n") {       //if the newline character has not yet arrived read another
            $out.= $key;
            $key = fread(STDIN, 1);
        }
    //}
    return $out;
}

// }}}
// {{{ function confirm_readline()
/**
 *
 * @return int 1 on y or 0 on anything else.
 */

function cos_confirm_readline($line = null, $setSilence = null){
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
    cos_cli_print(color_output($str, 'r'));
    exit(16);
}
// }}}



// {{{ cos_exec($command)
/**
 * function for executing commands with php built-in command exec
 * @param   string  $command to execute
 * @param   array   $options defaults to:
 *                  array ('silence' => false);
 * @return  mixed   $ret the value returned by the shell script being
 *                  executed through exec()
 */
function cos_exec($command, $options = array()){
    $output = array();
    exec($command.' 2>&1', $output, $ret);
    if ($ret == 0){
        if (!isset($options['silence'])){
            echo color_output ("Command [ok]    ", 'y');
            echo wordwrap($command) . "\n";
        }
    } else {
        if (!isset($options['silence'])){
            echo color_output("Command [error] ", 'r');
            $end_output = '';
            foreach($output as $key => $val){
                $end_output.= $val;
            }
            $end_output = wordwrap($end_output) . "\n";
            echo $end_output;
        }
    }
    return $ret;
}
// }}}

function color_output ($output, $color_code = 'g') {
    if (isset(config::$vars['coscms_main']['color_shell'])) {
        return Console_Color::convert("%$color_code$output%n");    
    } 
    return $output;
}

function cos_parse_shell_output ($output){
    if (!is_array($output)) return;
    $end_output = '';
    foreach($output as $key => $val){
        $end_output.= $val ."\n";
    }
    return $end_output;    
}
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
        echo color_output("Command [ok]    ", 'g');
        echo wordwrap($command) . "\n";
    } else {
        echo color_output("Command [error] ", 'r');
        $end_output = '';
        foreach($output as $key => $val){
            $end_output.= $val . "\n";
        }
        $end_output = wordwrap($end_output);
        echo $end_output;
    }
    return $ret;
}

/**
 * simple function for printing a message
 * @param  string $mes the message to echo
 */
function cos_cli_print($mes){
    echo $mes . "\n";
}


/**
 * checks if we are on command line 
 * @return boolean $res true if yes else false
 */
function cos_is_cli (){
    if (isset($_SERVER['SERVER_NAME'])){
        return false;
    }
    return true;
}
