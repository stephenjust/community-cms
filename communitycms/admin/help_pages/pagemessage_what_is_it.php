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
$return .= '<div id="admin_help_page"><h3>What is a Page Message?</h3>';

// Quick overview of what can be done
$return .= '<div class="admin_help_explanation">Page messages allow you to add
static content to your pages. A page message appears at the top of a certain page.
Similar to text blocks, they allow you to add content to the top of a page.</div>';

// Quick instructions

// In-depth instructions


$return .= '</div>';

// Back to table of contents
$return .= '<br /><a href="admin.php?module=help">Table of Contents</a><br />';
return $return;
?>
