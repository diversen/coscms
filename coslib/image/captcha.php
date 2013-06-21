<?php

// folowing class is modified from: 

// -------------------------------------------------------------------
// captcha.class - version 1.0
// Defines the Captcha class - generate CAPTCHAs
// Copyright (c) 2005 Gonçalo "gesf" Fontoura.
// Licensed under the GNU L-GPL
// http://gesf.org
// -------------------------------------------------------------------

//if(!defined("INSITE")) { die("No direct access allowed!"); }

class image_captcha {

// -------------------------------------------------------------------
// Settings
// -------------------------------------------------------------------

var $_capLength = 6;				# Default captcha string length
// private $_capLength = 6;
var $_capString;				# To store the capcha string/text
// private $_capString;
var $_capImageType = 'png';			# To store the image type
// private $_capImageType = 'png';
//var $_capFont = '/fonts/captcha.ttf';		# To store the captcha font type
// private $_capFont = '/fonts/captcha.ttf';
var $_capCharWidth = 16;			# Default character width
// private $_capCharWidth = 16;
var $_capTextColor = '000000';			# Default text color
// private $_capTextColor = '000000';
var $_capBgColor = 'FFFFFF';			# Default background color
// private $_capBgColor = 'FFFFFF';
var $_capCase = 5;				# To store the captcha string type
// private $_capCase = 5;
var $_capimage_height = 25;			# Stores the image height
// private $_capimage_height = 25;
var $_capimage_padding = 10;			# The captcha text padding
// private $_capimage_padding = 10;

// -------------------------------------------------------------------
// Constructor.
// Call needed methods and gerenate CAPTCHA right away ...
// -------------------------------------------------------------------

function Image_Captcha($str) {
// public function __constructor($letter = '', $case = 5) {	
	
	/*
        $this->_capCase = $case;

	if (empty($letter)) {
		$this->StringGen();
	} else {
		$this->_capLength = strlen($letter);
		$this->_capString = substr($letter, 0, $this->_capLength);
	}
	
	@session_start();
	$_SESSION["CAPTCHA_HASH"] = sha1($this->_capString);
	*/
        $font = config::getModuleIni ('image_captcha_font'); //'fonts/captcha.ttf';'
        $this->_capFont = _COS_HTDOCS . '/' . $font;
	$this->SendHeader();
        $this->setStr($str);
	$this->MakeCaptcha();
}

// -------------------------------------------------------------------
// Generate CAPTCHA string
// String Type:
//
// 0 : Lowercase Letters (a-z).
// 1 : Uppercase Letters (A-Z).
// 2 : Numbers Only (0-9).
// 3 : Letters Only (upper and lower case).
// 4 : Lowercase Letters and Numbers.
// 5 : Uppercase Letters and Numbers.
// 6 : All together
// -------------------------------------------------------------------

function setStr($str) {
// public function StringGen() {
	/*
	$uppercase  = range('A', 'Z');
	$lowercase  = range('a', 'z');
	$numeric    = range(0, 9);

	$char_pool  = array();

	switch($this->_capCase) {
		case 0: $char_pool = $lowercase; break;
		case 1: $char_pool = $uppercase; break;
		case 2: $char_pool = $numeric; break;
		case 3: $char_pool = array_merge($uppercase, $lowercase); break;
		case 4: $char_pool = array_merge($lowercase, $numeric); break;
		case 5: $char_pool = array_merge($uppercase, $numeric); break;
		case 6: $char_pool = array_merge($uppercase, $lowercase, $numeric); break;
		default:$char_pool = array_merge($uppercase, $numeric);
	}

	$pool_length = count($char_pool) - 1;
*/
	//for($i = 0; $i < $this->_capLength; $i++) {
		$this->_capString .= "$str";//$char_pool[mt_rand(0, $pool_length)];
                $this->_capLength = strlen($this->_capString);
    //}
}

// -------------------------------------------------------------------
// Sends the proper Content-type
// -------------------------------------------------------------------

function SendHeader() {
// public function SendHeader() {
		
	switch($this->_capImageType) {
		case 'jpeg': header('Content-type: image/jpeg'); break;
		case 'png': header('Content-type: image/png'); break;
		case 'gif': header('Content-type: image/gif'); break;
		default: header('Content-type: image/png'); break;
	}
}

// -------------------------------------------------------------------
// Generate the image based on all the settings
// -------------------------------------------------------------------

function MakeCaptcha() {
// public function MakeCaptcha() {	
	
	$imagelength = $this->_capLength * $this->_capCharWidth + $this->_capimage_padding;
	$image       = imagecreate($imagelength, $this->_capimage_height);
	$bgcolor = imagecolorallocate(
		$image,
		hexdec(substr($this->_capBgColor,0,2)),
		hexdec(substr($this->_capBgColor,2,2)),
		hexdec(substr($this->_capBgColor,4,2))
	);
	
	$stringcolor = imagecolorallocate(
		$image,
		hexdec(substr($this->_capTextColor,0,2)),
		hexdec(substr($this->_capTextColor,2,2)),
		hexdec(substr($this->_capTextColor,4,2))
	);
	$linecolor   = imagecolorallocate($image, 0, 0, 0);
	imagettftext($image, $this->_capCharWidth, 0, 0, 18, $stringcolor, $this->_capFont, $this->_capString);

	switch($this->_capImageType) {
		case 'jpeg': imagejpeg($image); break;
		case 'png': imagepng($image); break;
		case 'gif': imagegif($image); break;
		default: imagepng($image); break;
	}

	imagedestroy($image);
}

// -------------------------------------------------------------------
// Some additional methods you might want to use
// -------------------------------------------------------------------

// -------------------------------------------------------------------
// Returns the CAPTCHA string as it is
// -------------------------------------------------------------------

function GetCaptchaString() { 
// public function GetCaptchaString() {	
	return $this->_capString; 
}

// -------------------------------------------------------------------
// Returns the CAPTCHA hash
// -------------------------------------------------------------------

function GetCaptchaHash() { 
// public function GetCaptchaHash() {
	return $_SESSION["CAPTCHA_HASH"]; 
}

} // End Captcha class

