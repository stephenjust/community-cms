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

//
// TODO: Add link to TinyMCE Editor Help page
//

	// Page header
	$return .= '<div id="admin_help_page"><h3>What is TinyMCE?</h3>';

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
<li>At the top of any TinyMCE-enabled field are the \'Button Toolbars\'.</li>
<li>The top toolbar contains formatting options such as Bold, Italic,
Underline, text alignment, font size, and preset styles.</li>
<li>The second toolbar lets you cut, copy or paste text, and lets you
insert links or images, and change font colours.</li>
<li>In the third row are your table tools, and you can also insert
video or view TinyMCE in Full Screen mode.</li>
<li>Advanced tools such as layers or formatting marks are in the bottom
toolbar.</li>
<li>The bar on the bottom of the editor shows you where your cursor is in the
heirarchy of HTML tags making up your document.</li>
</ul>
</div>';

	// In-depth instructions

	$return .= '</div>';

	// Back to table of contents	
	$return .= '<br /><a href="admin.php?module=help">Table of Contents</a><br />';
	return $return;
?>