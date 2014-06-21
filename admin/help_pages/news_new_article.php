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
	$return .= '<div id="admin_help_page"><h3>Create New Article</h3>';

	// Quick overview of what can be done
	$return .= '<div class="admin_help_explanation">News articles can be 
used for many different purposes. You can use several news articles on one
page to create a list of time-ordered events, with the newest article at
the top of the page, or you could use one news article on a page with no
visible date to create a simple page of text. Using the built-in TinyMCE
rich text editor, you can make your pages fun and creative, and you don\'t
even need to know any code. This guide will help you to create functional
news articles that fill their desired purpose.</div>';

	// Quick instructions
	$return .= '<div class="admin_help_quick_instructions">
<h3>Quick Instructions</h3>
<ul>
<li>In the navigation menu of any administration page, click the \'News Articles\'
link in the \'Content\' category, and switch to the \'Create Article\' tab.</li>
<li>Choose an appropriate title for your new article or page. This will be
displayed at the top of the article. It can contain any character.</li>
<li>Using the TinyMCE Editor, give your article some content.</li>
<li>Next, choose a page to put this article on. Only pages that are configured
to display news will be available as choices.</li>
<li>You can assign an image to your news articles. This will be displayed with
the article. Choose one if desired. Use the file manager to upload new images.
Save them in the \'news_icons\' directory.</li>
<li>If you would like, you can show the date on your news article, or you could
show an abbreviated version of the date, or even show no date at all. The
latter option would be ideal if you are having a page with only one long
article.</li>
</ul>
</div>';

	// In-depth instructions
	

	$return .= '</div>';

	// Back to table of contents	
	$return .= '<br /><a href="admin.php?module=help">Table of Contents</a><br />';
	return $return;
?>