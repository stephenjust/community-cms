<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2007-2013 Stephen Just
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
require_once(ROOT.'config.php');
require_once(ROOT.'include.php');

// Page load timer
if (DEBUG === 1) {
	$time = microtime();
	$time = explode(" ", $time);
	$time = $time[1] + $time[0];
	$starttime = $time;
}

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
	try {
		$poll = new Poll($question_id);
		$poll->vote($answer_id, $user_ip);
		Page::$notification .= 'Thank you for voting.<br />';
	}
	catch (PollException $e) {
		Page::$notification .= '<span class="errormessage">'.$e->getMessage.'</span><br />';
	}
}
if ($page_id == NULL && $page_text_id != NULL) {
	Page::set_page($page_text_id,false);
} else {
	Page::set_page($page_id);
}
if (file_exists('./install')) {
	$debug->addMessage('The ./install directory still exists',true);
}

// Display the page.
Page::display_header();
Page::display_left();
Page::display_right();
Page::display_content();
if (DEBUG === 1) {
	Page::display_debug();
}
Page::display_footer();

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