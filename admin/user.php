<?php
	// Security Check
	if (@SECURITY != 1 || @ADMIN != 1) {
		die ('You cannot access this page directly.');
		}
	$root = "./";
	$message = NULL;
	if ($_GET['action'] == 'delete') {
		if($_GET['id'] == 1) {
			$message = 'Cannot delete Administrator.';
			} else {
			$delete_user_query = 'DELETE FROM '.$CONFIG['db_prefix'].'users WHERE id = '.$_GET['id'];
			$delete_user = $db->query($delete_user_query);
			if(!$delete_user) {
				$message = 'Failed to delete user. '.mysqli_error($db);
				} else {
				$message = 'Successfully deleted user. '.log_action('Deleted user #'.$_GET['id']);
				}
			}
		} // IF 'delete'

// ----------------------------------------------------------------------------

	$content = $message;
	$content = $content.'<h1>User List</h1>
<table class="admintable">
<tr><th>ID</th><th width="350">Name:</th><th colspan="2">&nbsp;</th></tr>';
	$page_list_query = 'SELECT * FROM '.$CONFIG['db_prefix'].'users ORDER BY realname ASC';
	$page_list_handle = $db->query($page_list_query);
	$page_list_rows = $page_list_handle->num_rows;
 	if($page_list_rows == 0) {
 		$content = $content.'<tr class="row1"><td colspan="4">An error has occured. No users were found.</td></tr>';
 		}
 	$rowstyle = 'row1';
	for ($i = 1; $i <= $page_list_rows; $i++) {
		$page_list = $page_list_handle->fetch_assoc();
		$content = $content.'<tr class="'.$rowstyle.'">
<td>'.$page_list['id'].'</td>
<td>'.stripslashes($page_list['realname']).'</td>
<td><a href="?module=user&action=delete&id='.$page_list['id'].'"><img src="<!-- $IMAGE_PATH$ -->delete.png" alt="Delete" width="16px" height="16px" border="0px" /></a></td>
<td><a href="?module=user_edit&id='.$page_list['id'].'"><img src="<!-- $IMAGE_PATH$ -->edit.png" alt="Edit" width="16px" height="16px" border="0px" /></a></td>
</tr>';
		if($rowstyle == 'row1') {
			$rowstyle = 'row2';
			} else {
			$rowstyle = 'row1';
			}
		}
$content = $content.'</table>';
?>