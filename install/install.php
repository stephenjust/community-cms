<?php
/**
 * Community CMS Installer
 *
 *
 * @copyright Copyright (C) 2007-2009 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.install
 */
$nav_bar = "<div align='center'><span style='color: #CCCC00;'>Check file
    permissions</span><hr />\n<span style='color: #CC0000;'>Configure settings
    </span><hr />\n<span style='color: #CC0000;'>Download/save config file</span></div>\n";
// TODO: Check for required libraries: GD / ImageMagick, MySQLi, MySQL, PHP5, etc.
$content = '<h1>Step 1: Check File Permissions</h1>'."\n";
$content .= '<table id="file_permissions"><tr><th>File/Folder</th><th>Status</th></tr>'."\n";
$error = 0;

// config.php
$content .= '<tr>'."\n".'<td>';
$content .= 'config.php';
$content .= '</td><td>';
if (!file_exists('../config.php')) {
	$content .= 'Does Not Exist';
	$error = 1;
} elseif (!is_writable('../config.php')) {
	$content .= 'Not Writable';
	$error = 1;
} else {
	$content .= 'Writable';
}
$content .= '</td></tr>'."\n";

// files/
$content .= '<tr>'."\n".'<td>';
$content .= 'files/';
$content .= '</td><td>';
if (!file_exists('../files')) {
	$content .= 'Does Not Exist';
	$error = 1;
} elseif (!is_writable('../files')) {
	$content .= 'Not Writable';
	$error = 1;
} else {
	$content .= 'Writable';
}
$content .= '</td></tr>'."\n";


// templates/
$content .= '<tr>'."\n".'<td>';
$content .= 'templates/';
$content .= '</td><td>';
if (!file_exists('../templates')) {
	$content .= 'Does Not Exist';
	$error = 1;
} elseif (!is_writable('../templates')) {
	$content .= 'Not Writable';
	$error = 1;
} else {
	$content .= 'Writable';
}
$content .= '</td></tr>'."\n";

$content .= '</table>'."\n";
if ($error == 0) {
	$content .= '<form method="POST" action="index.php?page=2"><input type="submit" value="Next" /></form>';
} else {
	$content .= '<br /><br />An error has occured. Please make sure that the files and folders listed above are writable.';
}
?>