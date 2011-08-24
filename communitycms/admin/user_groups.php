<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2007-2010 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.admin
 */
// Security Check
if (@SECURITY != 1 || @ADMIN != 1) {
	die ('You cannot access this page directly.');
}

function perm_list($group = 0) {
	global $acl;

	$return = NULL;
	$permission_list = $acl->permission_list;
	$form_var_list = array_keys($permission_list);
	$form_var_list = array2csv($form_var_list);
	$return .= '<input type="hidden" name="var_list" value="'.$form_var_list.'" />';

	$return .= permission_list($acl->permission_list,$group,true);
	return $return;
}

$content = NULL;
if (!$acl->check_permission('adm_user_groups')) {
	$content .= '<span class="errormessage">You do not have the necessary permissions to use this module.</span><br />';
	return true;
}

if ($_GET['action'] == 'delete') {
	if ($_GET['id'] == 1) {
		$content .= '<span class="errormessage">Cannot delete Administrator group.</span><br />';
	} else {
		$delete_group_query = 'DELETE FROM ' . USER_GROUPS_TABLE . '
			WHERE id = '.(int)$_GET['id'];
		$delete_group = $db->sql_query($delete_group_query);
		if($db->error[$delete_group] === 1) {
			$content .= '<span class="errormessage">Failed to delete group.</span><br />';
		} else {
			$content .= 'Successfully deleted group.<br />';
			$log->new_message('Deleted group #'.(int)$_GET['id']);
		}
	}
} // IF 'delete'

// ----------------------------------------------------------------------------

if ($_GET['action'] == 'new') {
	if ($acl->check_permission('group_create')) {
		if (strlen($_POST['group_name']) < 2) {
			$content .= '<span class="errormessage">Error: Your group name was too short.</span><br />';
		} else {
			$create_group_query = 'INSERT INTO ' . USER_GROUPS_TABLE . '
				(`name`, `label_format`) VALUES
				(\''.addslashes($_POST['group_name']).'\',\''.addslashes($_POST['label_format']).'\')';
			$create_group_handle = $db->sql_query($create_group_query);
			if($db->error[$create_group_handle] === 1) {
				$content .= '<span class="errormessage">Error: Failed to create group.</span><br />';
			} else {
				$content .= 'Created group \''.$_POST['group_name'].'\'.<br />';
				$log->new_message('Created user group \''.addslashes($_POST['group_name']).'\'');
			}
		}
	}
}

// ----------------------------------------------------------------------------

if ($_GET['action'] == 'permsave') {
	$set_perm_error = 0;
	if (!isset($_POST['id']) || !isset($_POST['var_list'])) {
		$content .= '<span class="errormessage">Failed to update permissions.</span><br />';
	} else {
		$var_list = csv2array($_POST['var_list']);
		$id = (int)$_POST['id'];
		unset($_POST['id']);
		foreach ($var_list as $form_var) {
			if (!isset($_POST[$form_var])) {
				$form_var_value = NULL;
			} else {
				$form_var_value = $_POST[$form_var];
			}
			$new_setting = checkbox($form_var_value);
			if (array_key_exists($form_var,$acl->permission_list)) {
				$set_perm = $acl->set_permission($form_var,$new_setting,$id,true);
				if (!$set_perm) {
					$set_perm_error = 1;
				}
				unset($set_perm);
			} else {
				$debug->addMessage('Permission \''.$form_var.'\' does not exist',true);
			}
		}
		unset($form_var);
		unset($form_var_value);
		if ($set_perm_error == 0) {
			$content .= 'Updated permissions for group.<br />';
			$log->new_message('Updated group permissions');
		} else {
			$content .= '<span class="errormessage">Failed to update permissions.</span><br />';
		}
	}
	// in_array($string,$array)
}

// ----------------------------------------------------------------------------

$tab_layout = new tabs;

// ----------------------------------------------------------------------------

if ($_GET['action'] == 'perm') {
	$tab_content['permission'] = '<form method="post" action="admin.php?module=user_groups&action=permsave">
		<input type="hidden" name="id" value="'.(int)$_GET['id'].'" />';
	$tab_content['permission'] .= perm_list((int)$_GET['id']);
	$tab_content['permission'] .= '<input type="submit" value="Save" /></form>';
	unset($permission);

	$tab_layout->add_tab('Manage Group Permissions',$tab_content['permission']);
}

// ----------------------------------------------------------------------------

$tab_content['manage'] = '<table class="admintable">
<tr><th>ID</th><th width="350">Name:</th><th colspan="4">&nbsp;</th></tr>';
$group_list_query = 'SELECT * FROM ' . USER_GROUPS_TABLE . ' ORDER BY name ASC';
$group_list_handle = $db->sql_query($group_list_query);
$group_list_rows = $db->sql_num_rows($group_list_handle);
if ($group_list_rows == 0) {
	$tab_content['manage'] .= '<tr class="row1"><td colspan="6">
		An error may have occured. No groups were found.</td></tr>';
}
$rowstyle = 'row1';
for ($i = 1; $i <= $group_list_rows; $i++) {
	$group_list = $db->sql_fetch_assoc($group_list_handle);
	$tab_content['manage'] .= '<tr class="'.$rowstyle.'">
		<td>'.$group_list['id'].'</td>
		<td><span style="'.stripslashes($group_list['label_format']).'"
		id="user_group_'.$group_list['id'].'">'.stripslashes($group_list['name']).'</span></td>
		<td><a href="admin.php?module=user_groups&action=delete&id='.$group_list['id'].'"><img src="<!-- $IMAGE_PATH$ -->delete.png"
		alt="Delete" width="16px" height="16px" border="0px" />Delete</a></td>
		<td><strike>Edit</strike></td>
		<td><a href="admin.php?module=user_groups&action=perm&id='.$group_list['id'].'">
		<img src="<!-- $IMAGE_PATH$ -->permissions.png"
		alt="Permissions" width="16px" height="16px" border="0px" /></a></td>
		<td><strike>Members</strike></td>
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

if ($acl->check_permission('group_create')) {
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
}

$content .= $tab_layout;
?>