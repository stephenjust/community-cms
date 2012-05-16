<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2007-2010 Stephen Just
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
initialize();

// Initialize some variables to keep PHP from complaining.
$module = (isset($_GET['module'])) ? $_GET['module'] : NULL;
unset($_GET['module']);
if (!isset($_GET['view'])) {
	$_GET['view'] = NULL;
}
if (!isset($_GET['login'])) {
	$_GET['login'] = NULL;
}
if (!isset($_GET['action'])) {
	$_GET['action'] = NULL;
}
if (!isset($_GET['id'])) {
	$_GET['id'] = NULL;
}
if (!isset($_GET['ui'])) {
	$_GET['ui'] = 0;
}
// Run login checks.
if (!$acl->check_permission('admin_access')) {
	err_page(3004);
}
require(ROOT.'functions/admin.php');
require(ROOT.'includes/admin_page_class.php');

$admin_page = new admin_page($module);
admin_page::display_header();

function display_admin() {
	global $CONFIG;
	global $db;
	global $acl;
	global $module;

	$template_page = new template;
	$template_page->load_admin_file();

	$template_page->nav_bar = '<div id="menu">'.admin_nav().'</div>';
	$template_page->nav_login = Page::display_login_box();
	$template_page_bottom = $template_page->split('content');
	echo $template_page;
	unset($template_page);
	$content = NULL;
	if (isset($module)) {
		if (!include('./admin/'.addslashes($module).'.php')) {
			include('./admin/index.php');
		}
	} else {
		include('./admin/index.php');
	}
	$template_page_bottom->content = $content;
	echo $template_page_bottom;
	unset($template_page_bottom);
}

display_admin($content);
if (DEBUG === 1) {
	admin_page::display_debug();
}
admin_page::display_footer();

clean_up();
?>
