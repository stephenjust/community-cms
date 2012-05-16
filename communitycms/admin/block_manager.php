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

global $acl;

if (!$acl->check_permission('adm_block_manager'))
	throw new AdminException('You do not have the necessary permissions to access this module.');

$tab_layout = new tabs;

switch ($_GET['action']) {
	default:
		break;

	case 'delete':
		try {
			block_delete($_GET['id']);
			echo 'Successfully deleted block.<br />';
		}
		catch (Exception $e) {
			echo '<span class="errormessage">'.$e->getMessage().'</span><br />';
		}
		break;

	case 'new':
		try {
			if (!isset($_POST['type'])) $_POST['type'] = NULL;
			if (!isset($_POST['attributes'])) $_POST['attributes'] = NULL;
			block_create($_POST['type'], $_POST['attributes']);
			echo 'Successfully created block.<br />';
		}
		catch (Exception $e) {
			echo '<span class="errormessage">'.$e->getMessage().'</span><br />';
		}
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

$block_list_query = 'SELECT `id`,`type`,`attributes`
	FROM `'.BLOCK_TABLE.'`
	ORDER BY `type` ASC';
$block_list_handle = $db->sql_query($block_list_query);
$block_list_rows = array();
for ($i = 1; $i <= $db->sql_num_rows($block_list_handle); $i++) {
	$block_list = $db->sql_fetch_assoc($block_list_handle);
	$attribute_list = ($block_list['attributes'] == '') ? NULL : ' ('.$block_list['attributes'].')';
	$current_row = array($block_list['type'].$attribute_list);
	if ($acl->check_permission('block_delete'))
		$current_row[] = '<a href="?module=block_manager&action=delete&id='.$block_list['id'].'"><img src="<!-- $IMAGE_PATH$ -->delete.png" alt="Delete" width="16px" height="16px" border="0px" /></a>';
	if ($acl->check_permission('block_edit'))
		$current_row[] = '<a href="?module=block_manager&action=edit&id='.$block_list['id'].'"><img src="<!-- $IMAGE_PATH$ -->edit.png" alt="Edit" width="16px" height="16px" border="0px" /></a>';
	$block_list_rows[] = $current_row;
}
$heading_list = array('Info');
if ($acl->check_permission('block_delete'))
	$heading_list[] = 'Delete';
if ($acl->check_permission('block_edit'))
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