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
	$return .= '<div id="admin_help_page"><h3>Using the TinyMCE Editor</h3>';

	// Quick overview of what can be done
	$return .= '<div class="admin_help_explanation">TinyMCE is a cross-platform
HTML and JavaScript based WYSIWYG (What You See Is What You Get) text
editor. Using TinyMCE, you can easily edit your content and fomat
it to your liking without knowing any HTML at all. TinyMCE also gives
advanced users the freedom to manually edit any code it produces, making
TinyMCE suitable for everybody.</div>';

	// Quick instructions
	$return .= '<div class="admin_help_quick_instructions">
<h3>Quick Instructions</h3>
<ul>
<li>TinyMCE is used like any ordinary word processor.</li>
<li>You can type text, select what you\'ve typed, and click a button on one of
the toolbars to edit the selected text.</li>
<li>You can change the formatting options before you start typing.</li>
<li>You can add tables, images and media from the toolbars.</li>
<li>NOTE: Avoid copying and pasting from Microsft Word directly. Microsoft Word
uses some non-friendly characters that may display incorrectly in some browsers.
To prevent this from occuring, try copying the text from another program other
than Microsoft Word.</li>
</ul>
</div>';

	// In-depth instructions

	$return .= '</div>';

	// Back to table of contents
	$return .= '<br /><a href="admin.php?module=help">Table of Contents</a><br />';
	return $return;
?>
