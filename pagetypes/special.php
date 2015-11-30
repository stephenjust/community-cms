<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2010 Stephen Just
 * @author    stephenjust@users.sourceforge.net
 * @package   CommunityCMS.main
 */

namespace CommunityCMS;

/**
 * @ignore
 */
if (!defined('SECURITY')) {
    exit;
}

$content = null;

switch (Page::$id) {
case 'change_password':
    Page::$title = 'Change Password';
    if (FormUtil::get('action') == 'save') {
        if (FormUtil::post('cp_newpass') != FormUtil::post('cp_confpass')) {
            $content .= '<strong>Your new password does not match.</strong>';
            break;
        }
        if (FormUtil::post('cp_newpass') == FormUtil::post('cp_oldpass')) {
            $content .= '<strong>Your password did not change.</strong>';
            break;
        }
        $user = new User(FormUtil::post('cp_user'));
        if (!$user->isPassword(FormUtil::post('cp_oldpass'))) {
            $content .= '<strong>Your username and password combination is invalid.</strong>';
            break;
        }
        $user->setPassword(FormUtil::post('cp_oldpass'), FormUtil::post('cp_newpass'));
        $content .= '<strong>Changed your password. You may now log in.</strong>';
    } else {
        if (isset($_SESSION['expired']) && $_SESSION['expired'] == true) {
            Page::$notification .= 'You must change your password because it has expired.';
        }
        UserSession::get()->logout();
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
