<?php

define('_COS_PATH', realpath('.'));
include_once "coslib/coslibSetup.php";

$apiKey = 'apikey';

// Replace this value with your account key
$accountKey = $apiKey;
$ServiceRootURL =  'https://api.datamarket.azure.com/Bing/SearchWeb/';  
$WebSearchURL = $ServiceRootURL . 'Web?$format=json&Query=';

//$q = '下载频道>资源分类>行业>互联网 ';
$q = "After his death, Tolkien's son published a series of works based on his father's extensive notes and unpublished manuscripts, including The Silmarillion";
$request = $WebSearchURL . urlencode( '\'' . $q . '\'');

$process = curl_init($request);
curl_setopt($process, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
curl_setopt($process, CURLOPT_USERPWD,  $accountKey . ":" . $accountKey);
curl_setopt($process, CURLOPT_TIMEOUT, 30);
curl_setopt($process, CURLOPT_RETURNTRANSFER, TRUE);
$response = curl_exec($process);

//die;
$jsonobj = json_decode($response, true);
print_r($jsonobj);

die;



