<?php

/**
 * @package shell
 *
 */

/**
 * Adds a super user directly. Password have to be the md5 of the real password. 
 * @param array $options
 * @return int
 */
function useradd_direct_add ($options = null){
//print_r($options);
    $values['email'] = $options['email'];
    $values['password'] = $options['password']; // MD5
    $values['username'] = $values['email'];
    $values['verified'] = 1;
    $values['admin'] = 1;
    $values['super'] = 1;
    //print_r($values);
    $res = useradd_db_insert($values);
    if ($res) { 
        return 0;
    } else {
        return 1;
    }
}

/**
 * Adds an admin user directly. Password have to be the md5 of the real password. 
 * @param array $options
 * @return int
 */
function useradd_direct_add_admin ($options = null){
//print_r($options);
    $values['email'] = $options['email'];
    $values['password'] = $options['password']; // MD5
    $values['username'] = $values['email'];
    $values['verified'] = 1;
    $values['admin'] = 1;
    $values['super'] = 0;
    //print_r($values);
    $res = useradd_db_insert($values);
    if ($res) { 
        return 0;
    } else {
        return 1;
    }
}



mainCli::setCommand('useradd_direct', array(
    'description' => 'Will create super user direct by setting email and password arguments',
));

mainCli::setOption('useradd_direct_add', array(
    'long_name'   => '--add',
    'description' => 'Add user direct.',
    'action'      => 'StoreTrue'
));

mainCli::setOption('useradd_direct_add_admin', array(
    'long_name'   => '--add-admin',
    'description' => 'Add user direct.',
    'action'      => 'StoreTrue'
));

mainCli::setArgument('email',
    array('description'=> 'email',
          'optional' => false));

mainCli::setArgument('password',
    array('description'=> 'password',
          'optional' => false));

