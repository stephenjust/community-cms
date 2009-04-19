<?php
/**
 * Community CMS
 *
 *
 * @copyright Copyright (C) 2007-2009 Stephen Just
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
		
	// Initialize some variables to keep PHP from complaining.
	if(!isset($_GET['id']) && !isset($_GET['page'])) {
		$page_id = $site_info['home'];
		$page_text_id = NULL;
		} else {
		if(isset($_GET['page'])) {
			$page_id = NULL;
			$page_text_id = addslashes($_GET['page']);
			} else {
			$page_id = (int)$_GET['id'];
			$page_text_id = NULL;
			}
		}
    // TODO: Change header to use 'text id' instead of 'id' if 'id' is given
    // header("Location: http://www.example.com/");
	if(!isset($_GET['view'])) {
		$_GET['view'] = NULL;
		}
	if(!isset($_GET['login'])) {
		$_GET['login'] = NULL;
		}
	// Run login checks.
	if($_GET['login'] != 1) {
	  $_POST['user'] = NULL;
	  $_POST['passwd'] = NULL;
		}
	if($_GET['login'] == 1) {
		login($_POST['user'],$_POST['passwd']);
		} elseif($_GET['login'] == 2) {
		logout();
		}
	checkuser();
	if($site_info['active'] == 0) {
		err_page(12);
		}

	// Load page information.
    $page = new page;
    if(isset($_POST['vote']) && isset($_POST['vote_poll'])) {
        $question_id = $_POST['vote_poll'];
        $answer_id = $_POST['vote'];
        $user_ip = $_SERVER['REMOTE_ADDR'];
        poll_vote($question_id,$answer_id,$user_ip);
    }
    $page->set_id($page_id);
    $page->set_text_id($page_text_id);
	if(file_exists('./install')) {
		$page->notification .= 'Please delete your ./install directory.<br />';
		}
	if(strlen($page_text_id) > 1) {
		$page_info_query = 'SELECT * FROM '.$CONFIG['db_prefix'].'pages WHERE text_id = \''.$page_text_id.'\'';
		} else {
		$page_info_query = 'SELECT * FROM '.$CONFIG['db_prefix'].'pages WHERE id = \''.$page_id.'\'';
		}
    // TODO: Finish page as a class implementation.
	$page_info_handle = $db->query($page_info_query);
	$page_info = $page_info_handle->fetch_assoc();
	
	// Display the page.
    $page->display_header();
	display_page($page_info,$site_info,$_GET['view']);
	$page->display_footer();

	clean_up();
?>