<?php
/**
 * Community CMS Installer
 *
 *
 * @copyright Copyright (C) 2007-2009 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.install
 */
define('ROOT','../');
define('SECURITY',1);
$error = 0;
$nav_bar = "<div align='center'><span style='color: #00CC00;'>Check file permissions</span><hr />\n<span style='color: #00CC00;'>Configure settings</span><hr />\n<span style='color: #CCCC00;'>Download/save config file</span></div>\n";
$content = "<h1>Installing...</h1>\n";
$CONFIG['db_prefix'] = $_POST['dbpfix'];
$CONFIG['db_engine'] = 'mysqli';
include('../include.php');
	$connect = mysql_connect($_POST['dbhost'],$_POST['dbuser'],$_POST['dbpass']);
	if (!$connect) {
		$content = 'One or more fields was filled out incorrectly. Please hit your browser\'s back button and correct the mistake. Could not connect to database.<br />';
		$error = 1;
	} else {
		// Try to open the database that is used by Community CMS.
		$select_db = mysql_select_db($_POST['dbname'],$connect);
		if (!$select_db) {
			$content = 'One or more fields was filled out incorrectly. Please hit your browser\'s back button and correct the mistake. Could not find database.<br />';
			$error = 1;
		}
	}
	if (!$connect) {
		$content = 'One or more fields was filled out incorrectly. Please hit your browser\'s back button and correct the mistake.<br />';
		$error = 1;
	} else {
		$handle = fopen('./schema/MySQL.sql', "r");
		if (!$handle) {
			$content .= 'Failed to read default database schema.<br />';
			$error = 1;
		}
		$query = fread($handle, filesize('./schema/MySQL.sql'));
		fclose($handle);
		$dbprefix = addslashes($_POST['dbpfix']);
		$sitename = addslashes($_POST['sitename']);
		$admin_user = addslashes(mysql_real_escape_string($_POST['admin_username'],$connect));
		$admin_pwd = addslashes(mysql_real_escape_string($_POST['admin_pwd'],$connect));
		$admin_pwd_conf = addslashes(mysql_real_escape_string($_POST['admin_pwd_conf'],$connect));
		if ($admin_pwd != $admin_pwd_conf) {
			$error = 1;
			$content .= 'Your Administrator passwords did not match.<br />';
		}
		$query = str_replace('<!-- $DB_PREFIX$ -->',$dbprefix,$query);
		$query = str_replace('<!-- $SITE_NAME$ -->',$sitename,$query);
		$query = str_replace('<!-- $ADMIN_USER$ -->',$admin_user,$query);
		$query = str_replace('<!-- $ADMIN_PWD$ -->',md5($admin_pwd),$query);
		$query = explode(';;',$query);
		for ($i = 0; $i < count($query); $i++) {
			if(!mysql_query($query[$i],$connect)) {
				$content .= 'Query '.$query[$i].' failed to execute.<br />'.mysql_error($connect).'<br />';
				$error = 1;
				}
			} // FOR
		$config_file = '../config.php';
		$handle = fopen($config_file, "w");
		if(!$handle) {
			$content .= 'Failed to open configuration file for writing.<br />';
			$error = 1;
			}
		$config = '<?php
// Security Check
if (@SECURITY != 1) {
	die (\'You cannot access this page directly.\');
}
// Turn of \'register_globals\'
ini_set(\'register_globals\',0);
$CONFIG[\'SYS_PATH\'] = \'Unused\';						// Path to Community CMS on server
$CONFIG[\'db_engine\'] = \'mysqli\';					// Database Engine
$CONFIG[\'db_host\'] = \''.$_POST['dbhost'].'\';		// Database server host (usually localhost)
$CONFIG[\'db_host_port\'] = 3306;						// Database server port (default 3306 for mysqli)
$CONFIG[\'db_user\'] = \''.$_POST['dbuser'].'\';		// Database user
$CONFIG[\'db_pass\'] = \''.$_POST['dbpass'].'\';		// Database password
$CONFIG[\'db_name\'] = \''.$_POST['dbname'].'\';		// Database
$CONFIG[\'db_prefix\'] = \''.$_POST['dbpfix'].'\';		// Database table prefix

// Set the value below to \'1\' to disable Community CMS
$CONFIG[\'disabled\'] = 0;
?>';

		$config_write = fwrite($handle,$config);
		if(!$config_write) {
			$content .= 'Failed to write to config.php. Is it writeable?';
			$error = 1;
			}
		fclose($handle);
		if($error != 1) {
		$content .= 'Install Successful.<br />Please delete the install folder once you are able to confirm that the system is fully functional.
<br />
To log in, use either the username and password you provided, or login as an unprivileged user with the username \'user\' and the password \'password\'. <a href="../index.php">Continue to Site</a>';
		}
	}
	if($error == 1) {
		$content .= 'Install failed.';
		}
?>