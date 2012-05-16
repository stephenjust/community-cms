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

if (!$acl->check_permission('adm_user'))
	throw new AdminException('You do not have the necessary permissions to access this module.');

$content = NULL;
switch ($_GET['action']) {
	case 'delete':
		if (!$acl->check_permission('user_delete')) {
			$content .= '<span class="errormessage">You do not have the necessary permissions to delete a user.</span><br />';
			break;
		}
		if ($_GET['id'] == 1) {
			$content .= '<span class="errormessage">Cannot delete Administrator.</span><br />';
			break;
		}
		$delete_user_query = 'DELETE FROM ' . USER_TABLE . '
			WHERE id = '.(int)$_GET['id'];
		$delete_user = $db->sql_query($delete_user_query);
		if ($db->error[$delete_user] === 1) {
			$content .= 'Failed to delete user.<br />';
			break;
		}
		$content .= 'Successfully deleted user.<br />';
		Log::addMessage('Deleted user #'.$_GET['id']);
		break;

// ----------------------------------------------------------------------------

	case 'create':
		if (!$acl->check_permission('user_create')) {
			$content .= '<span class="errormessage">You do not have the necessary permissions to create a new user.</span><br />';
			break;
		}
		if (!isset($_POST['username']) || !isset($_POST['pass']) ||
				!isset($_POST['pass_conf']) || !isset($_POST['first_name']) ||
				!isset($_POST['surname']) || !isset($_POST['telephone']) ||
				!isset($_POST['address']) && isset($_POST['email'])) {
			$content .= '<span class="errormessage">You did not fill out a required field.</span><br />';
			break;
		}
		$error = false;
		$username = addslashes($_POST['username']);
		$pass = $_POST['pass'];
		$pass_conf = $_POST['pass_conf'];
		if (preg_match('/,/',$_POST['surname'])) {
			$content .= '<span class="errormessage">You cannot have a comma in your surname.</span><br />';
			$error = true;
		}
		if (preg_match('/,/',$_POST['first_name'])) {
			$content .= '<span class="errormessage">You cannot have a comma in your first name.</span><br />';
			$error = true;
		}
		$real_name = addslashes($_POST['surname']).', '.addslashes($_POST['first_name']);
		$title = addslashes($_POST['title']);
		if (strlen($title) <= 1) {
			$title = NULL;
		}
		$groups = (isset($_POST['groups']) && is_array($_POST['groups']))
			? array2csv($_POST['groups']) : NULL;
		$telephone = addslashes($_POST['telephone']);
		$address = addslashes($_POST['address']);
		$email = addslashes($_POST['email']);
		if (strlen($username) <= 5) {
			$content .= '<span class="errormessage">Your user name should be at least six characters.</span><br />';
			$error = true;
		}
		if ($pass != $pass_conf || $pass == "" || $pass_conf == "") {
			$content .= '<span class="errormessage">Your passwords do not match, or you did not fill in one or more of the password fields.</span><br />';
			$error = true;
		}
		if (strlen($pass) <= 7) {
			$content .= '<span class="errormessage">Your password must be at least eight characters.</span><br />';
			$error = true;
		}
		if (!preg_match('/^[a-z0-9_\-\.]+@[a-z0-9\-]+\.[a-z0-9\-\.]+$/i',$email)) {
			$content .= '<span class="errormessage">You did not enter a valid email address.</span><br />';
			$error = true;
		}
		if (strlen($telephone) <= 11 || !preg_match('/^[0-9\-]+\-[0-9]+\-[0-9]+$/',$telephone)) {
			$content .= '<span class="errormessage">Your telephone number should include the area code, and should be in the format 555-555-1234 or 1-555-555-1234.</span><br />';
			$error = true;
		}
		$check_user_query = 'SELECT * FROM ' . USER_TABLE . '
			WHERE username = \''.$username.'\'';
		$check_user_handle = $db->sql_query($check_user_query);
		if ($db->error[$check_user_handle] === 1) {
			$content .= '<span class="errormessage">Failed to check if your username is already taken.</span><br />';
		}
		$check_user_num_rows = $db->sql_num_rows($check_user_handle);
		if ($check_user_num_rows != 0) {
			$content .= '<span class="errormessage">Your username has already been taken. Please choose another.</span>';
			$error = true;
		}
		if ($error == true) {
			break;
		}
		$time = time();
		$create_user_query = 'INSERT INTO ' . USER_TABLE . "
			(`type`,`username`,`password`,`password_date`,`realname`,`title`,`groups`,
			`phone`,`email`,`address`) VALUES
			(2,'$username','".md5($pass)."',$time,'$real_name',
			'$title','$groups','$telephone','$email','$address')";
		$create_user = $db->sql_query($create_user_query);
		if ($db->error[$create_user] === 1) {
			$content .= 'Your account could not be created.<br />';
			break;
		}
		$content .= "Thank you, $real_name, your account has been created.";
		Log::addMessage('New user \''.$real_name.'\'');
		unset($username);
		unset($pass);
		unset($real_name);
		unset($title);
		unset($groups);
		unset($telephone);
		unset($email);
		unset($address);
		break;
	default:
		break;
}
if ($_GET['action'] == 'delete') {

} // IF 'delete'

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

$content .= $tab_layout;
?>