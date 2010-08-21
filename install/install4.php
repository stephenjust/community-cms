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
define('ROOT','../');
define('SECURITY',1);
/**#@-*/

$error = 0;

$content = '<h1>Step 4: Save Database Configuration</h1>';
$config_file = '../config.php';
$content .= 'Loading configuration file... ';
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
$CONFIG[\'db_engine\'] = \''.$_POST['engine'].'\'; // Database Engine
$CONFIG[\'db_host\'] = \''.$_POST['host'].'\'; // Database server host (usually localhost)
$CONFIG[\'db_host_port\'] = '.$_POST['port'].'; // Database server port (default 3306 for mysqli)
$CONFIG[\'db_user\'] = \''.$_POST['user'].'\'; // Database user
$CONFIG[\'db_pass\'] = \''.$_POST['pass'].'\'; // Database password
$CONFIG[\'db_name\'] = \''.$_POST['name'].'\'; // Database
$CONFIG[\'db_prefix\'] = \''.$_POST['prefix'].'\'; // Database table prefix

// Set the value below to \'1\' to disable Community CMS
$CONFIG[\'disabled\'] = 0;
?>';
// ----------------------------------------------------------------------------
$content .= 'Writing to configuration file... ';
$config_write = fwrite($handle,$config);
if (!$config_write) {
	$content .= 'Failed to write to config.php. Is it writeable?';
	return true;
} else {
	$content .= 'Success.<br />';
}
fclose($handle);

$content .= '<form method="post" action="index.php?page=5"><input type="submit" value="Next" /></form>';
?>