<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2007-2010 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.admin
 */
// Security Check
if (@SECURITY != 1 || @ADMIN != 1) {
	die ('You cannot access this page directly.');
}

if (!$acl->check_permission('adm_filemanager'))
	throw new AdminException('You do not have the necessary permissions to access this module.');

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
	try {
		$content .= file_upload($_POST['path']);
	}
	catch (Exception $e) {
		$content .= '<span class="errormessage">'.$e->getMessage().'</span><br />'."\n";
	}
}

// Create new subfolder
if ($_GET['action'] == 'new_folder') {
	try {
		file_create_folder($_POST['new_folder_name']);
		$content .= 'Successfully created directory.<br />';
	}
	catch (Exception $e) {
		$content .= '<span class="errormessage">'.$e->getMessage()."</span><br />\n";
	}
}

// Delete files
if ($_GET['action'] == 'delete' && !isset($_GET['upload'])) {
	if (!isset($_GET['filename'])) {
		$content .= 'No file was specified to delete.<br />';
	} else {
		try {
			file_delete($_GET['filename']);
			$content .= 'Suucessfully deleted "'.$_GET['filename'].'".<br />';
		}
		catch (Exception $e) {
			$content .= '<span class="errormessage">'.$e->getMessage().'</span><br />';
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
	$form->set_target('admin.php?module=filemanager&amp;action=saveinfo&amp;path='.$_GET['path']);
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
'.folder_list('',basename($_POST['folder_list']),1,'adm_file_dir_list','onChange="update_file_list(\'-\')"'); // Create listbox with folder names and a form to navigate folders.
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
	try {
		$tab_content['upload'] .= file_upload_box(1);
	}
	catch (Exception $e) {
		$tab_content['upload'] .= '<span class="errormessage">'.$e->getMessage().'</span><br />';
	}
	$tab_layout->add_tab('Upload File',$tab_content['upload']);
}
$content .= $tab_layout;
?>