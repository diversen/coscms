<?php

define('_COS_PATH', '.');
include_once "coslib/coslibSetup.php";

// Load a configuration file (found in config/config.ini)
//config::loadMainCli();
$sql_date = '1982-02-11';
$ary =  date::getAry($sql_date);
print_r($ary);
echo "Years old: " . date::yearsOld($sql_date) . "\n";

$start_date = '2009-09-08';
$end_date = '2009-09-14';

$in_range = date::inRange($start_date, $end_date, '2010-01-01');
var_dump($in_range);

echo $start_date . " is a " . date::timestampToLocaleDay($start_date) . "\n";
echo "between $start_date and $end_date there is " . date::getDatesTimeDiff($start_date, $end_date) . " days\n";
echo "The 12 month is named " . date::getMonthName(12) . "\n";
echo "Current year is " . date::getCurrentYear() . "\n";
echo "The dates between $start_date and $end_date are (array dump): ";

$ary = date::getDatesAry($start_date, $end_date);
print_r($ary);

echo "Now is " . date::getDateNow() . "\n";
$unix_now = time();
echo "Now in unix time is $unix_now\n";
echo "Now from unix stamp is " . date::getDateNow(array ('hms' => 1), $unix_now) . "\n";
echo "Same as above, but the old way " . date::getDateNow(array ('hms' => 1, 'timestamp' => $unix_now)) . "\n";
echo "add 3 days to $start_date and you get " . date::addDaysToTimestamp($start_date, 3) . "\n";
echo "subtract 345 days from $start_date and you get " . date::substractDaysFromTimestamp($start_date, 345) . "\n";

$unvalid = '2012-13-30';
echo "date $unvalid is not valid ";
var_dump(date::isValid($unvalid));
echo "However $start_date is valid ";
var_dump(date::isValid($start_date));
echo "now has the week number: " . date::getWeekNumber('now') . "\n";
echo "now has the day number: " . date::getDayNumber('now') . "\n";

$weeks = date::getWeekInRange($start_date, $end_date);
echo "number of weeks in $start_date to $end_date ";
print_r($weeks);
$week = date::getWeek($start_date);
echo "$start_date is in week with array of days \n";
print_r($week);
$ary = date::getAry($start_date);
echo "$start_date as array ";
print_r($ary);
echo "If I was born on $start_date I would be " . date::yearsOld($start_date) . " years old\n";