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

    $return .= '<a href="admin.php?module=help&amp;page=gallery_create">Creating a new Image Gallery</a><br />
		<a href="admin.php?module=help&amp;gallery_add_images">Adding Images to your Image Gallery</a>';
	// Page header
	$return .= '<div id="admin_help_page"><h3>Setting up Community CMS to Enable Image Galleries</h3>';

	// Quick overview of what can be done
	$return .= '<div class="admin_help_explanation">When you first install
		Community CMS, it is not configured to support image galleries. You
		can use the Gallery Settings Manager to configure your image galleries.
		This is a necessary step because in the future, there are plans for
		Community CMS to support more than one image gallery add-on.</div>';

	// Quick instructions
	$return .= '<div class="admin_help_quick_instructions">
<h3>Quick Instructions</h3>
<ul>
<li>In the navigation menu of any administration page, click the \'Gallery Settings\'
link in the \'News\' category.</li>
<li>At this time, SimpleViewer is the only supported gallery application. Select
it from the "Gallery Type" drop-down.</li>
<li>In the "Gallery Directory" box, enter the path to the gallery installation,
relative to the root of the Community CMS installation. In the case of SimpleViewer,
this is the path to the folder with <em>web/simpleviewer.swf</em> in it.</li>
<li>Click the "Save Configuration" button.</li>
</ul>
</div>';

	// In-depth instructions
	

	$return .= '</div>';

	// Back to table of contents	
	$return .= '<br /><a href="admin.php?module=help">Table of Contents</a><br />';
	return $return;
?>