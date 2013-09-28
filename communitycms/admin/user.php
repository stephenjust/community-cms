<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2007-2012 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.admin
 */

require_once(ROOT.'includes/User.class.php');

// Security Check
if (@SECURITY != 1 || @ADMIN != 1) {
	die ('You cannot access this page directly.');
}

global $acl;
if (!$acl->check_permission('adm_user'))
	throw new AdminException('You do not have the necessary permissions to access this module.');

switch ($_GET['action']) {
	case 'delete':
		if (!$acl->check_permission('user_delete')) {
			echo '<span class="errormessage">You do not have the necessary permissions to delete a user.</span><br />';
			break;
		}
		if ($_GET['id'] == 1) {
			echo '<span class="errormessage">Cannot delete Administrator.</span><br />';
			break;
		}
		$delete_user_query = 'DELETE FROM ' . USER_TABLE . '
			WHERE id = '.(int)$_GET['id'];
		$delete_user = $db->sql_query($delete_user_query);
		if ($db->error[$delete_user] === 1) {
			echo 'Failed to delete user.<br />';
			break;
		}
		echo 'Successfully deleted user.<br />';
		Log::addMessage('Deleted user #'.$_GET['id']);
		break;
		
	case 'create':
		try {
			if (!isset($_POST['username']) || !isset($_POST['pass']) ||
					!isset($_POST['pass_conf']) || !isset($_POST['first_name']) ||
					!isset($_POST['surname']) || !isset($_POST['telephone']) ||
					!isset($_POST['address']) && isset($_POST['email'])) {
				throw new Exception('You did not fill out a required field.');
			}
			if ($_POST['pass'] != $_POST['pass_conf'])
				throw new Exception('Passwords do not match.');
			User::create($_POST['username'], $_POST['pass'], $_POST['first_name'],
					$_POST['surname'], $_POST['telephone'], $_POST['address'],
					$_POST['email'], $_POST['title'], $_POST['groups']);

			echo "Account created.";
		}
		catch (Exception $e) {
			echo '<span class="errormessage">'.$e->getMessage().'</span><br />';
		}
		break;

	default:
		break;
}

$tab_layout = new tabs;

// ----------------------------------------------------------------------------

$tab_content['manage'] = '<table class="admintable">
<tr><th>ID</th><th>Username</th><th width="350">Name</th>';
$cols = 4;
if ($acl->check_permission('user_delete')) {
	$tab_content['manage'] .= '<th></th>';
	$cols++;
}
$tab_content['manage'] .= "<th></th></tr>\n";
$page_list_query = 'SELECT * FROM ' . USER_TABLE . '
	ORDER BY realname ASC';
$page_list_handle = $db->sql_query($page_list_query);
$page_list_rows = $db->sql_num_rows($page_list_handle);
if($page_list_rows == 0) {
	$tab_content['manage'] .= '<tr class="row1"><td colspan="'.$cols.'">An error has occured. No users were found.</td></tr>';
}
$rowstyle = 'row1';
for ($i = 1; $i <= $page_list_rows; $i++) {
	$page_list = $db->sql_fetch_assoc($page_list_handle);
	$tab_content['manage'] .= '<tr class="'.$rowstyle.'">
		<td>'.$page_list['id'].'</td>
		<td>'.$page_list['username'].'</td>
		<td>'.stripslashes($page_list['realname']).'</td>';
	if ($acl->check_permission('user_delete')) {
		$tab_content['manage'] .= '<td><a href="?module=user&action=delete&id='.$page_list['id'].'"><img src="<!-- $IMAGE_PATH$ -->delete.png" alt="Delete" width="16px" height="16px" border="0px" /></a></td>';
	}
	$tab_content['manage'] .= '<td><a href="?module=user_edit&id='.$page_list['id'].'"><img src="<!-- $IMAGE_PATH$ -->edit.png" alt="Edit" width="16px" height="16px" border="0px" /></a></td>
		</tr>';
	if ($rowstyle == 'row1') {
		$rowstyle = 'row2';
	} else {
		$rowstyle = 'row1';
	}
}
$tab_content['manage'] .= '</table>';
$tab_layout->add_tab('Manage Users',$tab_content['manage']);

// ----------------------------------------------------------------------------

if ($acl->check_permission('user_create')) {
	$form = new form;
	$form->set_target('admin.php?module=user&amp;action=create');
	$form->set_method('post');
	$form->add_textbox('username','User Name');
	$form->add_password('pass','Password');
	$form->add_password('pass_conf','Confirm Password');
	$form->add_textbox('first_name','First Name');
	$form->add_textbox('surname','Surname');
	$form->add_textbox('title','Title/Position');
	$form->add_textbox('telephone','Phone Number');
	$form->add_textbox('address','Address');
	$form->add_textbox('email','Email Address');
	$group_list_query = 'SELECT * FROM ' . USER_GROUPS_TABLE . ' ORDER BY name ASC';
	$group_list_handle = $db->sql_query($group_list_query);
	$group_list_rows = $db->sql_num_rows($group_list_handle);
	if ($group_list_rows == 0) {
		$form->add_text(' An error may have occured. No groups were found.');
	} else {
		for ($i = 0; $i < $group_list_rows; $i++) {
			$group_list = $db->sql_fetch_assoc($group_list_handle);
			$group_list_id[$i] = $group_list['id'];
			$group_list_name[$i] = $group_list['name'];
		}
		$form->add_multiselect('groups','Groups',$group_list_id,$group_list_name,NULL,5,'style="height: 4em;"');
	}
	$form->add_submit('submit','Create User');
	$tab_content['create'] = $form;
	$tab_layout->add_tab('Create User',$tab_content['create']);
}

echo $tab_layout;
?>