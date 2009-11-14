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
if (!isset($_GET['create'])) {
	$_GET['create'] = NULL;
}
if ($_GET['create'] == 1) {
	$error = 0;
	if (isset($_POST['username']) && isset($_POST['pass']) && isset($_POST['pass_conf']) && isset($_POST['first_name']) && isset($_POST['surname']) && isset($_POST['telephone']) && isset($_POST['address']) && isset($_POST['email'])) {
		$content = NULL;
		$username = addslashes($_POST['username']);
		$pass = $_POST['pass'];
		$pass_conf = $_POST['pass_conf'];
		if (eregi(',',$_POST['surname'])) {
			$content .= 'You cannot have a comma in your surname.<br />';
			$error = 1;
		}
		if (eregi(',',$_POST['first_name'])) {
			$content .= 'You cannot have a comma in your first name.<br />';
			$error = 1;
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
			$content .= 'Your user name should be at least six characters.<br />';
			$error = 1;
		}
		if ($pass != $pass_conf || $pass == "" || $pass_conf == "") {
			$content .= 'Your passwords do not match, or you did not fill in one or more of the password fields.<br />';
			$error = 1;
		}
		if (strlen($pass) <= 7) {
			$content .= 'Your password must be at least eight characters.<br />';
			$error = 1;
		}
		if (!eregi('^[a-zA-Z0-9_\-\.]+@[a-zA-Z0-9\-]+\.[a-zA-Z0-9\-\.]+$',$email)) {
			$content .= 'You did not enter a valid email address.<br />';
			$error = 1;
		}
		if (strlen($telephone) <= 11 || !eregi('^[0-9\-]+\-[0-9]+\-[0-9]+$',$telephone)) {
			$content .= 'Your telephone number should include the area code, and should be in the format 555-555-1234 or 1-555-555-1234.<br />';
			$error = 1;
		}
		$check_user_query = 'SELECT * FROM ' . USER_TABLE . '
			WHERE username = \''.$username.'\'';
		$check_user_handle = $db->sql_query($check_user_query);
		if ($db->error[$check_user_handle] === 1) {
			$content .= 'Failed to check if your username is already taken.<br />';
		}
		$check_user_num_rows = $db->sql_num_rows($check_user_handle);
		if ($check_user_num_rows != 0) {
			$content .= 'Your username has already been taken. Please choose another.';
			$error = 1;
		}
		if ($error != 1) {
			$create_user_query = 'INSERT INTO ' . USER_TABLE . "
				(type,username,password,realname,title,groups,phone,email,
				address)
				VALUES (2,'$username','".md5($pass)."','$real_name',
				'$title','$groups','$telephone','$email','$address')";
			$create_user = $db->sql_query($create_user_query);
			if ($db->error[$create_user] === 1) {
				$content .= 'Your account could not be created.<br />';
			} else {
				$content .= "Thank you, $real_name, your account has been created. ".log_action('New user \''.$real_name.'\'');
			}
		}
	} else {
		$content = 'You did not enter some required information. Please fill in all fields.';
	}
} else {
	$tab_layout = new tabs;
	$form = new form;
	$form->set_target('admin.php?module=user_create&amp;create=1');
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
	$content .= $tab_layout;
}
?>