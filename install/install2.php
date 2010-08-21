<?php
/**
 * Community CMS Installer
 *
 *
 * @copyright Copyright (C) 2007-2009 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.install
 */

/**
 * @ignore
 */
define('SECURITY',1);
include('../config.php');
if (isset($CONFIG['db_user'])) {
	$user = $CONFIG['db_user'];
} else {
	$user = NULL;
}
if (isset($CONFIG['db_pass'])) {
	$pass = $CONFIG['db_pass'];
} else {
	$pass = NULL;
}
if (isset($CONFIG['db_name'])) {
	$name = $CONFIG['db_name'];
} else {
	$name = NULL;
}
if (isset($CONFIG['db_prefix'])) {
	$pfix = $CONFIG['db_prefix'];
} else {
	$pfix = 'comcms_'.rand(1000,9999).'_';
}
if (isset($CONFIG['db_host_port'])) {
	$port = $CONFIG['db_host_port'];
} else {
	$port = NULL;
}

$content = '<h1>Step 2: Configure the Database</h1>'."\n";
$content .= '<form method="post" action="index.php?page=3">'."\n";
$content .= '<table id="db_settings">'."\n";
// ----------------------------------------------------------------------------
$content .= '<tr><td>Database Engine</td><td><select name="db_engine" id="db_engine" onChange="setDefaultPort();">
	<option name="mysqli">MySQL</option><option name="pgsql">PostgreSQL</option></select></td></tr>';
$content .= '<tr><td>Database Server</td><td><input type="text" name="db_host" id="db_host" value="localhost" /></td></tr>';
$content .= '<tr><td>Database Server Port</td><td><input type="text" name="db_port" id="db_port" value="'.$port.'" /></td></tr>';
$content .= '<script type="text/javascript" language="JavaScript">setDefaultPort();</script>'."\n";
$content .= '<tr><td>Database Name</td><td><input type="text" name="db_name" id="db_name" value="'.$name.'" /></td></tr>'."\n";
$content .= '<tr><td>Database User</td><td><input type="text" name="db_user" id="db_user" value="'.$user.'" /></td></tr>'."\n";
$content .= '<tr><td>Database Password</td><td><input type="text" name="db_pass" id="db_pass" value="'.$pass.'" /></td></tr>'."\n";
$content .= '<tr><td>Database Table Prefix</td><td><input type="text" name="db_pfix" id="db_pfix" value="'.$pfix.'" /></td></tr>'."\n";
$content .= '<tr><td><input type="button" value="Test Settings" onClick="testSettings();" /></td><td><div id="db_error"></div></td></tr>'."\n";

// ----------------------------------------------------------------------------
$content .= '</table>'."\n";
$content .= '</form>'."\n";
?>