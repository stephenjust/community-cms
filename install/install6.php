<?php
/**
 * Community CMS Installer
 *
 * @copyright Copyright (C) 2009-2010 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.install
 */

/**#@+
 * @ignore
 */
define('SECURITY',1);
/**#@-*/

$error = 0;

$content = '<h1>Step 6: Save Settings and Populate Database</h1>';
require_once(ROOT . 'config.php');
require_once(ROOT . 'functions/error.php');
include_once(ROOT . 'include.php');
initialize('install');
$content .= 'Loading database contents file... ';
$schema_file = './schema/content.sql';
@$handle = fopen($schema_file, "r");
if (!$handle) {
	$content .= 'Failed.<br />';
	return true;
}
$query = fread($handle, filesize($schema_file));
fclose($handle);
$db_prefix = $db->sql_escape_string($CONFIG['db_prefix']);
$sitename = $db->sql_escape_string($_POST['sitename']);
$user = $db->sql_escape_string($_POST['adm_user']);
$pass = $_POST['adm_pass'];
$pass_hash = md5($pass);
$email = $db->sql_escape_string($_POST['adm_email']);
$query = str_replace('<!-- $DB_PREFIX$ -->',$db_prefix,$query);
$query = str_replace('<!-- $SITE_NAME$ -->',$sitename,$query);
$query = str_replace('<!-- $ADMIN_USER$ -->',$user,$query);
$query = str_replace('<!-- $ADMIN_PWD$ -->',$pass_hash,$query);
$query = str_replace('<!-- $ADMIN_EMAIL$ -->',$email,$query);
$content .= 'DONE.<br />';
// Display schema for the curious user, or advanced user.
$content .= '<br />'."\n".'<textarea rows="20" style="width: 100%;">'.$query.'</textarea><br />'."\n";
$content .= 'Adding content to the database... ';
$query = explode(';;',$query);
$content .= count($query) . ' queries to execute... ';
for ($i = 0; $i < count($query); $i++) {
	$query_handle[$i] = $db->sql_query($query[$i]);
	if($db->error[$query_handle[$i]] === 1) {
		$content .= 'Query <tt>'.$query[$i].'</tt> failed to execute.<br />'."\n";
		$error = 1;
	}
} // FOR
if ($error == 0) {
	$content .= 'DONE.<br />';
}

// Create permissions
$content .= 'Creating permission records... ';
if (permission_list_refresh() !== false) {
	$content .= 'DONE.<br />';
} else {
	$content .= 'Failed.<br />';
	$error = 1;
}

if ($error == 0) {
	$content .= '<br /><br />Community CMS is now successfully installed. Please delete the
		<tt>install</tt> directory to complete the installation.<br /><br />';
	$content .= '<form method="get" action="../index.php"><input type="submit" value="Go to Site" /></form>';
} else {
	$content .= 'An error has occured.';
}
Log::addMessage('Installed Community CMS',LOG_LEVEL_INSTALL);

clean_up();
?>