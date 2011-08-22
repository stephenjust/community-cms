<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2007-2010 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @version SVN
 * @package CommunityCMS.main
 */
// The not-so-secure security check.
define('SECURITY',1);
define('ROOT','./');

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

global $user;

// Perform login/logout operation
$login = (isset($_GET['login'])) ? $_GET['login'] : NULL;
$_POST['user'] = (isset($_POST['user'])) ? $_POST['user'] : NULL;
$_POST['passwd'] = (isset($_POST['passwd'])) ? $_POST['passwd'] : NULL;
if ($login == 1) {
	$user->login($_POST['user'],$_POST['passwd']);
} elseif ($login == 2) {
	$user->logout();
}
unset($_POST['user']);
unset($_POST['passwd']);

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
		// Don't cast (int) on $page_id because it could be a special page (text)
		$page_id = $_GET['id'];
		$page_text_id = NULL;
	}
}
unset($_GET['page'],$_GET['id']);

// Load page information.
$page = new Page;
if (isset($_POST['vote']) && isset($_POST['vote_poll'])) {
	$question_id = (int)$_POST['vote_poll'];
	$answer_id = (int)$_POST['vote'];
	$user_ip = $_SERVER['REMOTE_ADDR'];
	poll_vote($question_id,$answer_id,$user_ip);
}
if ($page_id == NULL && $page_text_id != NULL) {
	$page->set_page($page_text_id,false);
} else {
	$page->set_page($page_id);
}
if (file_exists('./install')) {
	$debug->add_trace('The ./install directory still exists',true);
}

// Display the page.
$page->display_header();
$page->display_left();
$page->display_right();
$page->display_content();
if (DEBUG === 1) {
	$page->display_debug();
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