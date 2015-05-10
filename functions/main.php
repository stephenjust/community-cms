<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2007-2009 Stephen Just
 * @author    stephenjust@users.sourceforge.net
 * @package   CommunityCMS.main
 */

namespace CommunityCMS;
require_once ROOT.'controllers/LoginController.class.php';

// Security Check
if (@SECURITY != 1) {
    die ('You cannot access this page directly.');
}

/**
 * Initializes many required variables
 *
 * @global db $db
 * @global Debug $debug
 */
function initialize($mode = null) 
{
    // Report all PHP errors
    error_reporting(E_ALL);

    // Send headers specific to AJAX scripts
    if ($mode == 'ajax') {
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Date in the past
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); // always modified
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0"); // HTTP/1.1
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache"); // HTTP/1.0
    } else {
        header('Content-type: text/html; charset=UTF-8');
    }

    global $db;
    global $debug;

    include_once ROOT . 'includes/debug.php';
    $debug = Debug::get();

    // Must initialize DB class before ACL class
    $db->sql_connect();
    if (!$db->connect) {
        err_page(1001); // Database connection error
    }

    // Don't do this when installing - we have no DB version set yet
    if ($mode != 'install') {
        // Check for up-to-date database
        if (SysConfig::get()->getValue('db_version') != DATABASE_VERSION) {
            err_page(10); // Wrong DB Version
        }
    }

    session_name(SysConfig::get()->getValue('cookie_name'));
    session_start();

    new LoginController();

    return;
}
function clean_up() 
{
    global $db;
    @ $db->sql_close();
}

/**
 * Truncate string
 * @param string $text Text to truncate
 * @param int    $numb Maximum number of characters to allow
 * @return string Truncated string
 */
function truncate($text,$numb) 
{
    $text = html_entity_decode($text, ENT_QUOTES);
    if (strlen($text) > $numb) {
        $text = substr($text, 0, $numb);
        $text = substr($text, 0, strrpos($text, " "));
        //This strips the full stop:
        if ((substr($text, -1)) == ".") {
            $text = substr($text, 0, (strrpos($text, ".")));
        }
        $etc = "...";
        $text .= $etc;
    }
    $text = htmlentities($text, ENT_QUOTES);
    return $text;
}

/**
 * array2csv - Convert an array to a list of comma separated values
 * @global Debug $debug Debug object
 * @param array $array Array of values that will appear in the result string
 * @return string Comma separated list of values
 */
function array2csv($array) 
{
    global $debug;
    if (count($array) == 0) {
        if (is_object($debug)) {
            $debug->addMessage('Array provided is empty', true); 
        }
        return '';
    }
    if (is_object($debug)) {
        $debug->addMessage('Array provided has '.count($array).' entries', false); 
    }
    $string = null;
    $array_count = count($array);
    for ($i = 0; $i < $array_count; $i++) {
        // The array may have empty indices. Increase the index number, and
        // increase the limit of the for loop
        while (!isset($array[$i])) {
            $i++;
            $array_count++;
        }
        if (strlen($array[$i]) > 0) {
            $string .= $array[$i];
        } else {
            if (is_object($debug)) {
                $debug->addMessage('Empty array element found', false); 
            }
        }
        if ($i != $array_count - 1) {
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
function csv2array($string) 
{
    if (strlen($string) == 0) {
        return array();
    }
    $array = array();
    $temp_array = explode(',', $string);
    for ($i = 0; $i < count($temp_array); $i++) {
        if (strlen($temp_array[$i]) != 0) {
            $array[] = $temp_array[$i];
        }
    }
    return $array;
}

/**
 * str_replace_count - Replace substrings a specified number of times
 * @param string $search
 * @param string $replace
 * @param string $subject
 * @param int    $times
 * @return string
 */
function str_replace_count($search,$replace,$subject,$times) 
{
    $subject_original = $subject;
    $len = strlen($search);
    $pos = 0;
    for ($i = 1; $i <= $times; $i++) {
        $pos = strpos($subject, $search, $pos);
        if($pos !== false) {
            $subject = substr($subject_original, 0, $pos);
            $subject .= $replace;
            $subject .= substr($subject_original, $pos + $len);
            $subject_original = $subject;
        } else {
            break;
        }
    }
    return($subject);
}

/**
 * format_tel - Format North American phone numbers
 * @global Debug $debug
 * @param integer $phone_number Phone number with no punctuation
 * @return string Phone number to display
 */
function format_tel($phone_number) 
{
    global $debug;

    if (!is_numeric($phone_number)) {
        $debug->addMessage('Phone number display function given non-numeric value');
        return $phone_number;
    }

    $format = SysConfig::get()->getValue('tel_format');
    if (strlen($phone_number) == 11) {
        $phone_number = preg_replace('/^1/', '', $phone_number);
    }
    if (strlen($phone_number) == 7) {
        switch ($format) {
        case '###.###.####':
            return substr($phone_number, 0, 3).'.'.substr($phone_number, 3, 4);
        default:
            return substr($phone_number, 0, 3).'-'.substr($phone_number, 3, 4);
        }
    }
    if (strlen($phone_number) != 10) {
        return $phone_number;
    }
    switch ($format) {
    case '(###) ###-####':
        return '('.substr($phone_number, 0, 3).') '.substr($phone_number, 3, 3).'-'.substr($phone_number, 6, 4);
    case '###-###-####':
        return substr($phone_number, 0, 3).'-'.substr($phone_number, 3, 3).'-'.substr($phone_number, 6, 4);
    case '###.###.####':
        return substr($phone_number, 0, 3).'.'.substr($phone_number, 3, 3).'.'.substr($phone_number, 6, 4);
    default:
        $debug->addMessage('Invalid phone number format', true);
        return $phone_number;
    }
}
