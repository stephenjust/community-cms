<?php
// Security Check
if (@SECURITY != 1) {
	die ('You cannot access this page directly.');
}
// Turn of 'register_globals'
ini_set('register_globals',0);
$CONFIG['SYS_PATH'] = 'Unused';			// Path to Community CMS on server
$CONFIG['db_engine'] = 'mysqli';		// Database Engine
$CONFIG['db_host'] = 'localhost';		// Database server host (usually localhost)
$CONFIG['db_host_port'] = 3306;			// Database server port
$CONFIG['db_user'] = '';				// Database user
$CONFIG['db_pass'] = '';				// Database password
$CONFIG['db_name'] = 'communitycms';	// Database
$CONFIG['db_prefix'] = 'comcms_';		// Database table prefix

// Set the value below to '1' to disable Community CMS
$CONFIG['disabled'] = 1;
?>
