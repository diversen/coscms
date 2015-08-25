<?php

use diversen\html;
use diversen\http;

session_start();

include "../vendor/autoload.php";

//http::prg();
$f = new html();

$f->init(array(), 'send', true);



$f->formStart();
$f->text('test');
$f->submit('send', 'ok');
$f->formEnd();
echo $f->getStr();

