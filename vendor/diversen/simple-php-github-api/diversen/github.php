<?php

session_start();

include_once "config.php";
include_once "mycurl.php";
include_once "githubapi.php";

/**
 * this is the config used for creating a url to github.com
 * then we go to the github url and asks for the user to accept
 * the scope of our application. 
 * 
 * You can edit scope. 
 * 
 * We press the url and we move to the github site where
 * the user will be able to accept that this app uses some
 * priviligies. If the user accepts We return to callback.php 
 * 
 * See: callback.php
 */
$access_config = array (
    'redirect_uri' => GITHUB_CALLBACK_URL,
    'client_id' => GITHUB_ID,
    'state' =>  md5(uniqid()),
    'scope' => 'user'
);

$api = new githubapi();

$url = $api->getAccessUrl($access_config);
echo "<a href=\"$url\">Github Login</a>";
  