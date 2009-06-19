<?php
/**
 * Community CMS
 * $Id$
 *
 * @copyright Copyright (C) 2007-2009 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.main
 */
header('Content-type: text/html; charset=utf-8');
// The not-so-secure security check.
define('SECURITY',1);
define('ROOT','./');
// Load error handling code
require_once ('./functions/error.php');
// Load database configuration
require_once('./config.php');
// Check if site is disabled.
if ($CONFIG['disabled'] == 1) {
	err_page(1);
}

// Once the database connections are made, include all other necessary files.
require_once('./include.php');
if (DEBUG == 1) {
	// Report all PHP errors
	error_reporting(E_ALL);
}
initialize();

// Initialize some variables to keep PHP from complaining.
if (!isset($_GET['view'])) {
	$_GET['view'] = NULL;
}
checkuser(1);
// Delete message
if (!isset($_GET['del'])) {
	$_GET['del'] = "";
}
$_GET['del'] = (int)$_GET['del'];
$content = NULL;
if ($_GET['del'] != "") {
	$del_query = 'DELETE FROM ' . MESSAGE_TABLE . '
		WHERE id = '.(int)$_GET['del'];
	$del = $db->sql_query($del_query);
	if ($db->error[$del] === 1) {
		$content .= 'Failed to delete message.<br />';
	} else {
		$content .= 'Successfully deleted message.<br />';
	}
}

$page = new page;
$page->id = 0;

// Get message list
$message_list_query = 'SELECT * FROM ' . MESSAGE_TABLE . '
	WHERE recipient = '.(int)$_SESSION['userid'].' ORDER BY id DESC';
$message_list_handle = $db->sql_query($message_list_query);
$message_num_rows = $db->sql_num_rows($message_list_handle);
$template_query = 'SELECT * FROM ' . TEMPLATE_TABLE . ' WHERE id = '.$site_info['template'].' LIMIT 1';
$template_handle = $db->sql_query($template_query);
$template = $db->sql_fetch_assoc($template_handle);
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
	$message = $db->sql_fetch_assoc($message_list_handle);
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
$nav_login = display_login_box();
$template = str_replace('<!-- $PAGE_TITLE$ -->',stripslashes($page_title),$template);
$template = str_replace('<!-- $CSS_INCLUDE$ -->',$css_include,$template);
$template = str_replace('<!-- $NAV_BAR$ -->',$nav_bar,$template);
$template = str_replace('<!-- $NAV_LOGIN$ -->',$nav_login,$template);
$template = str_replace('<!-- $CONTENT$ -->',$content,$template);
$template = str_replace('<!-- $IMAGE_PATH$ -->',$image_path,$template);
$template = str_replace('<!-- $FOOTER$ -->',stripslashes($site_info['footer']),$template);
echo $template;

// Close database connections and clean up loose ends.
clean_up();
?>