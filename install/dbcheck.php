<?php
/**
 * Community CMS Installer
 * $Id$
 *
 * @copyright Copyright (C) 2007-2009 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.install
 */

define('SECURITY',1);
define('ROOT','../');

$db_engine = $_GET['e'];
switch ($db_engine) {
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
$CONFIG['db_host'] = $_GET['h'];
$CONFIG['db_host_port'] = $_GET['p'];
$CONFIG['db_name'] = $_GET['n'];
$CONFIG['db_user'] = $_GET['u'];
$CONFIG['db_pass'] = $_GET['pa'];

include('../includes/db/db.php');
// The included file sets $db
if (@$db->sql_connect()) {
	echo 'Success! <input type="submit" value="Next" />';
} else {
	echo 'Failed to connect.';
}

?>
