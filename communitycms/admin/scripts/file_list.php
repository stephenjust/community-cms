<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2007-2012 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.admin
 */
/**#@+
 * @ignore
 */
define('ADMIN',1);
define('SECURITY',1);
define('ROOT','../../');
/**#@-*/

include (ROOT . 'config.php');
include (ROOT . 'include.php');
include (ROOT . 'functions/admin.php');

initialize('ajax');

if (!$acl->check_permission('adm_filemanager') || !$acl->check_permission('admin_access')) {
	die ('You do not have the necessary permissions to access this page.');
}
if (!isset($_GET['directory'])) {
	die ('No page ID provided to script.');
} else {
	$dir = replace_file_special_chars($_GET['directory']);
}

// Show special info about newsicons folder
if (folder_get_property($dir,'icons_only')) {
	echo '<div class="info">
		This folder can only contain icons. All of the images must be in PNG
		or Jpeg format. Images uploaded to this folder will automatically be
		resized to match your current icon size setting (default 100x100).
		</div><br />';
}

$file_list = new file_list;
$file_list->folder_form = $dir;
$file_list->set_directory($dir);
$file_list->get_list();
echo $file_list;

clean_up();
?>