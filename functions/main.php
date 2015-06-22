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
 * @param integer $phone_number Phone number with no punctuation
 * @return string Phone number to display
 */
function format_tel($phone_number) 
{
    if (!is_numeric($phone_number)) {
        Debug::get()->addMessage('Phone number display function given non-numeric value');
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
        Debug::get()->addMessage('Invalid phone number format', true);
        return $phone_number;
    }
}
