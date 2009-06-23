<?php
/**
 * Community CMS
 * $Id$
 *
 * @copyright Copyright (C) 2007-2009 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.admin
 */

/**
 * @ignore
 */
DEFINE('SECURITY',1);
DEFINE('ADMIN',1);
define('ROOT','./');

$content = NULL;
// Load error handling code
require_once('./functions/error.php');
// Load database configuration
if (!include_once('./config.php')) {
	err_page(0001);
}
// Check if site is disabled.
if ($CONFIG['disabled'] == 1) {
	err_page(1);
}
// Once the database connections are made, include all other necessary files.
if (!include_once('./include.php')) {
	err_page(2001);
}
$debug = new debug;
initialize();

// Initialize some variables to keep PHP from complaining.
if (!isset($_GET['view'])) {
	$_GET['view'] = NULL;
}
if (!isset($_GET['login'])) {
	$_GET['login'] = NULL;
}
if (!isset($_GET['module'])) {
	$_GET['module'] = NULL;
}
if (!isset($_GET['action'])) {
	$_GET['action'] = NULL;
}
if (!isset($_GET['id'])) {
	$_GET['id'] = NULL;
}
// Run login checks.
checkuser_admin();
include('./functions/admin.php');
include('./includes/admin.php');
function display_admin() {
	global $CONFIG;
	global $db;
	global $acl;
	global $site_info;
	adm_display_header();
	adm_display_navigation();
	$template_page = new template;
	$template_page->load_admin_file();
	$page_title = 'Community CMS Administration';
	$image_path = $template_page->path.'images/';
		$template_page->nav_login = display_login_box();
		$template_page->page_title = $page_title;
		$template_page_bottom = $template_page->split('content');
		echo $template_page;
		unset($template_page);
		if(isset($_GET['module'])) {
			if(!include('./admin/'.addslashes($_GET['module']).'.php')) {
				include('./admin/index.php');
				}
			} else {
			include('./admin/index.php');
			}
		$template_page_bottom->content = $content;
		$template_page_bottom->image_path = $image_path;
		if(DEBUG === 1) {
			$query_debug = $db->print_error_query();
		} else {
			$query_debug = NULL;
		}
		$template_page_bottom->footer = 'Powered by Community CMS'.$query_debug;
		echo $template_page_bottom;
		unset($template_page_bottom);
		}
	display_admin($content);

	clean_up();
?>
