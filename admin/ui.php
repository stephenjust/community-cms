<?php
/**
 * Community CMS
 * $Id$
 *
 * @copyright Copyright (C) 2007-2009 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.admin
 */

/**
 * @ignore
 */
if (!defined('SECURITY') || !defined('ADMIN')) {
	exit;
}
global $debug;
$debug->add_trace('Using AJAX user interface',false,'ui.php');
$content = NULL;
$template = new template;
$template->load_admin_file('content_container');
$first = 1;
if (file_exists(ROOT . 'admin/'.$_GET['module'].'.php')) {
	// Load navigation items
	include_once(ROOT . 'admin/' . $_GET['module'] . '.php');
	$menu = NULL;
	if (!isset($menu_items)) {
		$debug->add_trace('Menu has no items',false,'ui.php');
	} else {
		foreach ($menu_items as $mode_name => $link_name) {
			if ($first == 1) {
				$first_mode = $mode_name;
				$first = 0;
			}
			$menu .= '<div id="tab-'.$mode_name.'" class="tab" onClick="changeContent(\''.$_GET['module'].'\',\''.$mode_name.'\',\'\')">'.$link_name.'</div>'."\n";
		}
		unset($mode_name);
		unset($link_name);
	}
	$template->tabs_vertical = $menu;
} else {
	$debug->add_trace('Module file does not exist',true,'ui.php');
}

if (isset($_GET['upload'])) {
	if (!is_numeric($_GET['upload'])) {
		$upload_field = $_GET['upload'];
		// Check if file data was actually sent
		if (!isset($_FILES[$upload_field])) {
			$debug->add_trace('No file data was sent',true,'ui.php');
		} else {
			$_FILES['upload'] = $_FILES[$upload_field];
			$debug->add_trace('Attempting to upload file',false,'ui.php');
			file_upload('tmp',false);
			$filename = basename($_FILES[$upload_field]['name']);
		}
	}
}
$content .= $template;
if ($first == 0) {
	if (!isset($current_mode)) {
		$current_mode = $first_mode;
	}
	$action = (isset($_GET['action'])) ? $_GET['action'] : NULL;
	if (isset($filename)) {
		$action .= '&file='.$filename;
	}
	$content .= '<script language="javascript" type="text/javascript">changeContent("'.$_GET['module'].'","'.$current_mode.'","'.$action.'");</script>';
}
?>
