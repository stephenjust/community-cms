<?php
/**
 * Community CMS
 * $Id$
 *
 * @copyright Copyright (C) 2007-2009 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.admin
 */
// Security Check
if (@SECURITY != 1 || @ADMIN != 1) {
	die ('You cannot access this page directly.');
	}
$root = "./";
$message = NULL;

$tab_layout = new tabs;

switch ($_GET['action']) {
	default:
		break;

	case 'delete':
		$message .= delete_block($_GET['id']);
		break;

	case 'new':
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
			$message .= 'Failed to create block.';
			break;
		}
		$message .= 'Successfully created block.<br />'."\n";
		log_action('Created block \''.$type.' ('.$attributes_final.')\'');
		unset($type);
		break;

// ----------------------------------------------------------------------------

	case 'edit':
		if (!isset($_GET['id'])) {
			$message .= 'No block to edit.<br />'."\n";
			break;
		}
		if (!is_numeric($_GET['id'])) {
			$message .= 'Invalid block ID.<br />'."\n";
			break;
		}
		$edit_id = (int)$_GET['id'];
		$edit_block = new block;
		$edit_block->block_id = $edit_id;
		$edit_block->get_block_information();

		// Read block info file
		$info_file_handle = fopen(ROOT.'content_blocks/blocks.info','r');
		if (!$info_file_handle) {
			$message .= 'Failed to read block information file.<br />'."\n";
			break;
		}
		$info_file = fread($info_file_handle,filesize(ROOT.'content_blocks/blocks.info'));
		fclose($info_file_handle);

		// Parse relevant entry in block info file
		$info_file = explode("\n",$info_file);
		foreach ($info_file as $info_entry) {
			if (!preg_match('/'.$edit_block->type.'#.?/i',$info_entry)) {
				continue;
			}
			$info = $info_entry;
		}
		if (!isset($info)) {
			$message .= 'Entry missing for this block type. Is the info file corrupted?<br />'."\n";
			break;
		}

		unset($info_file);
		unset($info_entry);

		$tab_content['edit'] = NULL;
		$tab_content['edit'] .= 'Block Type: '.$edit_block->type.'<br />'."\n";
		$tab_content['edit'] .= 'Options:<br />'."\n";

		// Separate block type in info file from attributes
		$info_temp = explode('#',$info);
		$attributes = $info_temp[1];
		unset($info_temp);

		// Parse attributes
		$attributes = explode('&',$attributes);
		$num_attributes = count($attributes);
		$j = 1;

		// If no attributes
		if ($num_attributes == 0 || strlen($attributes[0]) < 1) {
			$tab_content['edit'] .= 'No options available.<br />
				<form method="post" action="?module=block_manager"><input type="submit" value="Go back" /></form>'."\n";
			$tab_layout->add_tab('Edit Block',$tab_content['edit']);
			break;
		}

		// Begin the form
		$tab_content['edit'] .= '<form method="post" action="?module=block_manager&amp;action=edit_save">'."\n";
		$allattributes = NULL;
		for ($j = 1; $j <= $num_attributes; $j++) {
			$atb = explode('=',$attributes[$j - 1]);
			$temp = explode('(\'',$atb[0]);
			$attribute_name = $temp[0];
			$temp = substr($temp[1],0,-2);
			$attribute_description = $temp;
			unset($temp);
			$tab_content['edit'] .= $attribute_description.'=';
			if (preg_match('#\{.+\}#',$atb[1])) {
				$temp = explode('{',$atb[1]);
				$attribute_type = $temp[0];
				$atb[1] = $temp[0];
				$possible_responses = substr($temp[1],0,-1);
			}

			// Handle each field type
			if ($atb[1] == 'int') { // $atb[1] = attribute type
				$tab_content['edit'] .= '<input type="text" maxlength="9" size="3" name="'.$attribute_name.'" value="'.$edit_block->attribute[$attribute_name].'" /><br />'."\n";
			} elseif ($atb[1] == 'option') {
				$tab_content['edit'] .= '<select name="'.$attribute_name.'">'."\n";
				$possible_responses = explode(',',$possible_responses);
				for ($i = 1; $i <= count($possible_responses); $i++) {
					if ($edit_block->attribute[$attribute_name] == $possible_responses[$i - 1]) {
						$tab_content['edit'] .= '<option value="'.$possible_responses[$i - 1].'" selected>'.$possible_responses[$i - 1].'</option>'."\n";
					} else {
						$tab_content['edit'] .= '<option value="'.$possible_responses[$i - 1].'">'.$possible_responses[$i - 1].'</option>'."\n";
					}
				}
				$tab_content['edit'] .= '</select><br />'."\n";
			} else {
				$tab_content['edit'] .= 'Not supported.<br />'."\n";
			}
			$allattributes .= $attribute_name;
			if ($j != $num_attributes) {
				$allattributes .= ',';
				}
			} // FOR $j
		$tab_content['edit'] .= '<input type="hidden" name="attributes" value="'.$allattributes.'" />';
		$tab_content['edit'] .= '<input type="hidden" name="id" value="'.$edit_id.'" />'."\n";
		$tab_content['edit'] .= '<input type="Submit" value="Save Changes" /></form>';
		$tab_layout->add_tab('Edit Block',$tab_content['edit']);
		break;

