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
if(!isset($_GET['edit'])) {
	$_GET['edit'] = NULL;
}
if($_GET['edit'] != "") {
	$_GET['id'] = $_GET['edit'];
}
$current_data_query = 'SELECT * FROM ' . USER_TABLE . '
	WHERE id = '.(int)$_GET['id'].' LIMIT 1';
$current_data_handle = $db->sql_query($current_data_query);
if ($db->sql_num_rows($current_data_handle) == 0) {
	$content .= 'Unable to find the specified user in the database.<br />';
} else {
	$current_data = $db->sql_fetch_assoc($current_data_handle);
	if ($_GET['edit'] != "") {
		if ($_POST['edit_old_pass'] != "" && md5($_POST['edit_old_pass']) == $current_data['password']) {
			if ($_POST['edit_pass'] == $_POST['edit_pass_conf'] && strlen($_POST['edit_pass']) >= 8) {
				$change_password_query = 'UPDATE ' . USER_TABLE . '
					SET password = \''.md5($_POST['edit_pass']).'\' WHERE id = '.(int)$_GET['edit'];
				$change_password_handle = $db->sql_query($change_password_query);
				if ($db->error[$change_password_query] === 1) {
					$content .= 'Failed to change password.<br />';
				} else {
					$content .= 'Password changed.<br />';
				}
			} else {
				$content .= 'Password not changed.<br />';
			}
		} else {
			$content .= 'Password not changed.<br />';
		}
		$telephone = addslashes($_POST['telephone']);
		$email = addslashes($_POST['email']);
		$title = addslashes($_POST['title']);
		$telephone_hide = (int)$_POST['telephone_hide'];
		$address_hide = (int)$_POST['address_hide'];
		$email_hide = (int)$_POST['email_hide'];
		$groups = (isset($_POST['groups']) && is_array($_POST['groups']))
			? array2csv($_POST['groups']) : NULL;
		if (!isset($_POST['hide'])) {
			$hide = NULL;
		} else {
			$hide = (int)$_POST['hide'];
		}
		$error = 0;
		$message = (isset($_POST['message'])) ? $_POST['message'] : NULL;
		if (strlen($telephone) <= 11 || !eregi('^[0-9\-]+\-[0-9]+\-[0-9]+$',$telephone)) {
			$content .= 'Your telephone number should include the area code, and should be in the format 555-555-1234 or 1-555-555-1234.<br />';
			$error = 1;
		}
		if (!eregi('^[a-zA-Z0-9_\-\.]+@[a-zA-Z0-9\-]+\.[a-zA-Z0-9\-\.]+$',$email)) {
			$content .= 'You did not enter a valid email address.<br />';
			$error = 1;
		}
		$telephone_hide = checkbox($telephone_hide);
		$address_hide = checkbox($address_hide);
		$email_hide = checkbox($email_hide);
		$hide = checkbox($hide);
		$message = checkbox($message);
		if ($_POST['surname'] == '' || $_POST['first_name'] == '') {
			$realname = $_POST['surname'].$_POST['first_name'];
		} else {
			$realname = $_POST['surname'].', '.$_POST['first_name'];
		}
		if ($error == 0) {
			$edit_query = 'UPDATE ' . USER_TABLE . '
				SET realname=\''.$realname.'\', title=\''.$title.'\',
				groups=\''.$groups.'\', phone=\''.$telephone.'\',
				email=\''.$email.'\', address=\''.addslashes($_POST['address']).'\',
				address_hide='.$address_hide.', email_hide='.$email_hide.',
				phone_hide='.$telephone_hide.', message='.$message.',
				hide='.$hide.' WHERE id = '.(int)$_GET['edit'];
			$edit_handle = $db->sql_query($edit_query);
			if($db->error[$edit_handle] === 1) {
				$content .= 'Failed to update user information. ';
			} else {
				$content .= 'Successfully updated user information.';
			}
		}
	} else { // IF 'edit'

// ----------------------------------------------------------------------------

		$current_name = explode(', ',$current_data['realname']);
		if(!isset($current_name[1])) {
			$current_name[1] = NULL;
		}
		$telephone_hide = checkbox($current_data['phone_hide'],1);
		$address_hide = checkbox($current_data['address_hide'],1);
		$email_hide = checkbox($current_data['email_hide'],1);
		$hide = checkbox($current_data['hide'],1);
		$message = checkbox($current_data['message'],1);
		$tab_layout = new tabs;
		$form = new form;
		$form->set_target('admin.php?module=user_edit&amp;edit='.$_GET['id']);
		$form->set_method('post');
		$form->add_password('edit_pass','Password');
		$form->add_password('edit_pass_conf','Confirm Password');
		$form->add_password('edit_old_pass','Old Password');
		$form->add_text('If the above password fields are filled correctly,
			your password will be changed. Leave the password fields empty
			if you do not want to change your password.');
		$form->add_textbox('first_name','First Name',$current_name[1]);
		$form->add_textbox('surname','Surname',$current_name[0]);
		$form->add_textbox('title','Title/Position',$current_data['title']);
		$form->add_textbox('telephone','Phone Number',$current_data['phone']);
		$form->add_checkbox('telephone_hide','Hide Phone Number',$current_data['phone_hide']);
		$form->add_textbox('address','Address',$current_data['address']);
		$form->add_checkbox('address_hide','Hide Address',$current_data['address_hide']);
		$form->add_textbox('email','Email Address',$current_data['email']);
		$form->add_checkbox('email_hide','Hide Email Address',$current_data['email_hide']);
		$form->add_text('If you would not like your phone number, email, or
			address to be displayed publicly, please check the boxes above.
			However, if you would like to allow people to see all or some of
			this information, uncheck the boxes corresponding to the values
			that you would like to be visible. You must enter this information
			so that the website administrators may contact you if the need
			arises. To hide your contact entry completely, check the box
			below.');
		$form->add_checkbox('hide','Hide on Contacts Page',$current_data['hide']);
		$form->add_checkbox('message','Allow Recieving Messages',$current_data['message']);
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
			$form->add_multiselect('groups','Groups',$group_list_id,$group_list_name,$current_data['groups']);
		}
		$form->add_submit('submit','Edit User');
		$tab_content['edit'] = $form;
		$tab_layout->add_tab('Edit User',$tab_content['edit']);
		$content .= $tab_layout;
	}
}
?>