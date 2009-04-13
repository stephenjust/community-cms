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
	// Report all PHP errors
	error_reporting(E_ALL);
	header('Content-type: text/html; charset=UTF-8');
	// The not-so-secure security check.
	define('SECURITY',1);
	define('ROOT','./');
	session_start();
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
	// Try to establish a connection to the MySQL server using the MySQLi classes.		
	@ $db = new mysqli($CONFIG['db_host'],$CONFIG['db_user'],$CONFIG['db_pass'],$CONFIG['db_name']);
	if(mysqli_connect_errno()) {
		err_page(1001); // Database connect error.
		}

	// Once the database connections are made, include all other necessary files.
	if(!include_once('./include.php')) {
		err_page(2001); // File not found error.
		}
		
	// Load global site information.
	$site_info_query = 'SELECT * FROM '.$CONFIG['db_prefix'].'config';
	$site_info_handle = $db->query($site_info_query);
    if(!$site_info_handle) {
        err_page(1001);
    }
	$site_info = $site_info_handle->fetch_assoc();
		
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
	if(file_exists('./install')) {
		$NOTIFICATION .= 'Please delete your ./install directory.<br />';
		}
	if($site_info['active'] == 0) {
		err_page(12);
		}
//	if(is_writeable('./config.php')) {
//		$NOTIFICATION .= 'Please change the permissions on ./config.php to 0755 or something else that makes it unwriteable.<br />';
//		}

	// Load page information.
	if($page_text_id != NULL) {
		$page_info_query = 'SELECT * FROM '.$CONFIG['db_prefix'].'pages WHERE text_id = \''.$page_text_id.'\'';
		} else {
		$page_info_query = 'SELECT * FROM '.$CONFIG['db_prefix'].'pages WHERE id = \''.$page_id.'\'';
		}
	$page_info_handle = $db->query($page_info_query);
	$page_info = $page_info_handle->fetch_assoc();
	
	// Display the page.
	display_page($page_info,$site_info,$_GET['view']);
	
	// Close database connections and clean up loose ends.
	$db->close();
?>