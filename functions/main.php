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
