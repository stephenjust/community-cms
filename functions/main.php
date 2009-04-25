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
 * @global array $CONFIG
 * @global resource $db
 * @global array $site_info
 */
function initialize() {
	// Report all PHP errors
	error_reporting(E_ALL);
	header('Content-type: text/html; charset=UTF-8');

    session_start();

    global $CONFIG;
    global $db;
    global $site_info;
	// Try to establish a connection to the MySQL server using the MySQLi classes.
    @ $db = new mysqli($CONFIG['db_host'],$CONFIG['db_user'],$CONFIG['db_pass'],$CONFIG['db_name']);
    if(mysqli_connect_errno()) {
        err_page(1001); // Database connect error.
    }
	// Load global site information.
	$site_info_query = 'SELECT * FROM '.$CONFIG['db_prefix'].'config';
	$site_info_handle = $db->query($site_info_query);
    if(!$site_info_handle) {
        err_page(1001);
    }
	$site_info = $site_info_handle->fetch_assoc();
}
function clean_up() {
    global $db;
    $db->close();
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
 * validate_int - Validate values of integers
 * @param int $value Integer to be validated
 * @return int -1 if false
 */
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
