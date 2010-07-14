<?php
/**
 * Community CMS
 * $Id$
 *
 * @copyright Copyright (C) 2007-2010 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.admin
 */
// Security Check
if (@SECURITY != 1 || @ADMIN != 1) {
	die ('You cannot access this page directly.');
}

if (!$acl->check_permission('adm_filemanager')) {
	$content = '<span class="errormessage">You do not have the necessary permissions to use this module.</span><br />';
	return true;
}

$content = NULL;
function add_information($path, $label) {
	global $db;
	$new_info_query = 'INSERT INTO ' . FILE_TABLE . '
		(`label`, `path`) VALUES (\''.$label.'\',\''.$path.'\')';
	$new_info_handle = $db->sql_query($new_info_query);
	if($db->error[$new_info_handle] === 1) {
		return 'Failed to update information.<br />';
	}
	return 'New Information.';
}
function edit_information($id, $path, $label) {
	global $db;
	if($id == 0) {
		$update_query = 'UPDATE ' . FILE_TABLE . '
			SET `label` = \''.$label.'\' WHERE `path` = \''.$path.'\' LIMIT 1';
	} else {
		$update_query = 'UPDATE ' . FILE_TABLE . '
			SET `label` = \''.$label.'\' WHERE `id` = \''.$id.'\' AND `path` = \''.$path.'\' LIMIT 1';
	}
	$update_handle = $db->sql_query($update_query);
	if($db->error[$update_handle] === 1) {
		return 'Failed to update information.<br />';
	}
	return 'Edited information.';
}
if ($_GET['action'] == 'saveinfo') {
	$id = (int)$_POST['id'];
	$path = addslashes($db->sql_escape_string($_POST['path']));
	$label = addslashes($_POST['label']);
	$check_if_info_exists_query = 'SELECT * FROM ' . FILE_TABLE . '
		WHERE `id` = \''.$id.'\' OR `path` = \''.$path.'\' LIMIT 1';
	$check_if_info_exists_handle = $db->sql_query($check_if_info_exists_query);
	if ($db->error[$check_if_info_exists_handle] === 1) {
		$content .= 'Failed to check for existing entries in the database.';
	} else {
		if ($db->sql_num_rows($check_if_info_exists_handle) != 1) {
			$content .= add_information($path,$label);
		} else {
			$content .= edit_information($id,$path,$label);
		}
	}
	unset($_POST['path']);
}

// ----------------------------------------------------------------------------

// Upload file
if (isset($_GET['upload'])) {
	if ($acl->check_permission('file_upload')) {
		$content .= file_upload($_POST['path']);
	}
}
// ----------------------------------------------------------------------------

if ($_GET['action'] == 'new_folder') {
	if ($acl->check_permission('file_create_folder')) {
		$new_folder_name = addslashes($_POST['new_folder_name']);
		$error = 0;
		// Validate folder name
		if (strlen($new_folder_name) > 30) {
			$content .= 'New folder name too long.<br />';
			$error = 1;
		}
		if(strlen($new_folder_name) < 4) {
			$content .= 'New folder name too short.<br />';
			$error = 1;
		}
		if(!preg_match('#^[a-z0-9\_]+$#i',$new_folder_name) && $error != 1) {
			$content .= 'New folder name contains an invalid character.<br />';
			$error = 1;
		}
		if($error != 1) {
			if(!file_exists(ROOT.'files/'.$new_folder_name)) {
				mkdir(ROOT.'files/'.$new_folder_name);
				log_action('Created new directory \'files/'.$new_folder_name.'\'');
			} else {
				$content .= 'A file or folder with that name already exists.';
			}
		} // IF error
	}
} // IF 'new_folder'

// ----------------------------------------------------------------------------

if ($_GET['action'] == 'delete' && !isset($_GET['upload'])) {
	if (!isset($_GET['filename'])) {
		$content .= 'No file was specified to delete.<br />';
	} elseif (preg_match('#^\.\.|\.\.#',$_GET['filename']) || !file_exists($_GET['filename'])) {
		$content .= 'Invalid file name.<br />';
	} else {
		$del = unlink($_GET['filename']);
		if(!$del) {
			$content .= 'Failed to delete file.<br />';
		} else {
			$content .= 'Successfully deleted '.$_GET['filename'].'.<br />'.
				log_action('Deleted file \''.$_GET['filename'].'\'');
			$delete_info_query = 'DELETE FROM ' . FILE_TABLE . '
				WHERE `path` = \''.addslashes($_GET['filename']).'\'';
			$delete_info_handle = $db->sql_query($delete_info_query);
			if($db->error[$delete_info_handle] === 1) {
				$content .= 'Failed to delete information for this file.<br />';
			} else {
				$content .= 'Deleted information associated with the file.<br />';
			}
		}
	}
}

// ----------------------------------------------------------------------------

$tab_layout = new tabs;
if ($_GET['action'] == 'edit') {
	$tab_content['edit'] = NULL;
	$file_info_query = 'SELECT * FROM ' . FILE_TABLE . '
		WHERE `path` = \''.addslashes($db->sql_escape_string($_GET['file'])).'\' LIMIT 1';
	$file_info_handle = $db->sql_query($file_info_query);
	if ($db->error[$file_info_handle] === 1) {
		$tab_content['edit'] .= 'Could not read file information from database.';
		$file_info['label'] = NULL;
		$file_info['id'] = NULL;
		$file_info['path'] = $_GET['file'];
	} else {
		if ($db->sql_num_rows($file_info_handle) != 1) {
			$file_info['label'] = NULL;
			$file_info['id'] = NULL;
			$file_info['path'] = $_GET['file'];
		} else {
			$file_info = $db->sql_fetch_assoc($file_info_handle);
		}
	}
	$form = new form;
	$form->set_target('admin.php?module=filemanager&action=saveinfo');
	$form->set_method('post');
	$form->add_hidden('id',$file_info['id']);
	$form->add_hidden('path',$file_info['path']);
	$form->add_textbox('label','Label',$file_info['label']);
	$form->add_submit('submit','Save');
	$tab_content['edit'] .= $form;
	$tab_layout->add_tab('Edit File Properties',$tab_content['edit']);
}
if (!isset($_POST['folder_list']) && !isset($_POST['path'])) {
	if (isset($_GET['path'])) {
		$_POST['folder_list'] = $_GET['path'];
	} else {
		$_POST['folder_list'] = NULL;
	}
} elseif (!isset($_POST['folder_list']) && isset($_POST['path'])) {
	$_POST['folder_list'] = $_POST['path'];
}
$tab_content['list'] = '<form method="POST" action="admin.php?module=filemanager">
'.folder_list('',$_POST['folder_list'],1,'adm_file_dir_list','onChange="update_file_list(\'-\')"'); // Create listbox with folder names and a form to navigate folders.
$tab_content['list'] .= '</form>
<br />
<div id="adm_file_list">Loading...</div>
<script type="text/javascript">
update_file_list(\''.$_POST['folder_list'].'\');
</script>';

if ($acl->check_permission('file_create_folder')) {
	$tab_content['list'] .= '<br />
		<br />
		<form method="post" action="?module=filemanager&action=new_folder">
		New folder: <input type="text" name="new_folder_name" maxlength="30" />
		<input type="submit" value="Create Folder" />
		</form>';
}
$tab_layout->add_tab('File List',$tab_content['list']);

// ----------------------------------------------------------------------------

if ($acl->check_permission('file_upload')) {
	$tab_content['upload'] = NULL;

	// Display upload form and upload location selector.
	$tab_content['upload'] .= file_upload_box(1);
	$tab_layout->add_tab('Upload File',$tab_content['upload']);
}
$content .= $tab_layout;
?>