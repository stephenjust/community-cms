<?php
	// Security Check
	if (@SECURITY != 1 || @ADMIN != 1) {
		die ('You cannot access this page directly.');
		}
		if($_GET['edit'] != "") {
			$_GET['id'] = $_GET['edit'];
			}
		$current_data_query = 'SELECT * FROM '.$CONFIG['db_prefix'].'users WHERE id = '.$_GET['id'].' LIMIT 1';
		$current_data_handle = $db->query($current_data_query);
		$current_data = $current_data_handle->fetch_assoc();
		if($current_data_handle->num_rows == 0) {
			$content = 'Unable to find the specified user in the database.';
			} else {
			$content = NULL;
			if($_GET['edit'] != "") {
				if($_POST['edit_old_pass'] != "" && md5($_POST['edit_old_pass']) == $current_data['password']) {
					if($_POST['edit_pass'] == $_POST['edit_pass_conf'] && strlen($_POST['edit_pass']) >= 8) {
						$change_password_query = 'UPDATE '.$CONFIG['db_prefix'].'users SET password = "'.md5($_POST['edit_pass']).'" WHERE id = '.$_GET['edit'].' LIMIT 1';
						if(!$db->query($change_password_query)) {
							$content .= 'Failed to change password.<br />'.mysqli_error($db).'<br />';
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
				$telephone_hide = $_POST['telephone_hide'];
				$address_hide = $_POST['address_hide'];
				$email_hide = $_POST['email_hide'];
				$hide = $_POST['hide'];
				$message = $_POST['message'];
				if(strlen($telephone) <= 11 || !eregi('^[0-9\-]+\-[0-9]+\-[0-9]+$',$telephone)) {
					$content .= 'Your telephone number should include the area code, and should be in the format 555-555-1234 or 1-555-555-1234.<br />';
					$error = 1;
					}
				if(!eregi('^[a-zA-Z0-9_\-\.]+@[a-zA-Z0-9\-]+\.[a-zA-Z0-9\-\.]+$',$email)) {
					$content .= 'You did not enter a valid email address.<br />';
					$error = 1;
					}
				$telephone_hide = checkbox($telephone_hide);
				$address_hide = checkbox($address_hide);
				$email_hide = checkbox($email_hide);
				$hide = checkbox($hide);
				$message = checkbox($message);
				if($_POST['surname'] == '' || $_POST['first_name'] == '') {
					$realname = $_POST['surname'].$_POST['first_name'];
					} else {
					$realname = $_POST['surname'].', '.$_POST['first_name'];
					}
				if($error == 0) {
					$edit_query = 'UPDATE '.$CONFIG['db_prefix'].'users SET realname="'.$realname.'", 
title="'.$title.'", phone="'.$telephone.'", email="'.$email.'", address="'.addslashes($_POST['address']).'", 
address_hide='.$address_hide.', email_hide='.$email_hide.', phone_hide='.$telephone_hide.', 
message='.$message.', hide='.$hide.' WHERE id = '.$_GET['edit'].' LIMIT 1';
					$edit_handle = $db->query($edit_query);
					if(!$edit_handle) {
						$content .= 'Failed to update user information. '.mysqli_error($db);
						} else {
						$content .= 'Successfully updated user information.';
						}
					}
				} else { // IF 'edit'

// ----------------------------------------------------------------------------

				$current_name = explode(', ',$current_data['realname']);
				$telephone_hide = checkbox($current_data['phone_hide'],1);
				$address_hide = checkbox($current_data['address_hide'],1);
				$email_hide = checkbox($current_data['email_hide'],1);
				$hide = checkbox($current_data['hide'],1);
				$message = checkbox($current_data['message'],1);
				$content = '<h1>Modify User</h1>
<form method="POST" action="admin.php?module=user_edit&edit='.$_GET['id'].'">
<table class="admintable">
<tr>
	<td class="row1">
		New Password:
		</td>
	<td class="row1">
		<input type="password" name="edit_pass" />
		</td>
	<td rowspan="3" class="row1">
		If these password fields are filled correctly, your password will be changed.
		Leave the password fields empty if you do not want to change your password.
		</td>
	</tr>
<tr>
	<td class="row2">
		Confirm Password:
		</td>
	<td class="row2">
		<input type="password" name="edit_pass_conf" />
		</td>
	</tr>
<tr>
	<td class="row1">
		Old Password:
		</td>
	<td class="row1">
		<input type="password" name="edit_old_pass" />
		</td>
	</tr>
<tr>
	<td class="row2">
		First Name:
		</td>
	<td class="row2" colspan="2">
		<input type="text" name="first_name" value="'.$current_name[1].'" />
		</td>
	</tr>
<tr>
	<td class="row1">
		Surname:
		</td>
	<td class="row1" colspan="2">
		<input type="text" name="surname" value="'.$current_name[0].'" />
		</td>
	</tr>
<tr>
	<td class="row2">
		Title/Position:
		</td>
	<td class="row2" colspan="2">
		<input type="text" name="title" value="'.$current_data['title'].'" />
		</td>
	</tr>
<tr>
	<td class="row1">
		Phone Number:
		</td>
	<td class="row1" colspan="2">
		<input type="text" name="telephone" value="'.$current_data['phone'].'" />
		<input type="checkbox" name="telephone_hide" '.$telephone_hide.' />
		</td>
	</tr>
<tr>
	<td class="row2">
		Address:
		</td>
	<td class="row2" colspan="2">
		<input type="text" name="address" value="'.$current_data['address'].'" />
		<input type="checkbox" name="address_hide" '.$address_hide.' />
		</td>
	</tr>
<tr>
	<td class="row1">
		Email Address:
		</td>
	<td class="row1" colspan="2">
		<input type="text" name="email" value="'.$current_data['email'].'" />
		<input type="checkbox" name="email_hide" '.$email_hide.' />
		</td>
	</tr>
<tr>
	<td colspan="3" class="row2">
		If you would not like your phone number, email, or address to be displayed publicly,
		please check the boxes above. However, if you would like to allow people to see all
		or some of this information, uncheck the boxes corresponding to the values that you
		would like to be visible. You must enter this information so that the website
		administrators may contact you if the need arises. To hide your contact entry
		completely, check the box below.
		</td>
	</tr>
<tr>
	<td class="row1">
		Hide on contacts page:
		</td>
	<td class="row1" colspan="2">
		<input type="checkbox" name="hide" '.$hide.' />
		</td>
	</tr>
<tr>
	<td class="row2">
		Allow recieving messages:
		</td>
	<td class="row2" colspan="2">
		<input type="checkbox" name="message" '.$message.' />
		</td>
	</tr>
<tr>
	<td colspan="3" class="row1">
		<input type="submit" value="Submit" />
		</td>
	</tr>
</table>
</form>';
			}
		}
	?>