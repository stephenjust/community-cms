<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2007-2010 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.main
 */

/**
 * @ignore
 */
if (@SECURITY != 1) {
	die ('You cannot access this page directly.');
}

/**
 * display_login_box - Generate and return content of login box area
 * @global db $db
 * @global object $acl
 * @return string
 */
function display_login_box() {
	global $db;
	global $acl;
	global $user;
	if (!$user->logged_in) {
		$template_loginbox = new template;
		$template_loginbox->load_file('login');
		$template_loginbox->login_username = '<input type="text" name="user" id="login_user" />';
		$template_loginbox->login_password = '<input type="password" name="passwd" id="login_password" />';
		$template_loginbox->login_button = '<input type="submit" value="Login!" id="login_button" />';
		$return = "<form method='post' action='index.php?".$_SERVER['QUERY_STRING']."&amp;login=1'>\n".$template_loginbox."</form>\n";
		unset($template_loginbox);
	} else {
		$return = $_SESSION['name']."<br />\n<a href='index.php?".$_SERVER['QUERY_STRING']."&amp;login=2'>Log Out</a><br />\n";
		$check_message_query = 'SELECT * FROM ' . MESSAGE_TABLE . '
			WHERE recipient = '.$_SESSION['userid'];
		$check_message_handle = $db->sql_query($check_message_query);
		if (!$check_message_handle) {
			$return .= 'Could not check messages.';
		} else {
			$check_message = $db->sql_num_rows($check_message_handle);
			$return .= '<a href="messages.php">'.$check_message." new messages</a><br />\n";
		}
		unset($check_message_handle);
		unset($check_message_query);
		$return .= '<a href="index.php?id=change_password">Change Password</a><br />'."\n";
		if ($acl->check_permission('admin_access')) {
			$return .= '<a href="admin.php">Admin</a>';
		}
	}
	return $return;
}
?>