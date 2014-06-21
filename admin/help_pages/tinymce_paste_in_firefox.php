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

    $return .= '<a href="admin.php?module=help&page=tinymce_using_tinymce">Using the TinyMCE Editor</a>';

	// Page header
	$return .= '<div id="admin_help_page"><h3>Why Can\'t I Paste My Content in Firefox?</h3>';

	// Quick overview of what can be done
	$return .= '<div class="admin_help_explanation">Newer versions of Mozilla
Firefox contain a security feature that prevents you from copying or pasting
content into scripted objects, such as TinyMCE. There are several ways to get
around this security measure, but some ways are more complicated than others.
</div>';

	// Quick instructions
	$return .= '<div class="admin_help_quick_instructions">
<h3>Option 1:</h3>
<ol>
<li>In the TinyMCE editor, press the \'Paste Text\' button in the second
toolbar. <img src="./admin/help_pages/images/tinymce_paste_text.png" alt="Paste Text"></li>
<li>Paste your content as plain text into the pop-up window that is
created.</li>
</ol>
</div>';

	$return .= '<div class="admin_help_quick_instructions">
<h3>Option 2:</h3>
<ol>
<li>Try using Ctrl+C or Ctrl+V instead of using the right-click menu. If that still does
not work, proceed to option 3.</li>
</ol>
</div>';

	$return .= '<div class="admin_help_quick_instructions">
<h3>Option 3:</h3>
<ol>
<li>In your Firefox profile directory (~/.mozilla/firefox/&lt;PROFILE&gt;/ on Unix-based
systems), find or create a file called \'user.js\'.</li>
<li>Paste the following code anywhere into the file:<br />
<tt>user_pref("capability.policy.allowclipboard.Clipboard.cutcopy", "allAccess");<br />
user_pref("capability.policy.allowclipboard.Clipboard.paste", "allAccess");<br />
user_pref("capability.policy.policynames", "allowclipboard");<br />
user_pref("capability.policy.allowclipboard.sites",<br />
"http://www.example.com/ http://www.example2.com/");</tt></li>
<li>Replace the example.com and example2.com entries with addresses of sites that you
would like to enable copy and paste, such as the one you are on now.</li>
<li>Restart your browser to allow the changes to take effect.</li>
<li>You must use Ctrl+C to copy, and Ctrl+V to paste your content.</li>
</ol>
</div>';

	// In-depth instructions

	$return .= '</div>';

	// Back to table of contents	
	$return .= '<br /><a href="admin.php?module=help">Table of Contents</a><br />';
	return $return;
?>