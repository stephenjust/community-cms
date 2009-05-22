<?php
/**
 * Community CMS
 * $Id$
 *
 * @copyright Copyright (C) 2007-2009 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.admin
 */
// Security Check
if (@SECURITY != 1 || @ADMIN != 1) {
	die ('You cannot access this page directly.');
}

$content = NULL;
if ($_GET['action'] == 'delete') {
	if ($_GET['id'] == 1) {
		$content .= 'Cannot delete Administrator.<br />';
	} else {
		$delete_user_query = 'DELETE FROM ' . USER_TABLE . '
			WHERE id = '.(int)$_GET['id'];
		$delete_user = $db->sql_query($delete_user_query);
		if ($db->error[$delete_user] === 1) {
			$content .= 'Failed to delete user.<br />';
		} else {
			$content .= 'Successfully deleted user.<br />'.log_action('Deleted user #'.$_GET['id']);
		}
	}
} // IF 'delete'

// ----------------------------------------------------------------------------

$content .= '<h1>User List</h1>
<table class="admintable">
<tr><th>ID</th><th width="350">Name:</th><th colspan="2">&nbsp;</th></tr>';
$page_list_query = 'SELECT * FROM ' . USER_TABLE . '
	ORDER BY realname ASC';
$page_list_handle = $db->sql_query($page_list_query);
$page_list_rows = $db->sql_num_rows($page_list_handle);
if($page_list_rows == 0) {
	$content .= '<tr class="row1"><td colspan="4">An error has occured. No users were found.</td></tr>';
}
$rowstyle = 'row1';
for ($i = 1; $i <= $page_list_rows; $i++) {
	$page_list = $db->sql_fetch_assoc($page_list_handle);
	$content .= '<tr class="'.$rowstyle.'">
		<td>'.$page_list['id'].'</td>
		<td>'.stripslashes($page_list['realname']).'</td>
		<td><a href="?module=user&action=delete&id='.$page_list['id'].'"><img src="<!-- $IMAGE_PATH$ -->delete.png" alt="Delete" width="16px" height="16px" border="0px" /></a></td>
		<td><a href="?module=user_edit&id='.$page_list['id'].'"><img src="<!-- $IMAGE_PATH$ -->edit.png" alt="Edit" width="16px" height="16px" border="0px" /></a></td>
		</tr>';
	if ($rowstyle == 'row1') {
		$rowstyle = 'row2';
	} else {
		$rowstyle = 'row1';
	}
}
$content .= '</table>';
?>