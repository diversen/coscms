<?php

include_once "config.php";

use diversen\githubapi as githubApi;

session_start();

/*
 * we are back from github and the user has accepted our
 * request. We now request a access token, 
 * The github api will just set this as a SESSION var found in
 * $_SESSION['access_token'] when returned from github
 * 
 * If this is a success we redirect to app_call.php
 * where we then can make api calls from the $_SESSION['access_token']
 * 
 * See: api_call.php
 */
$post = array (
    'redirect_uri' => GITHUB_CALLBACK_URL,
    'client_id' => GITHUB_ID,
    'client_secret' => GITHUB_SECRET,
);

$api = new githubApi();
$res = $api->setAccessToken($post);

if ($res) {
    header("Location: api_call.php");
} else {
    echo "Could not get access token. Errors: <br />";
    print_r($api->errors);
}
  