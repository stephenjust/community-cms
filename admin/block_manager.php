<?php
	// Security Check
	if (@SECURITY != 1 || @ADMIN != 1) {
		die ('You cannot access this page directly.');
		}
	$root = "./";
	$message = NULL;
	if ($_GET['action'] == 'delete') {
		$block_exists_query = 'SELECT * FROM '.$CONFIG['db_prefix'].'blocks WHERE id = '.$_GET['id'].' LIMIT 1';
		$block_exists_handle = $db->query($block_exists_query);
		if(!$block_exists_handle) {
			$message .= 'Failed to read block information. '.mysqli_error($db);
			} else {
			if($block_exists_handle->num_rows == 1) {
				$delete_block_query = 'DELETE FROM '.$CONFIG['db_prefix'].'blocks WHERE id = '.$_GET['id'];
				$delete_block = $db->query($delete_block_query);
				if(!$delete_block) {
					$message .= 'Failed to delete block. '.mysqli_error($db);
					} else {
					$block_exists = $block_exists_handle->fetch_assoc();
					$message .= 'Successfully deleted block. '.log_action('Deleted block \''.$block_exists['type'].' ('.$block_exists['attributes'].')\'');
					}
				} else {
				$message .= 'Could not find the block you are trying to delete.';
				}
			}
		} elseif ($_GET['action'] == 'new') {
		$type = addslashes($_POST['type']);
		$attributes = addslashes($_POST['attributes']);
		$attributes = explode(',',$attributes);
		$attb_count = count($attributes);
		$i = 1;
		$attributes_final = NULL;
		while ($i <= $attb_count) {
			$attributes_final .= $attributes[$i - 1].'='.addslashes($_POST[$attributes[$i-1]]);
			if ($i != $attb_count) {
				$attributes_final .= ',';
				}
			$i++;
			}
		$new_query = 'INSERT INTO '.$CONFIG['db_prefix'].'blocks (type,attributes) VALUES ("'.$type.'","'.$attributes_final.'")';
		$new_handle = $db->query($new_query);
		if(!$new_handle) {
			$message .= 'Failed to create block.';
			} else {
			$message .= 'Successfully created block. '.log_action('Created block \''.$type.' ('.$attributes_final.')\'');
			}
		unset($type);
		}
	$content = $message;
$content .= '<h1>Block Manager</h1>
<table style="border: 1px solid #000000;">
<tr><td>ID</td><td width="350">Info:</td><td>&nbsp;</td><td>&nbsp;</td></tr>';
	// Get block list by id
	$block_list_query = 'SELECT * FROM '.$CONFIG['db_prefix'].'blocks ORDER BY id DESC';
	$block_list_handle = $db->query($block_list_query);
 	if($block_list_handle->num_rows == 0) {
 		$content .= '<tr><td></td><td class="adm_page_list_item">No blocks exist.</td><td></td><td></td></tr>';
 		} else {
 		$i = 1;
 		while ($i <= $block_list_handle->num_rows) {
 			$block_list = $block_list_handle->fetch_assoc();
 			$content .= '<tr>
<td>'.$block_list['id'].'</td>
<td class="adm_page_list_item">'.$block_list['type'].' ('.$block_list['attributes'].')</td>
<td><a href="?module=block_manager&action=delete&id='.$block_list['id'].'"><img src="<!-- $IMAGE_PATH$ -->delete.png" alt="Delete" width="16px" height="16px" border="0px" /></a></td>
<td><a href="?module=block_manager&action=edit&id='.$block_list['id'].'"><img src="<!-- $IMAGE_PATH$ -->edit.png" alt="Edit" width="16px" height="16px" border="0px" /></a></td>
</tr>';
			$i++;
 			} 
 		}
$content .= '</table>';
$directory = 'content_blocks/';
$folder_open = ROOT.$directory;
$files = scandir($folder_open);
unset($folder_open);
unset($directory);
$num_files = count($files);
$i = 2;
if($num_files < 4) { // ( ., .., blocks.info, and a block file)
	$content .= 'No installed blocks.';
	$bock_types_list = '<select name="type" disabled>';
	} else {
	$block_types_list = '<select name="type" id="adm_block_type_list" onChange="block_options_list_update()">';
	while($i < $num_files) {
		$block_type = explode('_',$files[$i]);
		$block_type = $block_type[0];
		if(!eregi('^\.',$files[$i]) && !eregi('~$',$files[$i]) && !eregi('\.info$',$files[$i])) {
			$block_types_list .= '<option value="'.$block_type.'">'.$block_type.'</option>';
			}
		$i++;
		unset($block_type);
		}
	$block_types_list .= '</select>';
	$content .= '<h1>New Block</h1>
	<form method="post" action="admin.php?module=block_manager&action=new">
	<table style="border: 1px solid #000000;">
	<tr><td>Type:</td><td>'.$block_types_list.'</td></tr>
	<tr><td>Options:</td><td><div id="adm_block_type_options"></div></td></tr>
	<tr><td></td><td><input type="submit" value="Submit" /></td></tr>
	</table></form>
	<script language="javascript" type="text/javascript">
	block_options_list_update()</script>';
	}

?>