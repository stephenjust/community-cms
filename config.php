<?php
	// Security Check
	if (@SECURITY != 1) {
		die ('You cannot access this page directly.');
		}

	// ComPortal Configuration file
	//
	// Eventually, we will have an install script.
	// For now though, manually configure.

	$CONFIG['SYS_PATH'] = '/var/www/communitycms/';	// Path to CommunityCMS on server
	$CONFIG['db_host'] = 'localhost';		// MySQL server host (usually localhost)
	$CONFIG['db_user'] = 'root';			// MySQL database user
	$CONFIG['db_pass'] = 'pass';			// MySQL database password
	$CONFIG['db_name'] = 'communitycms';		// MySQL database
	$CONFIG['db_prefix'] = 'comcms_';		// Prefix for database tables (Not yet used by code)
	
	// Set the value below to '1' to disable Community CMS
	$CONFIG['disabled'] = 0;
?>
