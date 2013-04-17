<?php

include_once "config.php";

use diversen\githubapi as githubApi;
session_start();

/**
 * this is the config used for creating a url to github.com
 * then we go to the github url and asks for the user to accept
 * the scope of our application. 
 * 
 * You can edit scope. 
 * 
 * This is set to empty, but you could e.g. set it to 'user'
 * where ask for permissions to write to repos. 
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
    /*'scope' => 'user' */
);

$api = new githubApi();

$url = $api->getAccessUrl($access_config);
echo "<a href=\"$url\">Github Login</a>";
  
