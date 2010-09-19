<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2010 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.admin
 */
/**#@+
 * @ignore
 */
define('ADMIN',1);
define('SECURITY',1);
define('ROOT','../../');
/**#@-*/

include (ROOT . 'config.php');
include (ROOT . 'include.php');
include (ROOT . 'functions/admin.php');

initialize('ajax');

if (!$acl->check_permission('adm_page_message') || !$acl->check_permission('admin_access')) {
	die ('<span class="errormessage">You do not have the necessary permissions to access this page.</span><br />');
}

// Get current page ID
if (!isset($_GET['page'])) {
	die('<span class="errormessage">No page provided.</span><br />');
} else {
	$page_id = (int)$_GET['page'];
}

$page_message_query = 'SELECT * FROM `'.PAGE_MESSAGE_TABLE.'`
	WHERE `page_id` = '.$page_id;
$page_message_handle = $db->sql_query($page_message_query);
if ($db->error[$page_message_handle] === 1) {
	die ('<span class="errormessage">Failed to load page messages.</span><br />');
}
$page_message_rows = $db->sql_num_rows($page_message_handle);

$table_headings = array('Content');
if ($acl->check_permission('page_message_delete')) {
	$table_headings[] = 'Delete';
}
if ($acl->check_permission('page_message_edit')) {
	$table_headings[] = 'Edit';
}
$table_rows = array();

for ($i = 1; $i <= $page_message_rows; $i++) {
	$page_message = $db->sql_fetch_assoc($page_message_handle);
	$current_row = array();
	$current_row[] = truncate(strip_tags(stripslashes($page_message['text']),'<br>'),75);
	if ($acl->check_permission('page_message_delete')) {
		$current_row[] = '<a href="javascript:confirm_delete(\'?module=page_message&'
			.'action=delete&id='.$page_message['message_id'].'&amp;'
			.'page='.$page_id.'\')"><img src="./admin/templates/default/images/delete.png" '
			.'alt="Delete" width="16px" height="16px" border="0px" /></a>';
	}
	if ($acl->check_permission('page_message_edit')) {
		$current_row[] = '<a href="?module=page_message_edit&amp;'
			.'id='.$page_message['message_id'].'">'
			.'<img src="./admin/templates/default/images/edit.png" alt="Edit" '
			.'width="16px" height="16px" border="0px" /></a>';
	}
	$table_rows[] = $current_row;
} // FOR

$content = create_table($table_headings,$table_rows);

echo $content;

clean_up();
?>