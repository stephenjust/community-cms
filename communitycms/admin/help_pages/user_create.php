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
	$return .= '<div id="admin_help_page"><h3>Creating a New User</h3>';

	// Quick overview of what can be done
	$return .= '<div class="admin_help_explanation">Community CMS supports having
several users with access to different parts of the administration back-end. You
can create a new users so that each person can have their own personalized access
to prevent those people who may not be as technically-literate from breaking things.</div>';

	// Quick instructions
	$return .= '<div class="admin_help_quick_instructions">
<h3>Quick Instructions</h3>
<ul>
<li>In the navigation menu of any administration page, click the \'Manage Users\'
link in the \'Users\' category.</li>
<li>Open the \'Create User\' tab.</li>
<li>Give the user a unique user name and fill in their personal information.</li>
<li>Assign the user to one or more groups. Hold <i>Control</i> on your keyboard
to select more than one group. The groups that the user is a member of determines
what they have access to. Administrators have full access.</li>
<li>Click \'Create User\'. If you entered all of the information correctly, a
new user will be created.</li>
</ul>
</div>';

	// In-depth instructions
	$return .= '<div class="admin_help_quick_instructions">
<h3>Important Notes</h3>
<ul>
<li>Your username must be at least 6 characters long.</li>
<li>Your password must be at least 8 characters long.</li>
<li>You must provide a valid e-mail address.</li>
<li>You must provide an address.</li>
<li>Telephone numbers must be in the format \'1-555-555-5555\' OR \'555-555-5555\'.</li>
</ul>
</div>';

	$return .= '</div>';

	// Back to table of contents	
	$return .= '<br /><a href="admin.php?module=help">Table of Contents</a><br />';
	return $return;
?>