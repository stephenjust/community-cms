<?php
/**
 * Community CMS
 * $Id$
 *
 * @copyright Copyright (C) 2007-2009 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.main
 */
// The not-so-secure security check.
define('SECURITY',1);
define('ROOT','./');
// Load error handling code
require_once('./functions/error.php');
// Load database configuration
if(@ !include_once('./config.php')) {
    if(@ !include_once('./config.temp.php')) {
        err_page(0001);
        }
    }
// Check if site is disabled.
if(@$CONFIG['disabled'] == 1 || @ $CONFIG['not_installed'] == 1) {
    err_page(11);
    }
$NOTIFICATION = NULL;

// Once the database connections are made, include all other necessary files.
if(!include_once('./include.php')) {
    err_page(2001); // File not found error.
}

initialize();
checkuser();

if($site_info['active'] == 0) {
    err_page(12);
    }

// Load page information.
$page = new page;
$page->set_type('settings_main');
if(file_exists('./install')) {
    $page->notification .= 'Please delete your ./install directory.<br />';
}
// Display the page.
$page->display_header();
display_page(NULL,$site_info);
$page->display_footer();

clean_up();
?>
