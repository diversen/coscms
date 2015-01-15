<?php

//Phar::webPhar();
Phar::interceptFileFuncs();
//chdir('htdocs');
//echo "hello world";
include_once "htdocs/router.php";
__HALT_COMPILER();
