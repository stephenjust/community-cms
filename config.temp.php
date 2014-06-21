<?php
/**
 * Community CMS
 *
 * This is an example configuration file for Community CMS. When the installer
 * script is run, it will create a new configuration file with the values
 * proveided to the installation script.
 *
 * @package CommunityCMS.main
 */
// Security Check
if (@SECURITY != 1) {
	die ('You cannot access this page directly.');
}
// Turn of 'register_globals'
ini_set('register_globals',0);
$CONFIG['SYS_PATH'] = '';			// Path to Community CMS on server
$CONFIG['db_engine'] = '';			// Database Engine
$CONFIG['db_host'] = '';			// Database server host (usually localhost)
$CONFIG['db_host_port'] = 0;		// Database server port
$CONFIG['db_user'] = '';			// Database user
$CONFIG['db_pass'] = '';			// Database password
$CONFIG['db_name'] = '';			// Database
$CONFIG['db_prefix'] = '';			// Database table prefix

// Set the value below to '1' to disable Community CMS
$CONFIG['disabled'] = 1;
