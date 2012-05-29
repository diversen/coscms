<?php

/**
 * @package shell
 */

// {{{ create_cron_line($site)
/**
 * function for showing a readline form where we can collect 
 * information about how crons should be done
 * 
 * @param string $site
 * @return array $cron with info about the cron
 */
function create_cron_line(){


    $readline_str = "Shall cron run once every [h]our or once every [d]ay or once every month[m]: [h]/[d]/[m]? ";
    $cron = array();
    $hdm = cos_readline($readline_str);
    if ($hdm != 'h' AND $hdm != 'd' AND $hdm != 'm'){
        return 0;
    }
  
    $readline_str = "Enter minute when your cron shall run. Must be between 0 and 59: ";
    $cron = array();
    $minute = (int)cos_readline($readline_str);
    if ($minute >= 0 && $minute <= 59){
        $cron['minute'] = $minute;
    } else {
        return 0;
    }

    if ($hdm == 'h') return $cron;
  
    $readline_str = "Enter hour when your cron shall run. Must be between 0 and 23: ";
    $hour = (int)cos_readline($readline_str);
    if ($hour >= 0 && $hour <= 23){
        $cron['hour'] = $hour;
    } else {
        return 0;
    }
  
    if ($hdm == 'd') return $cron;
  
    $readline_str = "Enter day in month when your cron shall run. Must be between 1 and 31: ";
    $day = (int)cos_readline($readline_str);
    if ($day >= 1 && $day <= 31){
        $cron['day'] = $day;
    } else {
        return 0;
    }
  
    if ($hdm == 'm') return $cron;
    return 0;
}

// }}} 
// {{{ disable_cron_line($site)

/**
 * function for deleting entries from the crontab file
 *
 * @param string $site
 * @return int 1 
 */
function disable_cron_line(){
    $cron_line = _COS_PATH;
    $output = $cron_str = '';

    // get contents of crontab. We use exec because we need the output
    // then execute crontab -l command to get old crontabs
    exec("crontab -l", $output, $res);
    foreach ($output as $line) {
        if (!preg_match("[$cron_line]", $line)){
            $cron_str.=$line . "\n";
        }
    }

    $cron_str = rtrim ($cron_str);
    $command = 'echo "' . $cron_str . '" | crontab -';
    cos_exec($command);
    return 1;
}

// }}}
// {{{ enable_cron_line($site)
/**
 * function for enabling the site in the crontab
 *
 * @param string $site
 * @return int 0 on failure 1 on success
 */
function enable_cron_line(){
    
    $readline_str = "Do you want to enable cron job for the site [Y/n]? ";
    $y_or_n = strtoupper(cos_readline($readline_str));
    if ($y_or_n == 'Y' || empty($y_or_n)){
        // continue
    } else {
        return 0;
    }
  
    $cron = create_cron_line();
    if (!$cron){
        cos_cli_print("Can not enable cron");
        return 0;
    } else {
        // minute always set in this scenario
        $cron_line = $cron['minute'] . " ";
        // but not the day
        if (isset($cron['hour'])){
            $cron_line.= $cron['hour'] . " ";
        } else {
            $cron_line.= "* ";
        }
        // and not the month
        if (isset($cron['day'])){
            $cron_line.= $cron['day'] . " ";
        } else {
            $cron_line.= "* ";
        }
        // add the remains
        $cron_line.="* *  ";
    }

    $cron_line.= " cd " . _COS_PATH . " && ./coscli.sh cron --run-cron\n";
    // get contents of crontab. We use exec because we need the output
    // do the crontab -l command to get old crontabs
    exec("crontab -l", $output, $res);
    foreach ($output as $key => $val){
        // ensure only cron line per site
        $search = _COS_PATH;
        if (!preg_match("[$search]", $val)){
            $cron_line.= $val . "\n";
        }
    }

    $command = 'echo "' . rtrim($cron_line) . '" | crontab -';
    cos_exec($command);

    echo _COS_PATH;

    return 1;
}

// }}}
// {{{ function run_cron()
function run_cron (){
    
    $mi = new moduleLoader();
    $mods = $mi->getAllModules();
    $module_path = _COS_PATH . "/modules";

    foreach($mods as $val){
         $cron_file = $module_path . "/$val[module_name]/cron.inc";
         if (file_exists($cron_file)){
            include_once $cron_file;
            $cron_function = $val['module_name'] . "_cron";
            if (function_exists($cron_function)){
                $cron_function();
            }
         }
    }
}


// }}}
mainCli::setCommand('cron', array(
    'description' => 'Will enable cron job',
));

mainCli::setOption('enable_cron_line', array(
    'long_name'   => '--enable-cron',
    'description' => 'Enables new cron line for site. Will disable old cron line if any.',
    'action'      => 'StoreTrue'
));

mainCli::setOption('disable_cron_line', array(
    'long_name'   => '--disable-cron',
    'description' => 'Disable cron for site ',
    'action'      => 'StoreTrue'
));

mainCli::setOption('run_cron', array(
    'long_name'   => '--run-cron',
    'description' => 'Runs cron for site ',
    'action'      => 'StoreTrue'
));
