<?php
/**
 * Community CMS
 *
 *
 * @copyright Copyright (C) 2007-2009 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.main
 */

// Security Check
if (@SECURITY != 1) {
	die ('You cannot access this page directly.');
}

/**
 * Initializes many required variables
 * 
 * @global resource $db
 * @global array $site_info
 */
function initialize() {
	// Report all PHP errors
	error_reporting(E_ALL);
	header('Content-type: text/html; charset=UTF-8');

	session_start();

	global $db;
	global $site_info;
	global $acl;
	$acl = new acl;
	$db->sql_connect();
	// Load global site information.
	$site_info_query = 'SELECT * FROM ' . CONFIG_TABLE;
	$site_info_handle = $db->sql_query($site_info_query);
	if ($db->error[$site_info_handle]) {
		die('Failed to get site information.');
	}
	$site_info = $db->sql_fetch_assoc($site_info_handle);
}
function clean_up() {
	global $db;
	$db->sql_close();
}

/**
 * Truncate string
 * @param string $text Text to truncate
 * @param int $numb Maximum number of characters to allow
 * @return string Truncated string
 */
function truncate($text,$numb) {
	$text = html_entity_decode($text, ENT_QUOTES);
	if (strlen($text) > $numb) {
		$text = substr($text, 0, $numb);
		$text = substr($text,0,strrpos($text," "));
		//This strips the full stop:
		if ((substr($text, -1)) == ".") {
			$text = substr($text,0,(strrpos($text,".")));
		}
		$etc = "...";
		$text .= $etc;
	}
	$text = htmlentities($text, ENT_QUOTES);
	return $text;
}

/**
 * array2csv - Convert an array to a list of comma separated values
 * @param array $array Array of values that will appear in the result string
 * @return string Comma separated list of values
 */
function array2csv($array) {
	if (count($array) == 0) {
		return '';
	}
	$string = NULL;
	for ($i = 0; $i < count($array); $i++) {
		if (strlen($array[$i] > 0)) {
			$string .= $array[$i];
		}
		if ($i != count($array) - 1) {
			$string .= ',';
		}
	}
	return $string;
}

/**
 * csv2array - Convert a comma separated list of values to an array
 * @param string $string Comma separated list of values to insert into result array
 * @return array Array of values
 */
function csv2array($string) {
	if (strlen($string) == 0) {
		return array();
	}
	$array = array();
	$temp_array = explode(',',$string);
	for ($i = 0; $i < count($temp_array); $i++) {
		if (strlen($temp_array) != 0) {
			$array[] = $temp_array[$i];
		}
	}
	return $array;
}

function validate_int($value) {
    // FIXME: Stub
    return $value;
}

function validate_string($value) {
    // FIXME: Stub
    return $value;
}

function validate_array($value) {
    // FIXME: Stub
    return $value;
}
?>
