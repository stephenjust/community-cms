<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2007-2009 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.admin
 */
// Security Check
if (@SECURITY != 1 || @ADMIN != 1) {
	die ('You cannot access this page directly.');
}
$content = '<h1>Help</h1>';
if (!isset($_GET['page'])) {
	$page = 'table_of_contents';
} else {
	$page = addslashes($_GET['page']);
}
$content .= include(ROOT.'admin/help_pages/'.$page.'.php');
?>