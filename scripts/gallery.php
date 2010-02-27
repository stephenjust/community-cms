<?php
/**
 * Community CMS
 * $Id$
 *
 * @copyright Copyright (C) 2007-2010 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.main
 */

define('SECURITY',1);
define('ROOT','../');

if (!isset($_GET['id'])) {
	header("HTTP/1.0 404 Not Found");
	exit;
}
if (!is_numeric($_GET['id'])) {
	header("HTTP/1.0 404 Not Found");
	exit;
}

require(ROOT.'config.php');
require(ROOT.'include.php');

initialize();

switch (get_config('gallery_app')) {
	case 'simpleviewer':

		break;
	default:
		header("HTTP/1.0 404 Not Found");
		clean_up();
		exit;
}

clean_up();
?>