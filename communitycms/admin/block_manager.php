<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2007-2012 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.admin
 */
// Security Check
if (@SECURITY != 1 || @ADMIN != 1) {
	die ('You cannot access this page directly.');
}

/**
 * Edit a block entry
 * @global acl $acl
 * @global db $db
 * @param integer $id Block ID
 * @param string $attributes Comma separated list
 * @throws Exception 
 */
function block_edit($id,$attributes) {
	global $acl;
	global $db;
	
	if (!$acl->check_permission('block_edit'))
		throw new Exception('You are not allowed to edit content blocks.');

	// Validate inputs
	$id = (int)$id;
	if ($id < 1)
		throw new Exception('Invalid block ID.');
	$attributes = explode(',',$attributes);
	$attb_count = count($attributes);

	// Generate a string of attributes
	$attributes_final = array();
	for ($i = 0; $i < $attb_count; $i++) {
		if (!isset($_POST[$attributes[$i]])) $_POST[$attributes[$i]] = NULL;
		$attributes_final[] = $attributes[$i].'='.$_POST[$attributes[$i]];
	}
	$attb_string = $db->sql_escape_string(implode(',',$attributes_final));

	// Update the block record
	$query = 'UPDATE `'.BLOCK_TABLE."`
		SET `attributes` = '$attb_string'
		WHERE `id` = $id";
	$handle = $db->sql_query($query);
	if($db->error[$handle] === 1)
		throw new Exception('An error occurred while editing the block.');
	Log::addMessage('Edited block \''.$id.' ('.stripslashes($attb_string).')\'');
}


global $acl;

if (!$acl->check_permission('adm_block_manager'))
	throw new AdminException('You do not have the necessary permissions to access this module.');

$tab_layout = new tabs;

