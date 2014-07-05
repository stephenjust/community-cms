<form method="post" action="{$form_target}">
	<table style="border: 0px;">
		<tr>
			<td>User Name:</td><td><input type="text" name="cp_user" /></td>
		</tr>
		<tr>
			<td>Old Password:</td><td><input type="password" name="cp_oldpass" /></td>
		</tr>
		<tr>
			<td>New Password:</td><td><input type="password" name="cp_newpass" /></td>
		</tr>
		<tr>
			<td>New Password (Confirm):</td><td><input type="password" name="cp_confpass" /></td>
		</tr>
		<tr>
			<td></td>
			<td><input type="submit" value="Change Password" />&nbsp;<a href="index.php">Cancel</a></td>
		</tr>
	</table>
</form>