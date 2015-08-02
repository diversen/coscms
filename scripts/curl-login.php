<?php

include_once "vendor/autoload.php";
use diversen\mycurl;
use diversen\conf;

conf::setMainIni('base_path', realpath('.'));

$c = new mycurl('http://coscms/account/login/index');
$fields = array (
    'email' => 'test', 
    'password' => 'test',
    'submit_account_login' => 'Send');

$c->setPost($fields);
$c->createCurl();

echo $c->getWebPage();
