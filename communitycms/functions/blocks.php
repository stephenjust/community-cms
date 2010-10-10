<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2007-2009 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.main
 */
// Security Check
if (@SECURITY != 1) {
	die ('You cannot access this page directly.');
}

/**
 * get_block - Get contents of a block
 * @global acl $acl
 * @global db $db
 * @param int $block_id ID of block to display
 * @return string
 */
function get_block($block_id = NULL) {
	$block_id = (int)$block_id;
	if(strlen($block_id) < 1 || $block_id <= 0) {
		return;
	}
	global $acl;
	global $db;
	$block_content = NULL;
	$block_query = 'SELECT * FROM ' . BLOCK_TABLE . '
		WHERE id = '.$block_id.' LIMIT 1';
	$block_handle = $db->sql_query($block_query);
	if ($db->error[$block_handle] === 0) {
		if ($db->sql_num_rows($block_handle) == 1) {
			$block_info = $db->sql_fetch_assoc($block_handle);
			$block_content .= include(ROOT.'content_blocks/'.$block_info['type'].'_block.php');
		} else {
			if ($acl->check_permission('show_fe_errors')) {
				$block_content .= '<div class="notification"><strong>Error:</strong> Could not load block '.$block_id.'.</div>';
			}
		}
	}
	return $block_content;
}

/**
 * delete_block - Delete a block
 * @global object $acl Permission object
 * @global db $db Database connection object
 * @global debug $debug Debug object
 * @param integer $id Block ID
 * @return string Response message
 */
function delete_block($id) {
	global $acl;
	global $db;
	global $debug;
	$message = NULL;

	if (!$acl->check_permission('block_delete')) {
		$message = '<span class="errormessage">You do not have the necessary permissions to delete a block.</span><br />';
		return $message;
	}

	// Check data types
	if (!is_numeric($id)) {
		$message .= 'Malformed block ID provided.<br />'."\n";
		return $message;
	}
	$block_exists_query = 'SELECT * FROM `' . BLOCK_TABLE . '`
		WHERE `id` = '.$id.' LIMIT 1';
	$block_exists_handle = $db->sql_query($block_exists_query);
	if($db->error[$block_exists_handle] === 1) {
		$message .= 'Failed to read block information.<br />'."\n";
	} else {
		if ($db->sql_num_rows($block_exists_handle) == 1) {
			$delete_block_query = 'DELETE FROM `' . BLOCK_TABLE . '`
				WHERE `id` = '.$id;
			$delete_block = $db->sql_query($delete_block_query);
			if (!$db->error[$delete_block] === 1) {
				$message .= 'Failed to delete block.<br />'."\n";
			} else {
				$block_exists = $db->sql_fetch_assoc($block_exists_handle);
				log_action('Deleted block \''.$block_exists['type'].' ('.$block_exists['attributes'].')\'');
				$message .= 'Successfully deleted block.<br />'."\n";
			}
		} else {
			$message .= 'Could not find the block you are trying to delete.<br />'."\n";
		}
		return $message;
	}
}
?>