<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2007-2009 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @version SVN
 * @package CommunityCMS.main
 */
// The not-so-secure security check.
define('SECURITY',1);
define('ROOT','./');

$required_db_version = 0.05;
// Load error handling code
require_once('./functions/error.php');
// Load database configuration
if (@ !include_once('./config.php')) {
	if (@ !include_once('./config.temp.php')) {
		err_page(0001);
	}
}

if (!isset($CONFIG['db_engine'])) {
	err_page(15);
}

// Check if site is disabled.
if (@$CONFIG['disabled'] == 1 || @ $CONFIG['not_installed'] == 1) {
	err_page(11);
}

// Once the database connections are made, include all other necessary files.
require('./include.php');

// Page load timer
if (DEBUG === 1) {
	$time = microtime();
	$time = explode(" ", $time);
	$time = $time[1] + $time[0];
	$starttime = $time;
}

initialize();

// Check for up-to-date database
if(get_config('db_version') != $required_db_version) {
	err_page(10); // Wrong DB Version
}

// Initialize some variables to keep PHP from complaining.
if (!isset($_GET['id']) && !isset($_GET['page'])) {
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
if (!isset($_GET['view'])) {
	$_GET['view'] = NULL;
}
if (!isset($_GET['login'])) {
	$_GET['login'] = NULL;
}
// Run login checks.
$_POST['user'] = (!isset($_POST['user'])) ? NULL : $_POST['user'];
$_POST['passwd'] = (!isset($_POST['passwd'])) ? NULL : $_POST['passwd'];
if ($_GET['login'] != 1) {
	$_POST['user'] = NULL;
	$_POST['passwd'] = NULL;
}
if ($_GET['login'] == 1) {
	login($_POST['user'],$_POST['passwd']);
} elseif ($_GET['login'] == 2) {
	logout();
}
checkuser();
if (get_config('site_active') == 0) {
	err_page(12);
}

// Load page information.
$page = new page;
if (isset($_POST['vote']) && isset($_POST['vote_poll'])) {
	$question_id = $_POST['vote_poll'];
	$answer_id = $_POST['vote'];
	$user_ip = $_SERVER['REMOTE_ADDR'];
	poll_vote($question_id,$answer_id,$user_ip);
}
if ($page_id == NULL && $page_text_id != NULL) {
	$page->set_page($page_text_id,false);
} else {
	$page->set_page($page_id);
}
if (file_exists('./install')) {
	$debug->add_trace('The ./install directory still exists',true,'index.php');
}

// Display the page.
$page->display_header();
$page->display_content();
display_page($_GET['view']);
if (DEBUG === 1) {
	$db->print_query_stats();
	$db->print_queries();
	$debug->display_traces();
}
$page->display_footer();

clean_up();

// Page load timer
if (DEBUG === 1) {
	$time = microtime();
	$time = explode(" ", $time);
	$time = $time[1] + $time[0];
	$endtime = $time;
	$totaltime = ($endtime - $starttime);
	printf ("This page took %f seconds to load.", $totaltime);
}
?>