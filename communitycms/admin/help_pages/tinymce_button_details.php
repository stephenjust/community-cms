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
	$return .= '<div id="admin_help_page"><h3>What do all of those buttons do?</h3>';

	// Quick overview of what can be done
	$return .= '<div class="admin_help_explanation">The TinyMCE text editor is
very powerful and can be confusing. It has many features that are not entirely
apparent when you first look at the editor. The first step to unlocking the full
potential of the editor is to know what each of the buttons in the toolbars do.
</div>';

	// Quick instructions
	$return .= '<div class="admin_help_quick_instructions">
<h3>The Editor</h3>
This is the TinyMCE text editor:<br />
<img src="admin/help_pages/images/tinymce.png" alt="TinyMCE Editor" />
</div>';

	$return .= '<div class="admin_help_quick_instructions">
<h3>The Buttons</h3>
<table style="border: 0px;">
<!-- 1st ROW -->
<tr>
<td><img src="admin/help_pages/images/tinymce_buttons/bold.png" alt="Bold" /></td>
<td>Make selected text <strong>bold</strong>.</td>
</tr>
<tr>
<td><img src="admin/help_pages/images/tinymce_buttons/italic.png" alt="Italic" /></td>
<td>Make selected text <em>italic</em>.</td>
</tr>
<tr>
<td><img src="admin/help_pages/images/tinymce_buttons/underline.png" alt="Underline" /></td>
<td>Make selected text <u>underlined</u>.</td>
</tr>
<tr>
<td><img src="admin/help_pages/images/tinymce_buttons/strikethrough.png" alt="Strikethrough" /></td>
<td><del>Strike</del> selected text.</td>
</tr>
<!-- 2nd ROW -->
<tr>
<td><img src="admin/help_pages/images/tinymce_buttons/list-bullet.png" alt="Bulleted List" /></td>
<td>Create a bulleted list (dropdown is list style).</td>
</tr>
<tr>
<td><img src="admin/help_pages/images/tinymce_buttons/list-ordered.png" alt="Ordered List" /></td>
<td>Create a numbered list (dropdown is list style).</td>
</tr>
<!-- 3rd ROW -->

<!-- 4th ROW -->

</table>
</div>';

	$return .= '</div>';

	// Back to table of contents	
	$return .= '<br /><a href="admin.php?module=help">Table of Contents</a><br />';
	return $return;
?>