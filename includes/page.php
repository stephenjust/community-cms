<?php
/**
 * Community CMS
 * $Id$
 *
 * @copyright Copyright (C) 2007-2009 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.main
 */

/**
 * page_get_info - Get requested fields for a page entry in the database
 * @global object $db Database connection object
 * @global object $debug Debug object
 * @param int $id Page ID
 * @param array $fields Database fields to get, default is all
 * @return mixed Returns false on failure, associative array of row on success
 */
function page_get_info($id,$fields = array('*')) {
	global $db;
	global $debug;
	// Validate parameters
	if (!is_numeric($id)) {
		$debug->add_trace('ID is not an integer',true,'page_get_info');
		return false;
	}
	if(!is_array($fields)) {
		$debug->add_trace('Field list is not an array',true,'page_get_info');
		return false;
	}
	// Add backticks to field names and ensure that there are no spaces in field names
	$field_count = count($fields);
	for ($i = 0; $i < $field_count; $i++) {
		if (preg_match('/ /',$fields[$i])) {
			$debug->add_trace('Removed field "'.$fields[$i].'"',false,'page_get_info');
			unset($fields[$i]);
			continue;
		}
		if ($fields[$i] != '*' && strlen($fields[$i]) > 0) {
			$fields[$i] = '`'.$fields[$i].'`';
		}
	}
	$fields = array2csv($fields);
	$page_info_query = 'SELECT '.$fields.' FROM `' . PAGE_TABLE .'`
		WHERE `id` = '.$id.' LIMIT 1';
	$page_info_handle = $db->sql_query($page_info_query);
	if ($db->error[$page_info_handle] === 1) {
		$debug->add_trace('Failure to read page information',true,'page_get_info');
		return false;
	}
	if ($db->sql_num_rows($page_info_handle) != 1) {
		$debug->add_trace('Page not found',true,'page_get_info');
		return false;
	}
	$page_info = $db->sql_fetch_assoc($page_info_handle);
	return $page_info;
}

function page_add() {
	// FIXME: Stub
}

/**
 * page_delete - Delete a page entry from the database
 * @global object $acl Permissions object
 * @global object $db Database connection object
 * @global object $debug Debug object
 * @param int $id ID of page to delete
 * @return boolean Success
 */
function page_delete($id) {
	global $acl;
	global $db;
	global $debug;
	// Check for permission to execute
	if (!$acl->check_permission('page_delete')) {
		$debug->add_trace('Lacking permission to remove pages',true,'page_delete');
		return false;
	}
	// Validate parameters
	if (!is_int($id)) {
		$debug->add_trace('ID is not an integer',true,'page_delete');
		return false;
	}

	// FIXME: Check for content on page before deleting

	$page_info = page_get_info($id,array('title'));
	if (!$page_info) {
		$debug->add_trace('Failed to retrieve page info',true,'page_delete');
		return false;
	}

	// Delete page entry
	$delete_query = 'DELETE FROM `' . PAGE_TABLE . '`
		WHERE `id` = '.$id;
	$delete_handle = $db->sql_query($delete_query);
	if ($db->error[$delete_handle] === 1) {
		$debug->add_trace('Failure to delete page',true,'page_delete');
		return false;
	}
	if ($db->sql_affected_rows($delete_handle) < 1) {
		$debug->add_trace('Delete query did not delete any entries',true,'page_delete');
		return false;
	}
	log_action('Deleted page \''.stripslashes($page_info['title']).'\'');
	return true;
}

function page_hide($id) {
	global $acl;
	global $db;
	global $debug;
	// Check for permission to execute
	if (!$acl->check_permission('page_hide')) {
		$debug->add_trace('Lacking permission to hide pages',true,'page_hide');
		return false;
	}
	// Validate parameters
	if (!is_int($id)) {
		$debug->add_trace('ID is not an integer',true,'page_hide');
		return false;
	}
	// Check if page exists
	$page_info = get_page_info($id,array('title','hidden'));
	if (!$page_info) {
		$debug->add_trace('Failed to retrieve page info',true,'page_hide');
		return false;
	}
	// Check if page is already hidden
	if ($page_info['hidden'] == 1) {
		$debug->add_trace('Page is already hidden',true,'page_hide');
		return false;
	}
	// FIXME: Stub/incomplete
}

