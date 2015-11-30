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

    if ($mode == 'ajax') {
        define('SCRIPT', 1);
    }

    new Bootstrap();

    new LoginController();

    return;
}
