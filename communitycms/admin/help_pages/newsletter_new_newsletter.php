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
	$return .= '<div id="admin_help_page"><h3>Creating a Newsletter</h3>';

	// Quick overview of what can be done
	$return .= '<div class="admin_help_explanation">Newsletters keep people informed
about upcoming events, and give reports on recent past events. The Community CMS
newsletter system can help you to share and distribute your newsletters to those
people that it needs to reach.</div>';

	// Quick instructions
	$return .= '<div class="admin_help_quick_instructions">
<h3>Quick Instructions</h3>
<ul>
<li>In the navigation menu of any administration page, click the \'Newsletters\'
link in the \'Newsletter\' category.</li>
<li>In the \'Create Newsletter\' tab, first give your newsletter a
descriptive label, such as \'Community Spring Newsletter\'.</li>
<li>Using the file browser below, select a file on the server to use as
a newsletter. You can also click the \'Upload File\' button to copy a file from
your computer to the web site.</li>
<li>If you uploaded a file in the previous step, click the \'Refresh List\' button
and select the file you uploaded from the file list.</li>
<li>Select a date for your newsletter. The date is used to sort newsletters on the
page that your site\'s visitors can see. Typically, this date should correspond to
the date that the newsletter was published.</li>
<li>Select a page to put your newsletter on. Only the pages that are configured to
display newsletters will be in the list. Click \'Submit\' when you are finished.</li>
</ul>
</div>';

	// In-depth instructions
	

	$return .= '</div>';

	// Back to table of contents	
	$return .= '<br /><a href="admin.php?module=help">Table of Contents</a><br />';
	return $return;
?>