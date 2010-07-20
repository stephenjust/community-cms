<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2007-2009 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.admin
 */
define('SECURITY',1);
define('ADMIN',1);
define('ROOT','../');
include(ROOT.'config.php');
include(ROOT.'include.php');
include(ROOT.'functions/admin.php');
include(ROOT.'functions/error.php');
initialize();
checkuser_admin();
$template = new template;
$template->load_admin_file('dialog');
$template->root = ROOT;
$template->dialog_title = 'Upload File';
$content = NULL;
// Check if the form has been submitted.
if(isset($_GET['upload'])) {
	if (isset($_POST['thumbs'])) {
		$content .= file_upload($_POST['path'],true,true);
	} else {
		$content .= file_upload($_POST['path']);
	}
}
// Display upload form and upload location selector.
if (isset($_GET['dir'])) {
	$extra_vars = array();
	if (isset($_GET['thumb'])) {
		$extra_vars['thumbs'] = 1;
	}
	$content .= file_upload_box(0,$_GET['dir'],$extra_vars);
} else {
	$content .= file_upload_box(1);
}
$template->dialog_body = $content;
echo $template;
clean_up();
?>