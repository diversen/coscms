### coslibSetup

If you want to use the base lib it is quite easy to set it up

Example: 

<?php

// You will need a base path and then you will need to include the 
// coslibSetup.php file, which defines all auto loading and 
// everything else. 

define('_COS_PATH', '.');
include_once "coslib/coslibSetup.php";

// Load a configuration file (found in config/config.ini)
config::loadMainCli();

// Do something - but almost anytime you will need a db connection. 
$db = new db();
$db->connect();

$sql = "DESCRIBE account";
$row = $db->selectQuery($sql);

foreach ($row as $key => $val) {
    print_r($val);
}

?>