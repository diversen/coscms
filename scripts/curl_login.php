<?php


define('_COS_PATH', realpath('.'));
include_once "coslib/setup.php";
setup::common();
use diversen\mycurl;

//config::loadMainCli();

$c = new mycurl('http://default/account/login/index');

$fields = array (
    'email' => 'default', 
    'password' => 'default',
    'submit_account_login' => 'Send');

$c->setPost($fields);
$c->createCurl();

echo $c->getWebPage();
