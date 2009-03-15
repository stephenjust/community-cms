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
	$return = '<div id="admin_help_toc"><h3>Table of Contents</h3>';
	$return .= '<h4>News</h4>
<ol>
<li><a href="admin.php?module=help&page=news_new_article">Create New Article</a></li>
</ol>';
	$return .= '<h4>Newsletters</h4>
<ol>
<li><a href="admin.php?module=help&page=newsletter_new_newsletter">Creating a Newsletter</a></li>
</ol>';
	$return .= '<h4>Pages</h4>
<ol>
<li><a href="admin.php?module=help&page=pages_create_page">Creating a New Page</a></li>
<li><a href="admin.php?module=help&page=pages_add_block">Adding Blocks to a Page</a></li>
</ol>';
	$return .= '<h4>TinyMCE</h4>
<ol>
<li><a href="admin.php?module=help&page=tinymce_what_is_it">What is TinyMCE?</a></li>
<li><a href="admin.php?module=help&page=tinymce_paste_in_firefox">Pasting in Firefox</a></li>
</ol>';
	$return .= '<h4>Help</h4>
<ol>
<li><a href="admin.php?module=help&page=help_create">Creating a help page</a></li>
</ol>';
	$return .= '</div>';
	return $return;
?>