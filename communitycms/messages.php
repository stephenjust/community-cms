<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2007-2010 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.main
 */
header('Content-type: text/html; charset=utf-8');
// The not-so-secure security check.
/**#@+
 * @ignore
 */
define('SECURITY',1);
define('ROOT','./');
/**#@-*/
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

global $user;
if (!$user->logged_in) {
	err_page(3004);
}

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

$page = new Page;
$page->id = 0;
Page::$title .= 'Messages';
Page::$type = 'special.php';
Page::$exists = true;
Page::display_header();
Page::display_left();
Page::display_right();

// Get message list
$message_list_query = 'SELECT * FROM ' . MESSAGE_TABLE . '
	WHERE recipient = '.(int)$_SESSION['userid'].' ORDER BY id DESC';
$message_list_handle = $db->sql_query($message_list_query);
$message_num_rows = $db->sql_num_rows($message_list_handle);
$template_query = 'SELECT * FROM ' . TEMPLATE_TABLE . ' WHERE `id` = '.get_config('site_template').' LIMIT 1';
$template_handle = $db->sql_query($template_query);
$template = $db->sql_fetch_assoc($template_handle);
$template_path = $template['path'];
$message_list_template_file_path = $template_path."messages.html";
$message_list_file_handle = fopen($message_list_template_file_path, "r");
$message_list_template_file = fread($message_list_file_handle, filesize($message_list_template_file_path));
fclose($message_list_file_handle);
$i = 1;
if ($message_num_rows == 0) {
	$content .= 'No messages to be displayed.';
}
while ($message_num_rows >= $i) {
	$message = $db->sql_fetch_assoc($message_list_handle);
	$current_message = $message_list_template_file;
	$current_message = str_replace('<!-- $MESSAGE_BODY$ -->',stripslashes($message['message']),$current_message);
	$current_message = str_replace('<!-- $MESSAGE_ID$ -->',stripslashes($message['id']),$current_message);
	$content .= $current_message;
	$current_message = NULL;
	$i++;
}
$page->content = $content;

Page::display_content();
if (DEBUG === 1) {
	Page::display_debug();
}
Page::display_footer();

// Close database connections and clean up loose ends.
clean_up();
