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

// ----------------------------------------------------------------------------

		} elseif ($_GET['action'] == 'new') {
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
		$new_query = 'INSERT INTO '.$CONFIG['db_prefix'].'blocks (type,attributes) VALUES ("'.$type.'","'.$attributes_final.'")';
		$new_handle = $db->query($new_query);
		if(!$new_handle) {
			$message .= 'Failed to create block.';
			} else {
			$message .= 'Successfully created block. '.log_action('Created block \''.$type.' ('.$attributes_final.')\'');
			}
		unset($type);
		}

// ----------------------------------------------------------------------------

	$content = $message;
	$tab_layout = new tabs;
	$tab_content['manage'] = NULL;
	$tab_content['manage'] .= '<table class="admintable">
		<tr><th width="30">ID</th><th>Info:</th><th width="40" colspan="2"></th></tr>';
	// Get block list by id
	$block_list_query = 'SELECT * FROM '.$CONFIG['db_prefix'].'blocks ORDER BY id DESC';
	$block_list_handle = $db->query($block_list_query);
 	if($block_list_handle->num_rows == 0) {
 		$tab_content['manage'] .= '<tr><td></td><td class="adm_page_list_item">No blocks exist.</td><td></td><td></td></tr>';
 		} else {
 		$i = 1;
 		while ($i <= $block_list_handle->num_rows) {
 			$block_list = $block_list_handle->fetch_assoc();
 			$tab_content['manage'] .= '<tr>
<td>'.$block_list['id'].'</td>
<td class="adm_page_list_item">'.$block_list['type'].' ('.$block_list['attributes'].')</td>
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