<?php
/**
 * Community CMS Installer
 *
 * @copyright Copyright (C) 2008-2010 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.install
 */

/**
 * @ignore
 */
define('SECURITY',1);
include('../config.php');

// Try to get configuration from config.php
$user = (isset($CONFIG['db_user'])) ? $CONFIG['db_user'] : NULL;
$pass = (isset($CONFIG['db_pass'])) ? $CONFIG['db_pass'] : NULL;
$name = (isset($CONFIG['db_name'])) ? $CONFIG['db_name'] : NULL;
$pfix = (isset($CONFIG['db_prefix'])) ? $CONFIG['db_prefix'] : 'comcms_'.rand(1000,9999).'_';
$host = (isset($CONFIG['db_host'])) ? $CONFIG['db_host'] : 'localhost';
// JavaScript will take care of the initial value, so leave blank
$port = (isset($CONFIG['db_host_port'])) ? $CONFIG['db_host_port'] : NULL;

$content = '<h1>Step 2: Configure Database</h1>'."\n";
$content .= '<form method="post" action="index.php?page=3">'."\n";
$content .= '<table id="db_settings">'."\n";
// ----------------------------------------------------------------------------
$content .= '<tr><td>Database Engine</td><td><select name="db_engine" id="db_engine" onChange="setDefaultPort();">';
// Check for DB types
// MySQLi
if (function_exists('mysqli_connect')) {
	$content .= '<option name="mysqli">MySQL</option>';
}
// PostgreSQL
if (function_exists('pg_connect')) {
	$content .= '<option name="pgsql">PostgreSQL</option>';
}
$content .= '</select></td></tr>';
$content .= '<tr><td>Database Server</td><td><input type="text" name="db_host" id="db_host" value="'.$host.'" /></td></tr>';
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