<?php

/**
 * @package shell
 *
 */

/**
 *
 * @param array $options
 * @return int
 */
function useradd_add ($options = null){
	//$type = cos_readline('Enter type of super user to add [1] Email User, [2] OpenID user: ');
	//if ($type == 1){
		//$values['username'] = cos_readline ("Enter username: ");
		$values['email'] = cos_readline("Enter Email of super user (you will use this as login): ");
		$values['password'] = cos_readline ("Enter password: ");
		$values['password'] = md5($values['password']);
	//} else if ($type == 2){
//		$values['username'] = cos_readline ("Enter full url of openid to add as super user. Needs to be absolutely correct: ");
//		$values['url'] = $values['username'];
//	} else {
//		useradd_add($options);
	//}

        $values['username'] = $values['email'];
	$values['verified'] = 1;
	$values['admin'] = 1;
	$values['super'] = 1;
	$res = useradd_db_insert($values);

	return $values;
}

/**
 * function for inserting user
 * @param   array   $values
 * @return  boolean
 */
function useradd_db_insert ($values){
	$db = new db();
	return $res = $db->insert('account', $values);
}

mainCli::setCommand('useradd', array(
    'description' => 'Will create a super user for the coscms',
));

mainCli::setOption('useradd_add', array(
    'long_name'   => '--add',
    'description' => 'Add User.',
    'action'      => 'StoreTrue'
));

