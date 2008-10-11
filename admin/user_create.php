<?php
	// Security Check
	if (@SECURITY != 1 || @ADMIN != 1) {
		die ('You cannot access this page directly.');
		}
	if($_GET['create'] == 1) {
		if(isset($_POST['username']) && isset($_POST['pass']) && isset($_POST['pass_conf']) && isset($_POST['first_name']) && isset($_POST['surname']) && isset($_POST['telephone']) && isset($_POST['address']) && isset($_POST['email'])) {
			$content = NULL;
			$username = addslashes($_POST['username']);
			$pass = $_POST['pass'];
			$pass_conf = $_POST['pass_conf'];
			if(eregi(',',$_POST['surname'])) {
				$content .= 'You cannot have a comma in your surname.<br />';
				$error = 1;
				}
			if(eregi(',',$_POST['first_name'])) {
				$content .= 'You cannot have a comma in your first name.<br />';
				$error = 1;
				}
			$real_name = addslashes($_POST['surname']).', '.addslashes($_POST['first_name']);
			$title = addslashes($_POST['title']);
			if(strlen($title) <= 1) {
				$title = NULL;
				}
			$telephone = addslashes($_POST['telephone']);
			$address = addslashes($_POST['address']);
			$email = addslashes($_POST['email']);
			$telephone_hide = $_POST['telephone_hide'];
			$address_hide = $_POST['address_hide'];
			$email_hide = $_POST['email_hide'];
			$hide = $_POST['hide'];
			$message = $_POST['message'];
			if(strlen($username) <= 5) {
				$content .= 'Your user name should be at least six characters.<br />';
				$error = 1;
				}
			if($pass != $pass_conf || $pass == "" || $pass_conf == "") {
				$content .= 'Your passwords do not match, or you did not fill in one or more of the password fields.<br />';
				$error = 1;
				}
			if(strlen($pass) <= 7) {
				$content .= 'Your password must be at least eight characters.<br />';
				$error = 1;
				}
			if(!eregi('^[a-zA-Z0-9_\-\.]+@[a-zA-Z0-9\-]+\.[a-zA-Z0-9\-\.]+$',$email)) {
				$content .= 'You did not enter a valid email address.<br />';
				$error = 1;
				}
			if(strlen($telephone) <= 11 || !eregi('^[0-9\-]+\-[0-9]+\-[0-9]+$',$telephone)) {
				$content .= 'Your telephone number should include the area code, and should be in the format 555-555-1234 or 1-555-555-1234.<br />';
				$error = 1;
				}
			if($telephone_hide == 'on') {
				$telephone_hide = 1;
				} else {
				$telephone_hide = 0;
				}
			if($address_hide == 'on') {
				$address_hide = 1;
				} else {
				$address_hide = 0;
				}
			if($email_hide == 'on') {
				$email_hide = 1;
				} else {
				$email_hide = 0;
				}
			if($hide == 'on') {
				$hide = 1;
				} else {
				$hide = 0;
				}
			if($message == 'on') {
				$message = 1;
				} else {
				$message = 0;
				}	
			$check_user_query = 'SELECT * FROM '.$CONFIG['db_prefix'].'users WHERE username = "'.$username.'"';
			$check_user_handle = $db->query($check_user_query);
			if(!$check_user_handle) {
				$content .= 'Failed to check if your username is already taken. '.$mysqli_error($db);
				}
			$check_user_num_rows = $check_user_handle->num_rows;
			echo $check_user_num_rows;
			if($check_user_num_rows != 0) {
				$content .= 'Your username has already been taken. Please choose another.';
				$error = 1;
				}
			if($error != 1) {
				$create_user_query = 'INSERT INTO '.$CONFIG['db_prefix']."users (type,username,password,realname,title,phone,email,address,phone_hide,email_hide,address_hide,hide,message) VALUES (2,'$username','".md5($password)."','$real_name','$title','$telephone','$email','$address',$telephone_hide,$email_hide,$address_hide,$hide,$message)";
				$create_user = $db->query($create_user_query);
				if(!$create_user) {
					$content .= 'Your account could not be created.';
					} else {
					$content .= "Thank you, $real_name, your account has been created.";
					}
				}
			} else {
			$content = 'You did not enter some required information. Please fill in all fields.';
			}
		} else {
		$content = '<h1>Create New User</h1>
<form method="POST" action="admin.php?module=user_create&create=1">
<table class="admintable">
<tr>
<td class="row1">User Name:</td><td class="row1"><input type="text" name="username" /> johndoe01</td>
</tr>
<tr>
<td class="row2">Password:</td><td class="row2"><input type="password" name="pass" /></td>
</tr>
<tr>
<td class="row1">Confirm Password:</td><td class="row1"><input type="password" name="pass_conf" /></td>
</tr>
<tr>
<td class="row2">First Name:</td><td class="row2"><input type="text" name="first_name" /> John</td>
</tr>
<tr>
<td class="row1">Surname:</td><td class="row1"><input type="text" name="surname" /> Doe</td>
</tr>
<tr>
<td class="row2">Title/Position:</td><td class="row2"><input type="text" name="title" /></td>
</tr>
<tr>
<td class="row1">Phone Number:</td><td class="row1"><input type="text" name="telephone" /><input type="checkbox" name="telephone_hide" checked /> 555-555-1234</td>
</tr>
<tr>
<td class="row2">Address:</td><td class="row2"><input type="text" name="address" /><input type="checkbox" name="address_hide" checked /> 15648 Candycane Lane</td>
</tr>
<tr>
<td class="row1">Email Address:</td><td class="row1"><input type="text" name="email" /><input type="checkbox" name="email_hide" checked /> johndoe@example.com</td>
</tr>
<tr>
<td colspan="2" class="row2">If you would not like your phone number, email, or address to be displayed publicly, please check the boxes above. However, if you would like to allow people to see all or some of this information, uncheck the boxes corresponding to the values that you would like to be visible. You must enter this information so that the website administrators may contact you if the need arises. To hide your contact entry completely, check the box below.</td>
</tr>
<tr>
<td class="row1">Hide on contacts page:</td><td class="row1"><input type="checkbox" name="hide" /></td>
</tr>
<tr>
<td class="row2">Allow recieving messages:</td><td class="row2"><input type="checkbox" name="message" /></td>
</tr>
<tr>
<td colspan="2" class="row1"><input type="submit" value="Submit" /></td>
</tr>
</table></form>';
		}
?>