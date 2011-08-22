<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2010 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.main
 */
/**#@+
 * @ignore
 */
define('SECURITY',1);
define('ROOT','../');
/**#@-*/

// Load error handling code
require(ROOT.'functions/error.php');
// Load database configuration
require(ROOT.'config.php');

if (!isset($CONFIG['db_engine'])) {
	err_page(15);
}

// Check if site is disabled.
if (@$CONFIG['disabled'] == 1 || @ $CONFIG['not_installed'] == 1) {
	err_page(11);
}

// Once the database connections are made, include all other necessary files.
require(ROOT.'include.php');

initialize();

// Check if site is active
if (get_config('site_active') == 0) {
	err_page(12);
}

// Initialize some variables to keep PHP from complaining
$view = (isset($_GET['view'])) ? $_GET['view'] : NULL;
unset($_GET['view']);

// Figure out which page to fetch from the provided variables
if (!isset($_GET['id']) && !isset($_GET['page'])) {
	// No page provided - go to home page
	$page_id = get_config('home');
	$page_text_id = NULL;
} else {
	if (isset($_GET['page'])) {
		$page_id = NULL;
		$page_text_id = addslashes($_GET['page']);
	} else {
		$page_id = (int)$_GET['id'];
		$page_text_id = NULL;
	}
}
unset($_GET['page'],$_GET['id']);

// Load page information.
$page = new Page;
if ($page_id == NULL && $page_text_id != NULL) {
	$page->set_page($page_text_id,false);
} else {
	$page->set_page($page_id);
}
if (Page::$type == 'calendar.php') {
	Page::$notification .= 'The Calendar page type does not work properly with tabbed pages.';
}

// Display page content
$page->display_content();

clean_up();
?>