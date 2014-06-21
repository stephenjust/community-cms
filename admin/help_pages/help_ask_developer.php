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
	$return .= '<div id="admin_help_page"><h3>Ask a Developer for Help</h3>';

	// Quick overview of what can be done
	$return .= '<div class="admin_help_explanation">Sometimes, you may require
assistance with something that is not mentioned in a help file, or you would like
a new feature to be implemented on your site. You can send the developers of
Community CMS a message from right inside the CMS.</div>';

	// Quick instructions
	$return .= '<div class="admin_help_quick_instructions">
<h3>Quick Instructions</h3>
<ul>
<li>To ask one of the developers of Community CMS for help, click the icon in the
top-right corner of any administration page that depicts a question mark on an
envelope.</li>
<li>Select a category for your help request or comment. If there is no entry in the
list relating to your issue, select \'Other Comment\'.</li>
<li>Type a message that you would like the developers to see.</li>
<li>Click \'Send Message\'</li>
</ul>
</div>';

	// In-depth instructions
	$return .= '<div class="admin_help_quick_instructions">
<h3>Notes</h3>
<ul>
<li>You will not be able to send a help request if you have not set an administrator
email address. To do this, click \'Configuration\' in the \'Main\' category of 
the navigation menu on any administration page, and put your email in the \'Admin
E-Mail Address\' field.</li>
</ul>
</div>';

	$return .= '</div>';

	// Back to table of contents
	$return .= '<br /><a href="admin.php?module=help">Table of Contents</a><br />';
	return $return;
?>