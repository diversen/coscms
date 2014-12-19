<?php

use diversen\cli;
/**
 * class shell is a wrapper function around PEAR::commandLine
 *
 * @package     shell
 */

// need to seup include path and define commons
include_once "coslib/setup.php";
setup::common();

class mainCli extends cli{}
