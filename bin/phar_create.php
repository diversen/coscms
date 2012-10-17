<?php

// go to ../coscms and run as phar-create.php
$phar = new Phar('coscms.phar', 0, 'coscms.phar');
$phar->interceptFileFuncs();
$phar->buildFromDirectory(dirname(__FILE__) . '/coscms');
$phar->setStub($phar->createDefaultStub('./phar_stub.php'));
