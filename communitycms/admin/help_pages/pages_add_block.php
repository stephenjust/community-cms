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
	$return .= '<div id="admin_help_page"><h3>Adding Blocks to a Page</h3>';

	// Quick overview of what can be done
	$return .= '<div class="admin_help_explanation">By putting blocks on your page, you
can customize your pages. Blocks allow you to display multiple types of content on one
page, so you can miz and match your pages.</div>';

	// Quick instructions
	$return .= '<div class="admin_help_quick_instructions">
<h3>Quick Instructions</h3>
<ul>
<li>In the navigation menu of any administration page, click the \'Pages\'
link in the \'Pages\' category.</li>
<li>In the \'Manage Pages\' tab, click the edit button next to the page
you would like to add a block to.</li>
<li>In the \'Edit Page\' tab, list fill the \'Blocks\' fields with the IDs of the
blocks that you want to display. Block IDs can be found on the \'Block Manager\' page.</li>
<li>Make sure that the lists of Block IDs are comma separated, with no spaces, and no
comma at the end of the list.</li>
<li>Click \'Submit\'. Check the page you edited to make sure that the blocks are being
displayed correctly.</li>
</ul>
</div>';

	// In-depth instructions
	

	$return .= '</div>';

	// Back to table of contents	
	$return .= '<br /><a href="admin.php?module=help">Table of Contents</a><br />';
	return $return;
?>