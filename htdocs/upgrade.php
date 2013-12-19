<?php

include_once "webcommon.php";

$password = config::getMainIni('upgrade_password');
if (!$password || !isset($_GET['password'])) {
    //$server = config::getSchemeWithServerName();
    $url = "http://" . $_SERVER['SERVER_NAME'] . "/upgrade.php?password=password";
    die("Set ini_setting 'upgrade_password'='password' password in config/config.ini, and visit $url");
} else {
    if ($password == $_GET['password']) {
        upgrade_from_profile_web(array ('profile' => 'default'));

        // reload language
        $reload = new moduleinstaller();
        $reload->reloadLanguages();
    }
}
