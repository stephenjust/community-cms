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
	$return .= '<div id="admin_help_page"><h3>Creating a New Page</h3>';

	// Quick overview of what can be done
	$return .= '<div class="admin_help_explanation">Pages can help you to sort out
your content. Different types of pages can also display different types of content.
Having everything on one page can gat confusing, so sometimes, you may want to
create a page to remove some of the clutter on your site.</div>';

	// Quick instructions
	$return .= '<div class="admin_help_quick_instructions">
<h3>Quick Instructions</h3>
<ul>
<li>In the navigation menu of any administration page, click the \'Pages\'
link in the \'Pages\' category.</li>
<li>In the \'Create Page\' tab, first give your pate a title,
such as \'Community News\'.</li>
<li>Give your new page a unique Text ID that can be used to reference it.</li>
<li>Choose whether or not you would like the page\'s title to be displayed at the 
top of the page.</li>
<li>Choose whether or not you would like your page to appear in your site\'s menu.</li>
<li>Pick a type of page that you would like to use. A \'News\' page displays articles,
a \'Newsletter List\' lists newsletter entries by date, a \'Calendar\' page displays
a calendar, and a \'Contacts\' page lists users who have been configured to publicize
their contact details.</li>
<li>Click \'Submit\' and then using the arrows in the \'Manage Pages\' section of the
page, move the new page around on the menu to the desired position.</li>
</ul>
</div>';

	// In-depth instructions
	

	$return .= '</div>';

	// Back to table of contents	
	$return .= '<br /><a href="admin.php?module=help">Table of Contents</a><br />';
	return $return;
?>