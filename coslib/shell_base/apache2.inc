<?php

/**
 * File containing documentation functions for shell mode
 *
 * @package     shell
 */
// {{{ function create_logs ()
/**
 * create apache log files
 */
function create_logs(){
    touch(_COS_PATH . '/logs/access.log');
    touch(_COS_PATH . '/logs/error.log');
}
// }}} 
// {{{ function create_a2_conf()
/**
 * create apache2 configuration string
 * @param   string  name of host to enable
 * @return  string  a apache2 configuration string.
 */
function create_a2_conf($SERVER_NAME){
    $current_dir = getcwd();
    $DOCUMENT_ROOT = $current_dir . '/htdocs';
    $APACHE_LOG_ROOT = $current_dir . '/logs';
    
    if (isset(config::$vars['a2_use_ssl'])) {
        include "scripts/apache2/apache2-ssl.inc";

        
    } else {
        include "scripts/apache2/apache2.inc";
    }
    
    return $apache_str;
}
// }}}

// {{{ a2_enable_site($options)
/**
 * function for enabling a apche2 site
 * the script does the following:
 *
 * - create access.log and error.log in ./logs
 * - create virtual configuration and put it in sites-available
 * - enable new site
 * - create new /etc/hosts file
 *
 * @param   array   options only options is $options[sitename] 
 */
function a2_enable_site($options){
    $hostname = trim($options['hostname']);
    create_logs();

    // create apache2 conf and enable site
    $apache2_conf = create_a2_conf($hostname);
    $tmp_file = _COS_PATH . "/tmp/$hostname";
    file_put_contents($tmp_file, $apache2_conf);
    $apache2_conf_file = "/etc/apache2/sites-available/$hostname";
    cos_exec("sudo cp -f tmp/$hostname $apache2_conf_file");
    cos_exec("sudo a2ensite $hostname");

    // create new hosts file and reload server
    $hosts_file_str = file_get_contents("/etc/hosts");
    if (strstr($hosts_file_str, $hostname)){
        return 1;
    }
    $new_hosts_file_str = "127.0.0.1\t$hostname\n" . $hosts_file_str;
    file_put_contents("tmp/hosts", $new_hosts_file_str);
    cos_exec("sudo cp -f tmp/hosts /etc/hosts");
    cos_exec("sudo /etc/init.d/apache2 restart");
    cos_exec("rm tmp/hosts tmp/$hostname"); 
}
// }}}
// {{{ a2_disable_site()
/**
 * function for enabling a apche2 site
 * the script does the following:
 *
 * - create access.log and error.log in ./logs
 * - create virtual configuration and put it in sites-available
 * - enable new site
 * - create new /etc/hosts file
 *
 * @param   array   options only options is $options[sitename] 
 */
function a2_disable_site($options){
    $hostname = trim($options['hostname']);

    $apache2_conf_file = "/etc/apache2/sites-available/$hostname";
    cos_exec("sudo a2dissite $hostname");
    cos_exec("sudo rm -f $apache2_conf_file");

    // create new hosts file and reload server

    $host_file_str = '';
    $hosts_file_str = file("/etc/hosts");

    $str="";
    foreach ($hosts_file_str as $key => $val){
        if (strstr($val, $hostname)) {
            continue; 
        } else { 
            $host_file_str.=$val;
        }
    }
    file_put_contents("tmp/hosts", $host_file_str);
    cos_exec("sudo cp -f tmp/hosts /etc/hosts");
    cos_exec("sudo /etc/init.d/apache2 restart");
    cos_exec("rm tmp/hosts"); 

}
// }}}
function a2_use_ssl ($options) {
    config::$vars['a2_use_ssl'] = true;
}

mainCli::setCommand('apache2', array(
    'description' => 'Command for automatic configuration and setup of apache2 as virtual server',
));

mainCli::setOption('a2_use_ssl', array(
    'long_name'   => '--ssl',
    'description' => 'Set this flag and enable SSL mode',
    'action'      => 'StoreTrue'
));

mainCli::setOption('a2_enable_site', array(
    'long_name'   => '--enable',
    'description' => 'Will enable current directory as an apache2 virtual host. Will also add new sitename to your /et/hosts file',
    'action'      => 'StoreTrue'
));

mainCli::setOption('a2_disable_site', array(
    'long_name'   => '--disable',
    'description' => 'Will disable current directory as an apache2 virtual host, and remove sitename from /etc/hosts files',
    'action'      => 'StoreTrue'
));



mainCli::setArgument(
    'hostname',
    array('description'=> 'Specify the apache hostname to be used for install or uninstall. yoursite will be http://yoursite',
        'optional' => false,
));
