<?php
/**
 * Community CMS Help Browser
 *
 * @copyright Copyright (C) 2010 Stephen Just
 * @author    stephenjust@users.sourceforge.net
 * @package   CommunityCMS.help
 */

namespace CommunityCMS;
/**#@+
 * @ignore
 */
define('ROOT', '../');
define('SECURITY', 1);
define('IN_HELP', 1);
define('IMAGE_PATH', ROOT.'help/images/');
define('FILE_PATH', ROOT.'help/text/');
/**#@-*/

// Include necessary files
require ROOT.'help/help_functions.php';
$help_files = help_read_list();

// Load current help file
$current_file = (isset($_GET['id'])) ? $_GET['id'] : 'index';
ob_start();
require FILE_PATH.$help_files['by-id'][$current_file]['file'];
$content = ob_get_clean();
$page_title = $help_files['by-id'][$current_file]['label'].' - Community CMS Help';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<title><?php echo $page_title; ?></title>
		<link rel="Stylesheet" type="text/css" href="help.css" />
	</head>
	<body>
		<div id="menu">
			<h1>Topics</h1>
    <?php echo help_menu($help_files); ?>
		</div>
		<div id="content">
    <?php echo $content; ?>
		</div>
	</body>
</html>