// ----------------------------------------------------------------------------

	case 'edit_save':
		if (!isset($_POST['id'])) {
			$message .= 'No block to save.<br />'."\n";
			break;
		}
		if (!is_numeric($_POST['id'])) {
			$message .= 'Invalid block ID.<br />'."\n";
			break;
		}
		if (!isset($_POST['attributes'])) {
			$message .= 'No attributes to save.<br />'."\n";
			break;
		}
		$attributes = explode(',',$_POST['attributes']);
		unset($_POST['attributes']);
		$attb_count = count($attributes);

		$attributes_final = NULL;
		for ($i = 0; $i < $attb_count; $i++) {
			$attributes_final .= $attributes[$i].'='.addslashes($_POST[$attributes[$i]]);
			if ($i + 1 != $attb_count) {
				$attributes_final .= ',';
			}
		} // FOR
		$new_query = 'UPDATE `' . BLOCK_TABLE . '`
			SET `attributes` = \''.$attributes_final.'\'
			WHERE `id` = '.(int)$_POST['id'].'';
		$new_handle = $db->sql_query($new_query);
		if($db->error[$new_handle] === 1) {
			$message .= 'Failed to edit block.';
			break;
		}
		$message .= 'Successfully edited block.<br />'."\n";
		log_action('Edited block \''.$_POST['id'].' ('.$attributes_final.')\'');
		break;
}

// ----------------------------------------------------------------------------

$content = $message;
$tab_content['manage'] = NULL;
$tab_content['manage'] .= '<table class="admintable">
	<tr><th>Info:</th><th width="40" colspan="2"></th></tr>';
// Get block list by id
$block_list_query = 'SELECT * FROM ' . BLOCK_TABLE . ' ORDER BY id DESC';
$block_list_handle = $db->sql_query($block_list_query);
if($db->sql_num_rows($block_list_handle) == 0) {
	$tab_content['manage'] .= '<tr><td colspan="3">No blocks exist.</td></tr>';
} else {
	$i = 1;
	while ($i <= $db->sql_num_rows($block_list_handle)) {
		$block_list = $db->sql_fetch_assoc($block_list_handle);
		$attribute_list = ($block_list['attributes'] == '') ? NULL : ' ('.$block_list['attributes'].')';
		$tab_content['manage'] .= '<tr>
<td>'.$block_list['type'].$attribute_list.'</td>
<td><a href="?module=block_manager&action=delete&id='.$block_list['id'].'"><img src="<!-- $IMAGE_PATH$ -->delete.png" alt="Delete" width="16px" height="16px" border="0px" /></a></td>
<td><a href="?module=block_manager&action=edit&id='.$block_list['id'].'"><img src="<!-- $IMAGE_PATH$ -->edit.png" alt="Edit" width="16px" height="16px" border="0px" /></a></td>
</tr>';
		$i++;
	}
}
$tab_content['manage'] .= '</table>';
$tabs['manage'] = $tab_layout->add_tab('Manage Blocks',$tab_content['manage']);

// ----------------------------------------------------------------------------

$tab_content['create'] = NULL;
$directory = 'content_blocks/';
$folder_open = ROOT.$directory;
$files = scandir($folder_open);
unset($folder_open);
unset($directory);
$num_files = count($files);
$i = 2;
if($num_files < 4) { // ( ., .., blocks.info, and a block file)
	$tab_content['create'] .= 'No installed blocks.';
	$block_types_list = '<select name="type" disabled>';
} else {
	$block_types_list = '<select name="type" id="adm_block_type_list" onChange="block_options_list_update()">';
	while($i < $num_files) {
		$block_type = explode('_',$files[$i]);
		$block_type = $block_type[0];
		if(!preg_match('#^\.#',$files[$i]) && !preg_match('#~$#',$files[$i]) && !preg_match('#\.info$#',$files[$i])) {
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
$content .= $tab_layout;
?>