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
				$message = 'Successfully deleted user.';
				}
			}
		}
	$content = $message;
$content = $content.'<h1>User List</h1>
<table class="admintable">
<tr><td width="350">Name:</td><td>Del</td><td>Edit</td></tr>';
	$page_list_query = 'SELECT * FROM '.$CONFIG['db_prefix'].'users ORDER BY realname ASC';
	$page_list_handle = $db->query($page_list_query);
	$page_list_rows = $page_list_handle->num_rows;
 	$i = 1;
 	if($page_list_rows == 0) {
 		$content = $content.'<tr><td class="adm_page_list_item">An error has occured. No users were found.</td><td></td><td></td></tr>';
 		}
	while ($i <= $page_list_rows) {
		$page_list = $page_list_handle->fetch_assoc();
		$content = $content.'<tr>
<td class="adm_page_list_item">'.stripslashes($page_list['realname']).'</td>
<td><a href="?module=user&action=delete&id='.$page_list['id'].'"><img src="<!-- $IMAGE_PATH$ -->delete.png" alt="Delete" width="16px" height="16px" border="0px" /></a></td>
<td><a href="?module=user_edit&id='.$page_list['id'].'"><img src="<!-- $IMAGE_PATH$ -->edit.png" alt="Edit" width="16px" height="16px" border="0px" /></a></td>
</tr>';
		$i++;
	}
$content = $content.'</table>';
?>