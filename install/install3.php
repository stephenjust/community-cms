<?php
/**
 * Community CMS Installer
 *
 *
 * @copyright Copyright (C) 2007-2009 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.install
 */

/**#@+
 * @ignore
 */
define('SECURITY',1);
define('ROOT','../');
/**#@-*/

// Set config values
switch ($_POST['db_engine']) {
	default:
		die ('Invalid database engine.');
		break;
	case 'MySQL':
		$CONFIG['db_engine'] = 'mysqli';
		break;
	case 'PostgreSQL':
		$CONFIG['db_engine'] = 'postgresql';
		break;
}
$CONFIG['db_host'] = $_POST['db_host'];
$CONFIG['db_host_port'] = $_POST['db_port'];
$CONFIG['db_name'] = $_POST['db_name'];
$CONFIG['db_user'] = $_POST['db_user'];
$CONFIG['db_pass'] = $_POST['db_pass'];
$CONFIG['db_prefix'] = $_POST['db_pfix'];

include_once ('../functions/main.php');
include_once ('../includes/constants.php');
include_once ('../includes/db/db.php');
if (!$db->sql_connect()) {
	die('Failed to connect to the database.');
}

$content = '<h1>Step 3: Create/Update Tables</h1>'."\n";

$error = 0;

// Check if the database is being installed or updated.
$db_version_query = 'SELECT `db_version` FROM `'.CONFIG_TABLE.'`';
@$db_version_handle = $db->sql_query($db_version_query);
if ($db->error[$db_version_handle] === 1 && !get_config('db_version')) {
	// Install
	$content .= 'Loading table schema for '.$_POST['db_engine'].'... ';
	$schema_file = './schema/'.$_POST['db_engine'].'_tables.sql';
	@$handle = fopen($schema_file, "r");
	if (!$handle) {
		$content .= 'Failed.<br />';
		return true;
	}
	$query = fread($handle, filesize($schema_file));
	fclose($handle);
	$db_prefix = $db->sql_escape_string($CONFIG['db_prefix']);
	$query = str_replace('<!-- $DB_PREFIX$ -->',$db_prefix,$query);
	// Display schema for the curious user, or advanced user.
	$content .= '<br />'."\n".'<textarea cols="80" rows="20">'.$query.'</textarea><br />'."\n";
	$content .= 'DONE.<br />';
	$content .= 'Adding new tables to the database... ';
	$query = explode(';',$query);
	$content .= count($query) . ' queries to execute... ';
	for ($i = 0; $i < count($query); $i++) {
		$query_handle[$i] = $db->sql_query($query[$i]);
		if($db->error[$query_handle[$i]] === 1) {
			$content .= 'Query <tt>'.$db->query_text[$i].'</tt> failed to execute.<br />'."\n";
			$error = 1;
		}
	} // FOR
	if ($error == 0) {
		$content .= 'Success. ';
		$content .= '<form method="post" action="index.php?page=4">'."\n";
		$content .= '<input type="hidden" name="engine" value="'.$CONFIG['db_engine'].'" />'."\n";
		$content .= '<input type="hidden" name="host" value="'.$CONFIG['db_host'].'" />'."\n";
		$content .= '<input type="hidden" name="port" value="'.$CONFIG['db_host_port'].'" />'."\n";
		$content .= '<input type="hidden" name="name" value="'.$CONFIG['db_name'].'" />'."\n";
		$content .= '<input type="hidden" name="user" value="'.$CONFIG['db_user'].'" />'."\n";
		$content .= '<input type="hidden" name="pass" value="'.$CONFIG['db_pass'].'" />'."\n";
		$content .= '<input type="hidden" name="prefix" value="'.$CONFIG['db_prefix'].'" />'."\n";
		$content .= '<input type="submit" value="Next" /></form>';
	} else {
		$content .= 'Failed.';
		return true;
	}

} else {
// ----------------------------------------------------------------------------
	// Update
	$config_file = '../config.php';
	$content .= 'Loading configuration file for updating... ';
	$handle = fopen($config_file, "w");
	if(!$handle) {
		$content .= 'Failed.<br />';
		return true;
	} else {
		$content .= 'Success.<br />'."\n";
	}
	// ----------------------------------------------------------------------------
	$config = '<?php
// Security Check
if (@SECURITY != 1) {
	die (\'You cannot access this page directly.\');
}
// Turn of \'register_globals\'
ini_set(\'register_globals\',0);
$CONFIG[\'SYS_PATH\'] = \'Unused\'; // Path to Community CMS on server
$CONFIG[\'db_engine\'] = \''.$CONFIG['db_engine'].'\'; // Database Engine
$CONFIG[\'db_host\'] = \''.$CONFIG['db_host'].'\'; // Database server host (usually localhost)
$CONFIG[\'db_host_port\'] = '.$CONFIG['db_host_port'].'; // Database server port (default 3306 for mysqli)
$CONFIG[\'db_user\'] = \''.$CONFIG['db_user'].'\'; // Database user
$CONFIG[\'db_pass\'] = \''.$CONFIG['db_pass'].'\'; // Database password
$CONFIG[\'db_name\'] = \''.$CONFIG['db_name'].'\'; // Database
$CONFIG[\'db_prefix\'] = \''.$CONFIG['db_prefix'].'\'; // Database table prefix

// Set the value below to \'1\' to disable Community CMS
$CONFIG[\'disabled\'] = 0;
?>';
	// ----------------------------------------------------------------------------
	$content .= 'Writing to configuration file... ';
	$config_write = fwrite($handle,$config);
	if (!$config_write) {
		$content .= 'Failed to write to config.php. Is it writeable?';
	} else {
		$content .= 'Success.<br />';
		$content .= 'Starting the updater.<br />';
		$content .= include('./update.php');
	}
	fclose($handle);
}
?>