function page_unhide() {
	// FIXME: Stub
	// Could this be done by page_hide()?
}

/**
 * Generate a page list at a certain level in the page structure
 * @global object $db Database connection object
 * @param integer $parent Parent item of (sub)menu
 * @param boolean $visible_only Only list pages that will appear on menu
 * @return mixed False on fail, array of pages on success
 */
function page_list($parent = 0, $visible_only = false) {
	global $db;

	if (!is_numeric($parent) || is_array($parent)) {
		return false;
	}
	$parent = (int)$parent;

	$visible = NULL;
	if ($visible_only == true) {
		$visible = 'AND `menu` = 1 ';
	}

	$query = 'SELECT * FROM `'.PAGE_TABLE.'`
		WHERE `parent` = 0 '.$visible.'ORDER BY `list` ASC';
	$handle = $db->sql_query($query);
	if ($db->error[$handle] === 1) {
		return false;
	}
	if ($db->sql_num_rows($handle) == 0) {
		return false;
	}

	$page_list = array();

	for ($i = 0; $i < $db->sql_num_rows($handle); $i++) {
		$result = $db->sql_fetch_assoc($handle);

		$page_list[$i] = $result;
		$page_list[$i]['has_children'] = page_has_children($page_list[$i]['id']);
	}
	return $page_list;
}

/**
 * Test if there are any children to the given page
 * @global object $db Database connection object
 * @param integer $id Page ID of page to test
 * @param boolean $visible_children_only Only consider items that will appear in the menu
 * @return boolean
 */
function page_has_children($id, $visible_children_only = false) {
	global $db;

	if (!is_numeric($id) || is_array($id)) {
		return false;
	}
	$id = (int)$id;

	$visible = NULL;
	if ($visible_children_only == true) {
		$visible = 'AND `menu` = 1 ';
	}

	$query = 'SELECT * FROM `'.PAGE_TABLE.'`
		WHERE `parent` = '.$id.' '.$visible.'LIMIT 1';
	$handle = $db->sql_query($query);
	if ($db->error[$handle] === 1) {
		return false;
	}
	if ($db->sql_num_rows($handle) == 0) {
		return false;
	}
	return true;
}

/**
 * Clean the list values for the pages in the database
 * @global object $db Database connection object
 * @param integer $parent Parent of pages to reorder (0 for root)
 * @return boolean
 */
function page_clean_order($parent = 0) {
	global $db;

	if (!is_numeric($parent) || is_array($parent)) {
		return false;
	}
	$parent = (int)$parent;

	$page_list_query = 'SELECT * FROM ' . PAGE_TABLE . '
		WHERE `parent` = '.$parent.' ORDER BY `list` ASC';
	$page_list_handle = $db->sql_query($page_list_query);
	if($db->error[$page_list_handle] === 1) {
		// Quit the whole loop
		return false;
	} elseif ($db->sql_num_rows($page_list_handle) == 0 && $parent != 0) {
		// Continue to the next iteration
		return true;
	} elseif ($db->sql_num_rows($page_list_handle) == 0 && $parent == 0) {
		// For some reason, there is no top level pages. Abort.
		return false;
	} else {
		$page_list_rows = $db->sql_num_rows($page_list_handle);

		$subpages = array();

		// Reorder pages
		for ($i = 0; $i < $page_list_rows; $i++) {
			$page_list = $db->sql_fetch_assoc($page_list_handle);
			$move_page_query = 'UPDATE ' . PAGE_TABLE . '
				SET list = '.$i.' WHERE id = '.$page_list['id'];
			$move_page = $db->sql_query($move_page_query);
			if ($db->error[$move_page] === 1) {
				$content = 'Failed to optimize page order.<br />';
			}
			$subpages[] = $page_list['id'];
		}

		for ($i = 0; $i < count($subpages); $i++) {
			// Reorder sub-pages
			page_clean_order($subpages[$i]);
		}
	}
	return true;
}

