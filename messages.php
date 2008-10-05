<?php
	// Report all PHP errors
	error_reporting(E_ALL);
	header('Content-type: text/html; charset=utf-8');
	// The not-so-secure security check.
	define('SECURITY',1);
	define('ROOT','./');
	session_start();
	// Load error handling code
	require_once('./functions/error.php');
	// Load database configuration
	if(!include_once('./config.php')) {
		err_page(0001);
		}
	// Check if site is disabled.
	if($CONFIG['disabled'] == 1) {
		err_page(1);
		}
	// Try to establish a connection to the MySQL server using the MySQLi classes.		
	@ $db = new mysqli($CONFIG['db_host'],$CONFIG['db_user'],$CONFIG['db_pass'],$CONFIG['db_name']);
	if(mysqli_connect_errno()) {
		err_page(1001); // Database connect error.
		}

	// Once the database connections are made, include all other necessary files.
	if(!include_once('./include.php')) {
		err_page(2001); // File not found error.
		}
	// Initialize some variables to keep PHP from complaining.
	if(!isset($_GET['view'])) {
		$_GET['view'] = NULL;
		}
	checkuser(1);
	// Delete message
	if(!isset($_GET['del'])) {
		$_GET['del'] = "";
		}
	$content = NULL;
	if($_GET['del'] != "") {
		$del_query = 'DELETE FROM '.$CONFIG['db_prefix'].'messages WHERE id = '.$_GET['del'].' LIMIT 1';
		$del = $db->query($del_query);
		if(!$del) {
			$content .= 'Failed to delete message.<br />';
			} else {
			$content .= 'Successfully deleted message.<br />';
			}
		}
	
	// Load global site information.
	$site_info_query = 'SELECT * FROM '.$CONFIG['db_prefix'].'config';
	$site_info_handle = $db->query($site_info_query);
	$site_info = $site_info_handle->fetch_assoc();
	
	// Get message list
	$message_list_query = 'SELECT * FROM '.$CONFIG['db_prefix'].'messages WHERE recipient = '.$_SESSION['userid'].' ORDER BY id DESC';
	$message_list_handle = $db->query($message_list_query);
	$message_num_rows = $message_list_handle->num_rows;
	$template_query = 'SELECT * FROM '.$CONFIG['db_prefix'].'templates WHERE id = '.$site_info['template'].' LIMIT 1';
	$template_handle = $db->query($template_query);
	$template = $template_handle->fetch_assoc();
	$template_path = $template['path'];
	$message_list_template_file_path = $template_path."messages.html";
	$message_list_file_handle = fopen($message_list_template_file_path, "r");
	$message_list_template_file = fread($message_list_file_handle, filesize($message_list_template_file_path));
	fclose($message_list_file_handle);
	$i = 1;
	if($message_num_rows == 0) {
		$content .= 'No messages to be displayed.';
		}
	while($message_num_rows >= $i) {
		$message = $message_list_handle->fetch_assoc();
		$current_message = $message_list_template_file;
		$current_message = str_replace('<!-- $MESSAGE_BODY$ -->',stripslashes($message['message']),$current_message);
		$current_message = str_replace('<!-- $MESSAGE_ID$ -->',stripslashes($message['id']),$current_message);
		$content .= $current_message;
		$current_message = NULL;
		$i++;
		}
	// Display page
	$template_file = $template_path."index.html";
	$handle = fopen($template_file, "r");
	$template = fread($handle, filesize($template_file));
	fclose($handle);
	$page_title = 'Messages';
	$css_include = "<link rel='StyleSheet' type='text/css' href='".$template_path."style.css' />";
	$image_path = $template_path.'images/';
	$nav_bar = display_nav_bar();
	$nav_login = display_login_box($page_info,$site_info);
	$template = str_replace('<!-- $PAGE_TITLE$ -->',$page_title,$template);
	$template = str_replace('<!-- $CSS_INCLUDE$ -->',$css_include,$template);
	$template = str_replace('<!-- $NAV_BAR$ -->',$nav_bar,$template);
	$template = str_replace('<!-- $NAV_LOGIN$ -->',$nav_login,$template);
	$template = str_replace('<!-- $CONTENT$ -->',$content,$template);
	$template = str_replace('<!-- $IMAGE_PATH$ -->',$image_path,$template);
	$template = str_replace('<!-- $FOOTER$ -->','<a href="http://sourceforge.net"><img src="http://sflogo.sourceforge.net/sflogo.php?group_id=223968&amp;type=1" width="88" height="31" border="0" type="image/png" alt="SourceForge.net Logo" /></a>
 Powered by Community CMS',$template);
	echo $template;
	
	// Close database connections and clean up loose ends.
	$db->close();
?>