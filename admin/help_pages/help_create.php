<?php
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
	$return .= '<div id="admin_help_page"><h3>Create Help Page</h3>';

	// Quick overview of what can be done
	$return .= '<div class="admin_help_explanation">The Community CMS help system
is a great place to start looking for help if you don\'t know what to do. Of
course, not everything can be covered, but if you know something that may be of
use to a less technically-literate person, why not write a help article?</div>';

	// Quick instructions
	$return .= '<div class="admin_help_quick_instructions">
<h3>Quick Instructions</h3>
<ul>
<li>Copy one of the existing files in the ./admin/help_pages/ directory of your
Community CMS installation. Rename this file to something logical, for example
\'calendar_create_entry.php\' for an article dealing with how to create a calendar
entry.</li>
<li>Use your favourite text editor to replace the contents of your new help file
with some relevant text.</li>
<li>Add your new help page to the help table of contents (located in table_of_contents.php).
Use some of the existing entries as an example.</li>
<li>Submit your help page to the Community CMS bug/patch tracker on SF.net.</li>
</ul>
</div>';

	// In-depth instructions
	

	$return .= '</div>';

	// Back to table of contents	
	$return .= '<br /><a href="admin.php?module=help">Table of Contents</a><br />';
	return $return;
?>