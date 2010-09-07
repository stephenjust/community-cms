<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2007-2010 Stephen Just
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

if (!$acl->check_permission('adm_newsletter') || !$acl->check_permission('admin_access')) {
	die ('You do not have the necessary permissions to access this page.');
}
if (!isset($_GET['page'])) {
	die ('No page ID provided to script.');
} else {
	$page_id = $_GET['page'];
	if ($page_id != '*') {
		$page_id = (int)$page_id;
	}
}

// Get article list
if (!is_numeric($page_id)) {
    $nl_list_query = 'SELECT * FROM `' . NEWSLETTER_TABLE . '`
		ORDER BY `year` DESC, `month` DESC';
} else {
    $nl_list_query = 'SELECT * FROM `' . NEWSLETTER_TABLE . '`
		WHERE `page` = '.$page_id.'
		ORDER BY `year` DESC, `month` DESC';
}
$nl_list_handle = $db->sql_query($nl_list_query);
$nl_list_rows = $db->sql_num_rows($nl_list_handle);
$list_rows = array();
$months = array('January','February','March','April','May','June','July',
	'August','September','October','November','December');
for ($i = 1; $i <= $nl_list_rows; $i++) {
    $nl_list = $db->sql_fetch_assoc($nl_list_handle);
	$current_row = array();
	$current_row[] = strip_tags(stripslashes($nl_list['label']));
	$current_row[] = $months[$nl_list['month']-1];
	$current_row[] = $nl_list['year'];
	if ($acl->check_permission('newsletter_delete')) {
		$current_row[] = '<a href="?module=newsletter&amp;action=delete&amp;id='
			.$nl_list['id'].'&amp;page='.$page_id.'">'
			.'<img src="./admin/templates/default/images/delete.png" alt="Delete" width="16px" '
			.'height="16px" border="0px" /></a>';
	}
	$current_row[] = '<a href="?module=newsletter&amp;action=edit&amp;id='
		.$nl_list['id'].'"><img src="./admin/templates/default/images/edit.png" '
		.'alt="Edit" width="16px" height="16px" border="0px" /></a>';
	$list_rows[] = $current_row;
} // FOR

$label_list = array('Label','Month','Year');
if ($acl->check_permission('newsletter_delete')) {
	$label_list[] = 'Delete';
}
$label_list[] = 'Edit';
$content = create_table($label_list,$list_rows);

echo $content;

clean_up();
?>