switch ($_GET['action']) {
	default:
		break;

	case 'delete':
		echo delete_block($_GET['id']);
		break;

	case 'new':
		if (!$acl->check_permission('block_create')) {
			echo '<span class="errormessage">You do not have the permissions required to create a new block.</span><br />';
			break;
		}
		$type = addslashes($_POST['type']);
		$attributes = addslashes($_POST['attributes']);
		if(strlen($attributes) > 0) {
			$attributes = explode(',',$attributes);
			$attb_count = count($attributes);
		} else {
			$attb_count = 0;
		}
		$attributes_final = NULL;
		for ($i = 0; $i < $attb_count; $i++) {
			$attributes_final .= $attributes[$i].'='.addslashes($_POST[$attributes[$i]]);
			if ($i + 1 != $attb_count) {
				$attributes_final .= ',';
			}
		} // FOR
		$new_query = 'INSERT INTO ' . BLOCK_TABLE . ' (type,attributes)
			VALUES (\''.$type.'\',\''.$attributes_final.'\')';
		$new_handle = $db->sql_query($new_query);
		if($db->error[$new_handle] === 1) {
			echo 'Failed to create block.';
			break;
		}
		echo 'Successfully created block.<br />'."\n";
		Log::addMessage('Created block \''.$type.' ('.$attributes_final.')\'');
		unset($type);
		break;

// ----------------------------------------------------------------------------

	case 'edit':
		if (!isset($_GET['id'])) {
			echo 'No block to edit.<br />'."\n";
			break;
		}
		if (!is_numeric($_GET['id'])) {
			echo 'Invalid block ID.<br />'."\n";
			break;
		}
		$edit_id = (int)$_GET['id'];
		$edit_block = new block;
		$edit_block->block_id = $edit_id;
		$edit_block->get_block_information();
		$options = block_edit_form($edit_block->type,$edit_block->attribute);

		$tab_content['edit'] = NULL;
		$tab_content['edit'] .= 'Block Type: '.$edit_block->type.'<br />'."\n";
		$tab_content['edit'] .= 'Options:<br />'."\n";
		$tab_content['edit'] .= '<form method="post" action="?module=block_manager&amp;action=edit_save">'."\n"
			.$options.'<input type="hidden" name="id" value="'.$edit_id.'" />'."\n";
		if (count($edit_block->attribute) != 0) {
			$tab_content['edit'] .= '<input type="Submit" value="Save Changes" />';
		}
		$tab_content['edit'] .= '</form><form method="post" action="?module=block_manager"><input type="submit" value="Go back" /></form>'."\n";


		$tab_layout->add_tab('Edit Block',$tab_content['edit']);
		break;

// ----------------------------------------------------------------------------

	case 'edit_save':
		try {
			if (!isset($_POST['id'])) $_POST['id'] = NULL;
			if (!isset($_POST['attributes'])) $_POST['attributes'] = NULL;
			block_edit($_POST['id'],$_POST['attributes']);
			echo 'Successfully edited block.<br />'."\n";
		}
		catch (Exception $e) {
			echo '<span class="errormessage">'.$e->getMessage().'</span><br />';
		}
		break;
}

// ----------------------------------------------------------------------------

$block_list_query = 'SELECT * FROM `' . BLOCK_TABLE . '` ORDER BY `id` DESC';
$block_list_handle = $db->sql_query($block_list_query);
$block_list_rows = array();
for ($i = 1; $i <= $db->sql_num_rows($block_list_handle); $i++) {
	$block_list = $db->sql_fetch_assoc($block_list_handle);
	$attribute_list = ($block_list['attributes'] == '') ? NULL : ' ('.$block_list['attributes'].')';
	if ($acl->check_permission('block_delete')) {
		$block_list_rows[] = array($block_list['type'].$attribute_list,
			'<a href="?module=block_manager&action=delete&id='.$block_list['id'].'"><img src="<!-- $IMAGE_PATH$ -->delete.png" alt="Delete" width="16px" height="16px" border="0px" /></a>',
			'<a href="?module=block_manager&action=edit&id='.$block_list['id'].'"><img src="<!-- $IMAGE_PATH$ -->edit.png" alt="Edit" width="16px" height="16px" border="0px" /></a>');
	} else {
		$block_list_rows[] = array($block_list['type'].$attribute_list,
			'<a href="?module=block_manager&action=edit&id='.$block_list['id'].'"><img src="<!-- $IMAGE_PATH$ -->edit.png" alt="Edit" width="16px" height="16px" border="0px" /></a>');
	}
}
$heading_list = array('Info');
if ($acl->check_permission('block_delete')) {
	$heading_list[] = 'Delete';
}
$heading_list[] = 'Edit';
$tab_content['manage'] = create_table($heading_list, $block_list_rows);
$tabs['manage'] = $tab_layout->add_tab('Manage Blocks',$tab_content['manage']);

// ----------------------------------------------------------------------------

if ($acl->check_permission('block_create')) {
	$tab_content['create'] = NULL;
	$directory = 'content_blocks/';
	$folder_open = ROOT.$directory;
	$files = scandir($folder_open);
	unset($folder_open);
	unset($directory);
	$num_files = count($files);
	$i = 2;
	if($num_files < 3) { // ( ., .., and a block file)
		$tab_content['create'] .= 'No installed blocks.';
		$block_types_list = '<select name="type" disabled>';
	} else {
		$block_types_list = '<select name="type" id="adm_block_type_list" onChange="block_options_list_update()">';
		while($i < $num_files) {
			$block_type = explode('_',$files[$i]);
			$block_type = $block_type[0];
			if(!preg_match('#^\.#',$files[$i]) && !preg_match('#~$#',$files[$i])) {
				$block_types_list .= '<option value="'.$block_type.'">'.$block_type.'</option>';
			}
			$i++;
			unset($block_type);
		}
		$block_types_list .= '</select>';

// ----------------------------------------------------------------------------

		$tab_content['create'] .= '<form method="post" action="admin.php?module=block_manager&action=new">
			<table class="admintable">
			<tr><td>Type:</td><td>'.$block_types_list.'</td></tr>
			<tr><td>Options:</td><td><noscript>You need JavaScript enabled for the block options view to work properly.</noscript>
			<div id="adm_block_type_options"></div></td></tr>
			<tr><td class="empty"></td><td><input type="submit" value="Submit" /></td></tr>
			</table></form>
			<script language="javascript" type="text/javascript">
			block_options_list_update()</script>';
	}
	$tab_layout->add_tab('Create Block',$tab_content['create']);
}

echo $tab_layout;
?>