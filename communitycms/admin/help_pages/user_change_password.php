<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2007-2010 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.help
 */
	// Security Check
	if (@SECURITY != 1 || @ADMIN != 1) {
		header('HTTP/1.1 403 Forbidden');
		die ('<html>
<head>
<title>Forbidden</title>
</head>
<body>
You cannot access this page directly. Please view<br />
this help file through the administrative interface<br />
by clicking the question mark in the top right corner<br />
of any administration page.
</body>
</html>');
		}
	// Back to table of contents	
	$return = '<br /><a href="admin.php?module=help">Table of Contents</a><br />';

	// Page header
	$return .= '<div id="admin_help_page"><h3>Changing Your Password</h3>';

	// Quick overview of what can be done
	$return .= '<div class="admin_help_explanation">Changing your password often
is a good way to keep your login details confidential, and to keep the CMS secure.</div>';

	// Quick instructions
	$return .= '<div class="admin_help_quick_instructions">
<h3>Quick Instructions</h3>
<ul>
<li>In the navigation menu of any administration page, click the \'User List\'
link in the \'Users\' category.</li>
<li>Click the \'Edit\' button corresponding to the user you would like to change
the password for.</li>
<li>In the \'New Password\' and \'Confirm Password\' fields, enter a new password.</li>
<li>In the \'Old Password\' field, enter your old password.</li>
<li>Click the \'Edit User\' button when you are finished.</li>
<li>If the resulting screen displays \'Password changed\', you are done. If the
resulting screen displays \'Password not changed\', then you may have entered an
incorrect old password, or your new password does not match in both fields.</li>
</ul>
</div>';

	// Alternate Method
	$return .= '<div class="admin_help_quick_instructions">
<h3>Alternate Method</h3>
<ul>
<li>When you log in, a \'Change Password\' link will appear where the login box
was. Click this link to change your password without having admin privileges.</li>
</ul>
</div>';

	$return .= '</div>';

	// Back to table of contents	
	$return .= '<br /><a href="admin.php?module=help">Table of Contents</a><br />';
	return $return;
?>