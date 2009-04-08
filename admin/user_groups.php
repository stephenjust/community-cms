<?php
	// Security Check
	if (@SECURITY != 1 || @ADMIN != 1) {
		die ('You cannot access this page directly.');
		}
	$content = NULL;
	if ($_GET['action'] == 'delete') {
		if($_GET['id'] == 1) {
			$message = 'Cannot delete Administrator group.';
			} else {
			$delete_group_query = 'DELETE FROM '.$CONFIG['db_prefix'].'user_groups WHERE id = '.(int)$_GET['id'];
			$delete_group = $db->query($delete_group_query);
			if(!$delete_group) {
				$content .= 'Failed to delete group.<br />';
				} else {
				$content .= 'Successfully deleted group.<br />'.log_action('Deleted group #'.(int)$_GET['id']);
				}
			}
		} // IF 'delete'

// ----------------------------------------------------------------------------

	if ($_GET['action'] == 'new') {
		if (strlen($_POST['group_name']) < 2) {
			$content .= '<strong>Error: </strong>Your group name was too short.<br />';
			} else {
			$create_group_query = 'INSERT INTO '.$CONFIG['db_prefix'].'user_groups (`name`, `label_format`) 
				VALUES ("'.addslashes($_POST['group_name']).'","'.addslashes($_POST['label_format']).'")';
			$create_group_handle = $db->query($create_group_query);
			if(!$create_group_handle) {
				$content .= '<strong>Error: </strong>Failed to create group.<br />';
				} else {
				$content .= 'Created group \''.$_POST['group_name'].'\'.<br />'.log_action('Created user group \''.addslashes($_POST['group_name']).'\'');
				}
			}
		}

// ----------------------------------------------------------------------------

	$tab_layout = new tabs;
	$tab_content['manage'] = '<table class="admintable">
<tr><th>ID</th><th width="350">Name:</th><th colspan="4">&nbsp;</th></tr>';
	$group_list_query = 'SELECT * FROM '.$CONFIG['db_prefix'].'user_groups ORDER BY name ASC';
	$group_list_handle = $db->query($group_list_query);
	$group_list_rows = $group_list_handle->num_rows;
 	if($group_list_rows == 0) {
 		$tab_content['manage'] .= '<tr class="row1"><td colspan="6">
 			An error may have occured. No groups were found.</td></tr>';
 		}
 	$rowstyle = 'row1';
	for ($i = 1; $i <= $group_list_rows; $i++) {
		$group_list = $group_list_handle->fetch_assoc();
		$tab_content['manage'] .= '<tr class="'.$rowstyle.'">
			<td>'.$group_list['id'].'</td>
			<td><span style="'.stripslashes($group_list['label_format']).'" 
			id="user_group_'.$group_list['id'].'">'.stripslashes($group_list['name']).'</span></td>
			<td><a href="admin.php?module=user_groups&action=delete&id='.$group_list['id'].'"><img src="<!-- $IMAGE_PATH$ -->delete.png" 
			alt="Delete" width="16px" height="16px" border="0px" /></a></td>
			<td><a href="#"><img src="<!-- $IMAGE_PATH$ -->edit.png" 
			alt="Edit" width="16px" height="16px" border="0px" /></a></td>
			<td><a href="#"><img src="<!-- $IMAGE_PATH$ -->permissions.png" 
			alt="Permissions" width="16px" height="16px" border="0px" /></a></td>
			<td><a href="#"><img src="<!-- $IMAGE_PATH$ -->members.png" 
			alt="Members" width="16px" height="16px" border="0px" /></a></td>
			</tr>';
		if($rowstyle == 'row1') {
			$rowstyle = 'row2';
			} else {
			$rowstyle = 'row1';
			}
		}
	$tab_content['manage'] .= '</table>';
	$tab['manage'] = $tab_layout->add_tab('Manage Groups',$tab_content['manage']);

// ----------------------------------------------------------------------------

	$tab_content['create'] = NULL;
	$tab_content['create'] .= '<form method="POST" action="admin.php?module=user_groups&action=new"><table class="admintable">
		<tr><td>Group Name:</td><td><input type="text" name="group_name" /></td>
		</tr>
		<tr><td>Styling:</td><td><input type="text" name="label_format" />CSS Code</td>
		</tr>
		<tr><td class="empty"></td><td><input type="submit" value="Create Group" /></td>
		</tr>
		</table></form>';
	$tab['create'] = $tab_layout->add_tab('Create Group',$tab_content['create']);

	$content .= $tab_layout;
?>