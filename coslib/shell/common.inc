<?php

/**
 * Some helper functions to use with shell.php
 *
 * @package     shell
 */

if (!config::isCli()){
    $new_line = "<br />";
} else {
    $new_line = PHP_EOL;
}

/**
 * define new line if not cli
 */

define('NEW_LINE', $new_line);
define('COS_CLI_STATUS_LENGTH', 12);
/**
 * readline
 * found on php.net 
 *
 * @param string ouput to print to screen
 * @return string the input which readline reads
 */
function cos_readline($str){
    echo $str;
    $out = "";
    $key = "";
    $key = fgetc(STDIN);      // read from standard input (keyboard)
    while ($key!="\n") {      // if the newline character has not yet arrived read another
        $out.= $key;
        $key = fread(STDIN, 1);
    }
    return $out;
}

/**
 * a function for getting a confirm from command prompt
 * @param string $line a line to inform user what is going to happen
 * @param mixed $silence. If set we answer 'y' to all confirm readlines
 * @return int 1 on 'y' or 'Y' and 0 on anything else.
 */

function cos_confirm_readline($line = null, $set_silence = null){
    static $silence = null;
    if (isset($set_silence)){
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

/**
 * command for aborting a script and printing info about abort
 * @param   string  $str string to be printed on abort
 * @return  int     $res  16
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

/**
 * function for executing commands with php command exec
 * @param   string  $command to execute
 * @param   array   $options defaults to:
 *                  array ('silence' => false);
 * @return  mixed   $ret the value returned by the shell script being
 *                  executed through exec()
 */
function cos_exec($command, $options = array(), $echo_output = 1){
    $output = array();
    exec($command.' 2>&1', $output, $ret);
    if ($ret == 0){
        if (!isset($options['silence'])){
            
            
            echo color_output (cos_get_color_status('[OK]'), 'g');
            echo $command . "\n";
            if ($echo_output) {
                echo cos_parse_shell_output($output);
            }
        }
    } else {
        if (!isset($options['silence'])){
            echo color_output(cos_get_color_status('[ERROR]'), 'r');
            echo $command . "\n";
            if ($echo_output) {
                echo cos_parse_shell_output($output);
            }
        }
    }
    return $ret;
}

/**
 * calculate and gets correct length of a status message,
 * e.g. [OK] and [NOTICE]
 * @param string $status [OK]
 * @return string $status
 */
function cos_get_color_status ($status) {
    $len = strlen($status);
    $add_spaces = COS_CLI_STATUS_LENGTH - $len;
    $status.=str_repeat(' ', $add_spaces);
    return $status;
}


/**
 * method for coloring output to command line
 * @param string $output
 * @param char $color_code (e.g. 'g', 'y', 'r')
 * @return string $colorered output
 */
function color_output ($output, $color_code = 'g') {
    static $color = null;
    if (!config::isCli()) {
        return $output;
    }
    
    if (!isset(config::$vars['coscms_main']['color_shell_disable'])) {
        if (!$color) {
            $color = new Console_Color();
        }
        $ret = $color->convert("%$color_code$output%n");    
    } 
    return $ret;
}

/**
 * transform an array of output from exec into a single string
 * @param array $output
 * @return string $str
 */
function cos_parse_shell_output ($output){
    if (!is_array($output)) return '';
    $end_output = '';
    foreach($output as $val){
        $end_output.= $val ."\n";
    }
    //$output = wordwrap($end_output);
    return $end_output;    
}

/**
 * function for executing commands with php built-in command exec
 * @param string $command to execute
 * @return int   $ret the value returned by the shell script being
 *                 executed through exec()
 */
function cos_system($command){
    $output = array();
    system($command.' 2>&1', $ret);
    if ($ret == 0){
        echo color_output ("[OK]    ", 'g');
        echo $command . "\n";
    } else {
        echo color_output("[ERROR] ", 'r');
        echo $command . "\n";
        //$end_output = '';
        //foreach($output as $key => $val){
        //    $end_output.= $val . "\n";
        //}
        //$end_output = wordwrap($end_output);
        //echo $end_output;
    }
    return $ret;
}

/**
 * simple function for printing a message
 * @param  string $mes the message to echo
 */
function cos_cli_print($mes, $color = null){
    if ($color) {
        echo color_output($mes . "\n", $color);
        return;
    }
    if (config::isCli()) {
        echo $mes . NEW_LINE;
    } else {
        echo $mes . "<br />\n";
    }
    return;
}

/**
 * echos a colored status message
 * @param string $mes (e.g. UPGRADE, NOTICE, ERROR)
 * @param char $color the color to print, e.g. 'y', 'r', 'g'
 * @param string $status the status message to be appended to e.g. 
 *               'module upgrade failed'
 * @return void
 */
function cos_cli_print_status ($mes, $color, $status) {
    if (config::isCli()) {
        echo color_output(cos_get_color_status("[$mes]"), $color);
        echo $status . "\n";
    } else {
        cos_cli_print($status);
    }
    return;
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

/**
 * checks if user is root or not
 * @return boolean true if is root else false
 */
function cos_is_root () {
    
    if (0 == posix_getuid()) {
        return true;
    } else {
        return false;
    }
}

/**
 * examine if user is root. If not we die and echos a message
 * @param string $str
 * @return int 0 on success else a positive int. 
 */
function cos_needs_root ($str = '') {
    
    $output = '';

    $output.= "Current command needs to be run as root. E.g. with sudo: ";
    if (!empty($str)) {
        $output.="\nsudo $str";
    }

    
    if (!cos_is_root()) {
        echo $output . PHP_EOL;
        mainCli::exitInt(128);
    }
    return 0;
}
