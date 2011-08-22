<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2010 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.main
 */

/**
 * @ignore
 */
if (!defined('SECURITY')) {
	exit;
}

global $db;
global $user;
$content = NULL;

if (!isset($_GET['action'])) {
	$_GET['action'] = NULL;
}

switch (Page::$id) {
	case 'change_password':
		Page::$title = 'Change Password';
		if ($_GET['action'] == 'save') {
			if (!isset($_POST['cp_user']) || !isset($_POST['cp_oldpass']) ||
					!isset($_POST['cp_newpass']) || !isset($_POST['cp_confpass'])) {
				$content .= '<strong>You failed to fill in one or more fields.</strong>';
				break;
			}
			if ($_POST['cp_newpass'] != $_POST['cp_confpass']) {
				$content .= '<strong>Your new password does not match.</strong>';
				break;
			}
			if ($_POST['cp_newpass'] == $_POST['cp_oldpass']) {
				$content .= '<strong>Your password did not change.</strong>';
				break;
			}
			$check_oldpass_query = 'SELECT `id` FROM `'.USER_TABLE.'`
				WHERE `username` = \''.addslashes($_POST['cp_user']).'\'
				AND `password` = \''.md5($_POST['cp_oldpass']).'\'';
			$check_oldpass_handle = $db->sql_query($check_oldpass_query);
			if ($db->error[$check_oldpass_handle] === 1) {
				$content .= '<strong>There was an error checking your credentials.</strong>';
				break;
			}
			if ($db->sql_num_rows($check_oldpass_handle) != 1) {
				$content .= '<strong>Your username and password combination is invalid.</strong>';
				break;
			}
			$check_oldpass = $db->sql_fetch_assoc($check_oldpass_handle);
			$change_pw_query = 'UPDATE `'.USER_TABLE.'`
				SET `password` = \''.md5($_POST['cp_newpass']).'\',
				`password_date` = '.time().' WHERE `id` = '.$check_oldpass['id'];
			$change_pw_handle = $db->sql_query($change_pw_query);
			if ($db->error[$change_pw_handle] === 1) {
				$content .= '<strong>Failed to change your password.</strong>';
				break;
			}
			$content .= '<strong>Changed your password. You may now log in.</strong>';
		} else {
			if (isset($_SESSION['expired']) && $_SESSION['expired'] == true) {
				Page::$notification .= 'You must change your password because it has expired.';
			}
			$user->logout();
			$content .= '<h1>Change Password</h1>'."\n";
			$content .= '<form method="post" action="?id=change_password&amp;action=save">'."\n";
			$content .= '<table style="border: 0px;">';
			$content .= '<tr><td>User Name:</td><td><input type="text" name="cp_user" /></td></tr>';
			$content .= '<tr><td>Old Password:</td><td><input type="password" name="cp_oldpass" /></td></tr>';
			$content .= '<tr><td>New Password:</td><td><input type="password" name="cp_newpass" /></td></tr>';
			$content .= '<tr><td>New Password (Confirm):</td><td><input type="password" name="cp_confpass" /></td></tr>';
			$content .= '<tr><td></td><td><input type="submit" value="Change Password" />&nbsp;
				<a href="index.php">Cancel</a></td></tr>';
			$content .= '</table></form>'."\n";
		}
		break;
	default:
		break;
}

return $content;
?>
