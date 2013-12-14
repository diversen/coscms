<?php


include_once "config.php";
use diversen\githubapi as githubApi;

session_start();

// we have a access token and we can now call the api: 
$api = new githubApi();

// simple call - get current users credentials
$command = "/user";

$res = $api->apiCall($command);
if (!$res) {
    print_r( $api->errors); die;
}

print_r($res);

// Or: more complex: first param is the command
// The next is the REQUEST Method
// 3. is an array with $post if we .eg. PATCH, or POST
// $res = $api->apiCall('/gists/4381068', 'PATCH', $content);
/*
 * $content = array (
    'description' => 'mmmmmmmm....',
    'public' => 'true',
    'files' => array (
        'file7.txt' => array (
            'content' => 'New content from api'
         ),
    ),
);
 */
// PATCH a gist - you will need to set the correct scope, e.g. 'user,gist'
// $res = $api->apiCall('/gists/4381068', 'PATCH', $content);
// $http_ret_code 
// return code
//echo $api->returnCode;
