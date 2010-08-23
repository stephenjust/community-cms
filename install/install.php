<?php
/**
 * Community CMS Installer
 *
 *
 * @copyright Copyright (C) 2007-2009 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.install
 */
// TODO: Check for versions: PHP, MySQL, PostgreSQL etc.
$content = '<h1>Step 1: Check File Permissions</h1>'."\n";
$content .= '<table id="file_permissions"><tr><th>File/Folder</th><th>Status</th></tr>'."\n";
$error = 0;

// config.php
$content .= '<tr>'."\n".'<td>';
$content .= 'config.php';
$content .= '</td><td>';
touch('../config.php');
if (file_exists('../config.php')) {
	if (is_writable('../config.php')) {
		$content .= '<span class="req_good">Writable</span>';
	} else {
		$content .= '<span class="req_bad">Not Writable</span>';
	}
} else {
	$content .= '<span class="req_bad">Does Not Exist</span>';
}
$content .= '</td></tr>'."\n";

// files/
$content .= '<tr>'."\n".'<td>';
$content .= 'files/';
$content .= '</td><td>';
if (!file_exists('../files')) {
	$content .= '<span class="req_bad">Does Not Exist</span>';
	$error = 1;
} elseif (!is_writable('../files')) {
	$content .= '<span class="req_bad">Not Writable</span>';
	$error = 1;
} else {
	$content .= '<span class="req_good">Writable</span>';
}
$content .= '</td></tr>'."\n";


// templates/
$content .= '<tr>'."\n".'<td>';
$content .= 'templates/';
$content .= '</td><td>';
if (!file_exists('../templates')) {
	$content .= '<span class="req_bad">Does Not Exist</span>';
	$error = 1;
} elseif (!is_writable('../templates')) {
	$content .= '<span class="req_bad">Not Writable</span>';
	$error = 1;
} else {
	$content .= '<span class="req_good">Writable</span>';
}
$content .= '</td></tr>'."\n";

// Separator
$content .= '<tr><td colspan="2">&nbsp;</td></tr>'."\n";

// Config Header
$content .= '<tr><th>Configuration</th><th>Status</th></tr>'."\n";

// Register globals
$content .= '<tr><td>register_globals</td><td>';
if (ini_get('register_globals')) {
	$content .= '<span class="req_bad">Enabled</span>';
	$error = 1;
} else {
	$content .= '<span class="req_good">Disabled</span>';
}

// Separator
$content .= '<tr><td colspan="2">&nbsp;</td></tr>'."\n";

// Databases Header
$content .= '<tr><th>Database</th><th>Status</th></tr>'."\n";
$content .= '<tr><td colspan="2"><div style="font-size: small; text-align: center;">Requires one of the following:</div></td></tr>'."\n";
$db = 0;

// MySQLi
$content .= '<tr><td>MySQLi</td><td>';
if (function_exists('mysqli_connect')) {
	$content .= '<span class="req_good">Found</span>';
	$db = 1;
} else {
	$content .= '<span class="req_bad">Not Found</span>';
}
$content .= '</td></tr>'."\n";

// PostgreSQL
$content .= '<tr><td>PostgreSQL</td><td>';
if (function_exists('pg_connect')) {
	$content .= '<span class="req_good">Found</span>';
	$db = 1;
} else {
	$content .= '<span class="req_bad">Not Found</span>';
}
$content .= '</td></tr>'."\n";

if ($db == 0) {
	$error = 1;
}

// Separator
$content .= '<tr><td colspan="2">&nbsp;</td></tr>'."\n";

// Libraries Header
$content .= '<tr><th>Library</th><th>Status</th></tr>'."\n";

// Check for GD
$gd_found = function_exists('imageCreateTrueColor');
$content .= '<tr><td>GD Image Library</td><td>';
if ($gd_found) {
	$content .= '<span class="req_good">Found</span>';
} else {
	$content .= '<span class="req_false">Not Found</span>';
	$error = 1;
}

//// Check for PEAR
//$pear_found = @ include('PEAR.php');
//$content .= '<tr><td>PEAR Library</td><td>';
//if ($pear_found) {
//	$content .= '<span class="req_good">Found</span>';
//} else {
//	$content .= '<span class="req_bad">Not Found</span>';
//	$error = 1;
//}
//$content .= '</td></tr>'."\n";

// Check for XMLReader Class
$xmlreader_found = @ new xmlreader;
$content .= '<tr><td>XMLReader</td><td>';
if (is_object($xmlreader_found)) {
	$content .= '<span class="req_good">Found</span>';
} else {
	$content .= '<span class="req_bad">Not Found</span>';
	$error = 1;
}

$content .= '</table>'."\n";
if ($error == 0) {
	$content .= '<form method="POST" action="index.php?page=2"><input type="submit" value="Next" /></form>';
} else {
	$content .= '<br /><br />An error has occured. Please make sure that the files and folders listed above are writable, and that the required libraries are available.';
}
?>