/**
 * Move a page up the list in the site structure
 * @global object $db Database connection object
 * @param integer $id Page ID to move up
 * @return boolean
 */
function page_move_up($id) {
	global $db;

	if (!is_numeric($id) || is_array($id)) {
		return false;
	}
	$id = (int)$id;

	$page_info = page_get_info($id,array('id','list','parent'));
	if (!$page_info)  {
		return false;
	}

	$start_pos = $page_info['list'];
	$end_pos = $page_info['list'] - 1;
	$move_down_query1 = 'SELECT id,list FROM ' . PAGE_TABLE . "
		WHERE `list` = $end_pos
		AND `parent` = {$page_info['parent']} LIMIT 1";
	$move_down1 = $db->sql_query($move_down_query1);
	if ($db->sql_num_rows($move_down1) != 1) {
		return false;
	}
	$move_down_handle1 = $db->sql_fetch_assoc($move_down1);
	$move_up_query2 = 'UPDATE ' . PAGE_TABLE . '
		SET list = '.$end_pos.' WHERE id = '.$page_info['id'];
	$move_up_query3 = 'UPDATE ' . PAGE_TABLE . '
		SET list = '.$start_pos.' WHERE id = '.$move_down_handle1['id'];
	$move_up_handle2 = $db->sql_query($move_up_query2);
	$move_up_handle3 = $db->sql_query($move_up_query3);
	if ($db->error[$move_up_handle2] === 1 || $db->error[$move_up_handle3] === 1) {
		return false;
	}
	return true;
}

/**
 * Move a page down the list in the site structure
 * @global object $db Database connection object
 * @param integer $id Page ID to move down
 * @return boolean
 */
function page_move_down($id) {
	global $db;

	if (!is_numeric($id) || is_array($id)) {
		return false;
	}
	$id = (int)$id;

	$page_info = page_get_info($id,array('id','list','parent'));
	if (!$page_info)  {
		return false;
	}

	$start_pos = $page_info['list'];
	$end_pos = $page_info['list'] + 1;
	$move_up_query1 = "SELECT id,list FROM " . PAGE_TABLE . "
		WHERE `list` = $end_pos
		AND `parent` = {$page_info['parent']} LIMIT 1";
	$move_up1 = $db->sql_query($move_up_query1);
	if ($db->sql_num_rows($move_up1) != 1) {
		return false;
	}
	$move_up_handle1 = $db->sql_fetch_assoc($move_up1);
	$move_down_query2 = 'UPDATE ' . PAGE_TABLE . '
		SET list = '.$end_pos.' WHERE id = '.$page_info['id'];
	$move_down_query3 = 'UPDATE ' . PAGE_TABLE . '
		SET list = '.$start_pos.' WHERE id = '.$move_up_handle1['id'];
	$move_down_handle2 = $db->sql_query($move_down_query2);
	$move_down_handle3 = $db->sql_query($move_down_query3);
	if ($db->error[$move_down_handle2] === 1 || $db->error[$move_down_handle3] === 1) {
		return false;
	}
	return true;
}

function page_level($id) {
	global $db;

	if (!is_numeric($id) || is_array($id)) {
		return false;
	}
	$id = (int)$id;

	$page_info = page_get_info($id,array('parent'));
	if ($page_info['parent'] == 0) {
		return 0;
	}
	$level = 0;
	while ($page_info['parent'] != 0) {
		$page_info = page_get_info($page_info['parent'],array('parent'));
		$level++;
	}
	unset($page_info);
	return $level;
}

function page_path($id) {
	if (!is_numeric($id) || is_array($id)) {
		return false;
	}
	$id = (int)$id;

	if ($id == get_config('home')) {
		$page_info = page_get_info($id,array('title'));
		return $page_info['title'];
	}

	$page_info = page_get_info($id,array('parent','title'));
	$list = ' > '.$page_info['title'];
	while ($page_info['parent'] != 0) {
		$page_info = page_get_info($page_info['parent'],array('parent','title'));
		$list = ' > '.$page_info['title'].$list;
	}
	$page_info = page_get_info(get_config('home'),array('title'));
	$list = $page_info['title'].$list;
	return $list;
}
?>
