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

$content .= 'Writing to configuration file... ';
if (config_file_write($_POST['engine'],
		$_POST['host'],
		$_POST['port'],
		$_POST['name'],
		$_POST['user'],
		$_POST['pass'],
		$_POST['prefix'])) {
	$content .= 'Success.<br />';
} else {
	$content .= 'Failed to write to config.php. Is it writeable?';
	return true;
}
fclose($handle);

$content .= '<form method="post" action="index.php?page=5"><input type="submit" value="Next" /></form>';
?>