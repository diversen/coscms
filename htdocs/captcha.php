<?php

include_once "definePath.php";
include_once "captcha.php";
include_once "captchaImage.php";
//include_once "coslib/session.php";
session::initSession();
//session_start();

// -------------------------------------------------------------------
// captcha.php
// This file gets the request and initialize the CAPTCHA class
// Copyright (c) 2005 Gonçalo "gesf" Fontoura.
// -------------------------------------------------------------------
//session_start();
//define("INSITE", true);

//require_once dirname(__FILE__) . '/classes/captcha.class.php';

// -------------------------------------------------------------------------------------------
// To make sure the image is not cached
// -------------------------------------------------------------------------------------------

header("Expires: Mon, 23 Jul 1993 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
// HTTP/1.1
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
// HTTP/1.0
header("Pragma: no-cache");

// Usage Example:
// -------------------------------------------------------------------------------------------
// captcha.php?s=123456		/ Output: 123456
// captcha.php?c=1&s=foobar 	/ Output: FOOBAR
// captcha.php?c=2&s=foobar 	/ Output: A 6 digits random number (letters are discarded)
// captcha.php?c=6		/ Output: A 6 digits random string (Lower/upper letters + numbers)
// -------------------------------------------------------------------------------------------
// Verification code example:
// -------------------------------------------------------------------------------------------
//	if(isset($_POST["user_captcha"]) && sha1($_POST["user_captcha"]) == $_SESSION["CAPTCHA_HASH"]) {
//		print "ok";
//	} else {
//		print "error";	
//		exit;
//	}
// -------------------------------------------------------------------------------------------

//$userstring = isset($_GET['s']) ? trim($_GET['s']) : '';
//$captchatype = isset($_GET['c']) ? trim($_GET['c']) : 5;

print_r($_SESSION); die;
echo $str = $_SESSION['cstr']; die;
$captcha = new captchaImage($str);
die;
