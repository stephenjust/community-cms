<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2007-2010 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.admin
 */

namespace CommunityCMS;

/**
 * @ignore
 */
DEFINE('SECURITY',1);
DEFINE('ADMIN',1);
define('ROOT','./');

require_once('vendor/autoload.php');

// Load required includes
require(ROOT.'functions/error.php');
require(ROOT.'include.php');

initialize();

// Initialize some variables to keep PHP from complaining.
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
if (!acl::get()->check_permission('admin_access')) {
	err_page(3004);
}
require(ROOT.'functions/admin.php');
require(ROOT.'includes/admin_page_class.php');

$module = (isset($_GET['module'])) ? $_GET['module'] : NULL;
unset($_GET['module']);
AdminPage::setModule($module);
AdminPage::display_header();
AdminPage::display_admin();

if (DEBUG === 1) {
	AdminPage::display_debug();
}
AdminPage::display_footer();

clean_up();
