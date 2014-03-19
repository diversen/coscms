<?php

// only router.php if cli-server
if (php_sapi_name() == 'cli-server') {
    $info = parse_url($_SERVER['REQUEST_URI']);
    if (file_exists( "./$info[path]")) {
        return false;
    } else {
        include_once "index.php";
        return true;
    }
}
