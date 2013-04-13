<?php

session_start();

include_once "mycurl.php";
include_once "githubapi.php";
include_once "config.php";

// we have a access token and we can now call the api: 
$api = new githubapi();

// simple call

//$command = '/legacy/repos/search/coscms';
$command = "/users";

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
