<?php
/**
 * Community CMS Installer
 *
 * @copyright Copyright (C) 2008-2010 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.install
 */
// TODO: Check for versions: PHP, MySQL, PostgreSQL etc.
$content = '<h1>Step 1: Check Dependencies</h1>'."\n";
$content .= <<< END
<table class="container"><tr><td width="215px" align="center">
<table class="content"><tr><th>File/Folder</th><th>Status</th></tr>
END;
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
if (check_library('mysqli')) {
	$content .= '<span class="req_good">Found</span>';
	$db = 1;
} else {
	$content .= '<span class="req_bad">Not Found</span>';
}
$content .= '</td></tr>'."\n";

// PostgreSQL
$content .= '<tr><td>PostgreSQL</td><td>';
if (check_library('postgresql')) {
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
$content .= '<tr><td>GD Image Library</td><td>';
if (check_library('gd')) {
	$content .= '<span class="req_good">Found</span>';
} else {
	$content .= '<span class="req_false">Not Found</span>';
	$error = 1;
}
$content .= '</td></tr>'."\n";

// Check for PEAR
$content .= '<tr><td>PEAR Library</td><td>';
if (check_library('pear')) {
	$content .= '<span class="req_good">Found</span>';
} else {
	$content .= '<span class="req_bad">Not Found</span>';
	$error = 1;
}
$content .= '</td></tr>'."\n";

// Check for XMLReader Class
$content .= '<tr><td>XMLReader</td><td>';
if (check_library('xmlreader')) {
	$content .= '<span class="req_good">Found</span>';
} else {
	$content .= '<span class="req_bad">Not Found</span>';
	$error = 1;
}
$content .= '</td></tr>'."\n";

$content .= <<< END
</table>
</td><td>

<h1>Community CMS</h1>
<p>
Thank you for downloading Community CMS. Community CMS is alpha software, so
there will be some bugs. Please report any bugs you may find to our
<a href="http://sourceforge.net/tracker/?group_id=223968" target="_blank">bug
tracker</a>, powered by <a href="http://sourceforge.net/" target="_blank">
SourceForge.net</a>.
</p>

<p>
If you do not meet the requirements to the left, you will not be able to install
Community CMS. If you do meet these requirements, you may continue. Completing
the installation process will require you to have write-access to either a
MySQL or PostgreSQL database.
</p>

<h1>Security Issues</h1>
<p>
The developer of Community CMS is not a security expert. There may be security-
related vulnerabilities within the code of the content management system. If you
decide to use this software in a production environment (which is not advised),
make sure you monitor your server logs for suspicious activity and make backups
regularly.
</p>

<h1>Reporting Bugs</h1>
<p>
When reporting bugs in Community CMS, please provide a list of PHP modules that
you have enabled, and your PHP, web server, and database server versions. PHP's
version information can be retreived using the phpinfo() function in a PHP file.
Please use our <a href="http://sourceforge.net/tracker/?group_id=223968"
target="_blank">tracker</a> to report any bugs.
</p>

</td></tr></table>
END;
if ($error == 0) {
	$content .= '<form method="POST" action="index.php?page=2"><input type="submit" value="Next" /></form>';
} else {
	$content .= '<br /><br />An error has occured. Please make sure that the files and folders listed above are writable, and that the required libraries are available.';
}
?>