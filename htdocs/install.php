<?php

include_once "webcommon.php";

// check to see if an install have been made.
// we check if there are rows in 'modules'
try {
    $num_rows = $db->getNumRows('modules');
} catch (PDOException $e) {   
    $num_rows = 0;
}

if ($num_rows == 0){
    echo "No tables or data in database. OK<br>";
    // read default sql and execute it.
    $sql = $db->readSql();
    $res = $db->rawQuery($sql);

    // if positive we install base modules.
    if ($res){
        install_from_profile(array ('profile' => 'default'));
    }
    echo "Base system installed.<br />";
} else {
    echo "System is installed! <br>";
}

$users = $db->getNumRows('account');
if ($users == 0) {
    web_install_add_user();
} else {
    echo "User exists. Install OK<br />\n";
}
