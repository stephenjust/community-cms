<?php
  define('ROOT','../');
  define('SECURITY',1);
  $nav_bar = "<div align='center'><span style='color: #00CC00;'>Check file permissions</span><hr />\n<span style='color: #00CC00;'>Configure settings</span><hr />\n<span style='color: #CCCC00;'>Download/save config file</span></div>\n";
  $content = "<h1>Installing...</h1>\n";
  $content = $content."Right now, the site name value you entered is not recognized by the installer. To configure the system further, you must use a program such as phpMyAdmin to edit site information.";
  include('../include.php');
	$connect = mysql_connect($_POST['dbhost'],$_POST['dbuser'],$_POST['dbpass']);
	if (!$connect) {
		$content = 'One or more fields was filled out incorrectly. Please hit your browser\'s back button and correct the mistake. Could not connect to database.<br />';
		$error = 1;
		} else {
		// Try to open the database that is used by Community CMS.
		$select_db = mysql_select_db($_POST['dbname'],$connect);
		if(!$select_db) {
			$content = 'One or more fields was filled out incorrectly. Please hit your browser\'s back button and correct the mistake. Could not find database.<br />';
			$error = 1;
			}
		}
	if(!$connect) {
		$content = 'One or more fields was filled out incorrectly. Please hit your browser\'s back button and correct the mistake.<br />';
		$error = 1;
		} else {
		$handle = fopen('./schema/MySQL.sql', "r");
		if(!$handle) {
			$content .= 'Failed to read default database schema.';
			$error = 1;
			}
		$query = fread($handle, filesize('./schema/MySQL.sql'));
		fclose($handle);
		$query = explode(';',$query);
		$i = 1;
		$f = count($query);
		while ($i <= $f) {
			if(!mysql_query($query[$i],$connect)) {
				$content .= 'Query '.$query[$i].' failed to execute.<br />';
				$error = 1;
				}
			$i++;
			}
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
	$CONFIG[\'SYS_PATH\'] = \'Unused\';	// Path to Community CMS on server
	$CONFIG[\'db_host\'] = \''.$_POST['dbhost'].'\';		// MySQL server host (usually localhost)
	$CONFIG[\'db_user\'] = \''.$_POST['dbuser'].'\';			// MySQL database user
	$CONFIG[\'db_pass\'] = \''.$_POST['dbpass'].'\';			// MySQL database password
	$CONFIG[\'db_name\'] = \''.$_POST['dbname'].'\';		// MySQL database
	$CONFIG[\'db_prefix\'] = \''.$_POST['dbpfix'].'\';		// Prefix for database tables (must be "comcms_")

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
		$content .= 'Install Successful. Please delete the install folder once you are able to confirm that the system is fully functional.';
		}
	}
	if($error == 1) {
		$content .= 'Install failed.';
		}
?>