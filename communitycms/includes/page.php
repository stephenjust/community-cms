<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2007-2009 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.main
 */

/**
 * page_get_info - Get requested fields for a page entry in the database
 * @global db $db Database connection object
 * @global debug $debug Debug object
 * @param integer $id Page ID
 * @param array $fields Database fields to get, default is all
 * @return mixed Returns false on failure, associative array of row on success
 */
function page_get_info($id,$fields = array('*')) {
	global $db;
	global $debug;
	// Validate parameters
	if (!is_numeric($id)) {
		$debug->add_trace('ID is not an integer',true);
		return false;
	}
	if(!is_array($fields)) {
		$debug->add_trace('Field list is not an array',true);
		return false;
	}
	// Add backticks to field names and ensure that there are no spaces in field names
	$field_count = count($fields);
	for ($i = 0; $i < $field_count; $i++) {
		if (preg_match('/ /',$fields[$i])) {
			$debug->add_trace('Removed field "'.$fields[$i].'"',false);
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
		$debug->add_trace('Failure to read page information',true);
		return false;
	}
	if ($db->sql_num_rows($page_info_handle) != 1) {
		$debug->add_trace('Page \''.$id.'\' not found',true);
		return false;
	}
	$page_info = $db->sql_fetch_assoc($page_info_handle);
	return $page_info;
}

/**
 * Check if a new text ID is unique
 * @global db $db
 * @global debug $debug
 * @param string $text_id
 * @return boolean 
 */
function page_check_unique_id($text_id) {
	global $db;
	global $debug;

	if (strlen($text_id) == 0) {
		$debug->add_trace('Text ID is empty',true);
		return false;
	}
	$text_id_query = 'SELECT * FROM `'.PAGE_TABLE.'`
		WHERE `text_id` = \''.$text_id.'\' LIMIT 1';
	$text_id_handle = $db->sql_query($text_id_query);
	if ($db->sql_num_rows($text_id_handle) == 1) {
		return false;
	}
	return true;
}

/**
 * Create a page record
 * @global acl $acl
 * @global db $db
 * @global debug $debug
 * @global log $log
 * @param string $text_id
 * @param string $title
 * @param string $meta_desc
 * @param integer $type
 * @param boolean $show_title
 * @param boolean $show_menu
 * @param integer $parent
 * @param integer $group
 * @return boolean
 */
function page_add($text_id,$title,$meta_desc,$type,$show_title,$show_menu,$parent,$group) {
	global $acl;
	global $db;
	global $debug;
	global $log;

	if (!$acl->check_permission('page_create')) {
		return false;
	}

	// Validate parameters
	if (strlen($text_id) == 0) {
		$text_id = NULL;
	} else {
		$text_id = strtolower(str_replace(array(' ','/','\\','?','&','\'','"'),'_',$text_id));
		// Make sure text ID is unique
		if (!page_check_unique_id($text_id)) {
			$text_id = NULL;
		}
	}
	if (strlen($title) == 0) {
		$debug->add_trace('Page title is not long enough.',true);
		return false;
	}
	if (strlen($meta_desc) == 0) {
		$meta_desc = NULL;
	}
	if (!is_numeric($type)) {
		$debug->add_trace('Page type is not numeric',true);
		return false;
	}
	$type = (int)$type;
	if (!is_bool($show_title)) {
		$debug->add_trace('Show title value is not a boolean',true);
		return false;
	}
	$show_title = ($show_title === true) ? 1 : 0;
	if (!is_bool($show_menu)) {
		$debug->add_trace('Show menu value is not a boolean',true);
		return false;
	}
	$show_menu = ($show_menu === true) ? 1 : 0;
	if (!is_numeric($parent)) {
		$debug->add_trace('Parent page ID is not numeric',true);
		return false;
	}
	$parent = (int)$parent;
	if (!is_numeric($group)) {
		$debug->add_trace('Page group is not numeric',true);
		return false;
	}
	$group = (int)$group;

	// Add page to database.
	$new_page_query = 'INSERT INTO `'.PAGE_TABLE.'`
		(`text_id`,`title`,`meta_desc`,`show_title`,`type`,`menu`,`parent`,`page_group`)
		VALUES
		(\''.$text_id.'\',\''.addslashes($title).'\',\''.addslashes($meta_desc).'\','.$show_title.',
		'.$type.','.$show_menu.','.$parent.','.$group.')';
	$new_page = $db->sql_query($new_page_query);
	if ($db->error[$new_page] === 1) {
		return false;
	}
	$log->new_message('New page \''.$title.'\'');
	return true;
}

function page_add_link() {
	// FIXME: Stub
}

/**
 * Create a new page group
 * @global acl $acl Permission object
 * @global db $db Database object
 * @global debug $debug Debugging object
 * @global log $log Logger object
 * @param string $group_name Name of new group
 * @return boolean Success
 */
function page_add_group($group_name) {
	global $acl;
	global $db;
	global $debug;
	global $log;

	if (!$acl->check_permission('page_group_create')) {
		return false;
	}
	if (strlen($group_name) == 0) {
		return false;
	}
	$group_name = addslashes($group_name);

	// Create group
	$query = 'INSERT INTO `'.PAGE_GROUP_TABLE.'` (`label`)
		VALUES (\''.$group_name.'\')';
	$handle = $db->sql_query($query);
	if ($db->error[$handle] === 1) {
		return false;
	}

	// Create permission key
	if (!$acl->create_key('pagegroupedit-'.$db->sql_insert_id(PAGE_GROUP_TABLE,'id'),
			'Edit Page Group \''.stripslashes($group_name).'\'',
			'Allow user to edit pages in the group \''.stripslashes($group_name).'\'',0)) {
		$debug->add_trace('Failed to create new permission value',true);
		return false;
	}
	$log->new_message('Created page group \''.stripslashes($group_name).'\'');
	return true;
}

/**
 * page_delete - Delete a page entry from the database
 * @global object $acl Permissions object
 * @global db $db Database connection object
 * @global debug $debug Debug object
 * @global Log $log Logger object
 * @param int $id ID of page to delete
 * @return boolean Success
 */
function page_delete($id) {
	global $acl;
	global $db;
	global $debug;
	global $log;
	// Check for permission to execute
	if (!$acl->check_permission('page_delete')) {
		$debug->add_trace('Lacking permission to remove pages',true);
		return false;
	}
	// Validate parameters
	if (!is_int($id)) {
		$debug->add_trace('ID is not an integer',true);
		return false;
	}

	// FIXME: Check for content on page before deleting

	$page_info = page_get_info($id,array('title'));
	if (!$page_info) {
		$debug->add_trace('Failed to retrieve page info',true);
		return false;
	}

	// Delete page entry
	$delete_query = 'DELETE FROM `' . PAGE_TABLE . '`
		WHERE `id` = '.$id;
	$delete_handle = $db->sql_query($delete_query);
	if ($db->error[$delete_handle] === 1) {
		$debug->add_trace('Failure to delete page',true);
		return false;
	}
	if ($db->sql_affected_rows($delete_handle) < 1) {
		$debug->add_trace('Delete query did not delete any entries',true);
		return false;
	}
	$log->new_message('Deleted page \''.$page_info['title'].'\'');
	return true;
}

/**
 * Delete a page group
 * @global acl $acl
 * @global db $db
 * @global debug $debug
 * @global log $log
 * @param integer $group_id ID of group to delete
 * @return mixed Boolean for success, integer error codes for more detailed error messages
 */
function page_delete_group($group_id) {
	global $acl;
	global $db;
	global $debug;
	global $log;

	if (!is_numeric($group_id)) {
		$debug->add_trace('No ID provided',true);
		return false;
	}
	// Make sure page group is empty before deleting it
	$verify_empty_query = 'SELECT `page_group` FROM `'.PAGE_TABLE.'`
		WHERE `page_group` = '.$group_id;
	$verify_empty_handle = $db->sql_query($verify_empty_query);
	if ($db->error[$verify_empty_handle] === 1) {
		$debug->add_trace('Failed to check if the group is empty',true);
		return false;
	}
	if ($db->sql_num_rows($verify_empty_handle) != 0) {
		$debug->add_trace('The group is not empty',true);
		return 2;
	}

	// Remove any permission assignments related to this group
	$get_permission_query = 'SELECT `acl_table`.`acl_record_id` FROM
		`'.ACL_KEYS_TABLE.'` `key_table`, `'.ACL_TABLE.'` `acl_table` WHERE
		`acl_table`.`acl_id` = `key_table`.`acl_id`
		AND `key_table`.`acl_name` = \'pagegroupedit-'.$group_id.'\'';
	$get_permission_handle = $db->sql_query($get_permission_query);
	if ($db->error[$get_permission_handle] === 1) {
		$debug->add_trace('Failed to check if there are users with permission to edit this group',true);
		return false;
	}
	for ($i = 1; $i <= $db->sql_num_rows($get_permission_handle); $i++) {
		$permission_entry = $db->sql_fetch_assoc($get_permission_handle);
		$remove_permission_query = 'DELETE FROM `'.ACL_TABLE.'` WHERE
			`acl_record_id` = '.$permission_entry['acl_record_id'];
		$remove_permission_handle = $db->sql_query($remove_permission_query);
		if ($db->error[$remove_permission_handle] === 1) {
			$debug->add_trace('Failed to delete user permission records',true);
			return 3;
		}
	}

	// Remove permission key
	$del_acl_key_query = 'DELETE FROM `'.ACL_KEYS_TABLE.'` WHERE
		`acl_name` = \'pagegroupedit-'.$group_id.'\'';
	$del_acl_key_handle = $db->sql_query($del_acl_key_query);
	if ($db->error[$del_acl_key_handle] === 1) {
		$debug->add_trace('Failed to delete permission key',true);
		return 4;
	}

	// Delete group
	$del_group_query = 'DELETE FROM `'.PAGE_GROUP_TABLE.'` WHERE
		`id` = '.$group_id;
	$del_group_handle = $db->sql_query($del_group_query);
	if ($db->error[$del_group_handle] === 1) {
		return false;
	}
	$log->new_message('Deleted page group');
	return true;
}

function page_hide($id) {
	global $acl;
	global $db;
	global $debug;
	// Check for permission to execute
	if (!$acl->check_permission('page_hide')) {
		$debug->add_trace('Lacking permission to hide pages',true);
		return false;
	}
	// Validate parameters
	if (!is_int($id)) {
		$debug->add_trace('ID is not an integer',true);
		return false;
	}
	// Check if page exists
	$page_info = get_page_info($id,array('title','hidden'));
	if (!$page_info) {
		$debug->add_trace('Failed to retrieve page info',true);
		return false;
	}
	// Check if page is already hidden
	if ($page_info['hidden'] == 1) {
		$debug->add_trace('Page is already hidden',true);
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
 * @global db $db Database connection object
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
		$page_list[$i]['has_children'] = page_has_children($page_list[$i]['id'],$visible_only);
	}
	return $page_list;
}

/**
 * Test if there are any children to the given page
 * @global db $db Database connection object
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
 * @global db $db Database connection object
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
			// Only reorder if necessary
			if ($page_list['list'] != $i) {
				$move_page_query = 'UPDATE ' . PAGE_TABLE . '
					SET list = '.$i.' WHERE id = '.$page_list['id'];
				$move_page = $db->sql_query($move_page_query);
				if ($db->error[$move_page] === 1) {
					return false;
				}
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
 * @global object $acl Permission object
 * @global db $db Database connection object
 * @param integer $id Page ID to move up
 * @return boolean
 */
function page_move_up($id) {
	global $acl;
	global $db;

	if (!$acl->check_permission('page_order')) {
		return false;
	}
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
 * @global object $acl Permissions object
 * @global db $db Database connection object
 * @param integer $id Page ID to move down
 * @return boolean
 */
function page_move_down($id) {
	global $acl;
	global $db;

	if (!$acl->check_permission('page_order')) {
		return false;
	}
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

/**
 * set_home_page - Change the default CMS page
 * @global object $acl Permission object
 * @global db $db Database connection object
 * @global debug $debug Debugger object
 * @global Log $log Logging object
 * @param integer $id Page ID to set as the default page
 * @return boolean Success
 */
function page_set_home($id) {
	global $acl;
	global $db;
	global $debug;
	global $log;

	if (!$acl->check_permission('page_set_home')) {
		$debug->add_trace('Need permission \'page_set_home\' to perform this action',true);
		return false;
	}

	// Validate variables
	if (!is_numeric($id)) {
		$debug->add_trace('Page ID is not numeric',true);
		return false;
	}
	$id = (int)$id;

	$check_query = 'SELECT `id`,`title` FROM `'.PAGE_TABLE."`
		WHERE `id` = $id LIMIT 1";
	$check_handle = $db->sql_query($check_query);
	if ($db->error[$check_handle] === 1) {
		$debug->add_trace('Failed to read page table',true);
		return false;
	}
	if ($db->sql_num_rows($check_handle) != 1) {
		$debug->add_trace('Page does not exist',true);
		return false;
	}
	if(!set_config('home',$id)) {
		$debug->add_trace('Failed to set config value',true);
		return false;
	}
	$check_page = $db->sql_fetch_assoc($check_handle);
	$log->new_message('Set home page to \''.$check_page['title'].'\'');
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
	global $debug;
	global $page;

	// Don't execute this for special pages or non-existant pages
	if ((int)$page->id == 0 || $page->exists == false) {
		$debug->add_trace('Not generating page path',false);
		return false;
	}

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

function page_editable($page_id) {
	global $acl;
	global $debug;

	// Validate input
	if (!is_numeric($page_id)) {
		$debug->add_trace('Invalid page ID',true);
		return false;
	}
	$page_id = (int)$page_id;

	// First, check for permission to edit specific page group
	if ($acl->check_permission('pagegroupedit-'.$page_id) &&
		$acl->check_permission('page_edit')) {
		return true;
	}

	return false;
}

/**
 * page_group_news - Get the page group that a news article belongs to
 * @global db $db Database connection object
 * @param integer $article_id News article ID
 * @return mixed False (on fail) or a page group ID
 */
function page_group_news($article_id) {
	global $db;

	// Validate parameters
	if (!is_numeric($article_id)) {
		return false;
	}
	$article_id = (int)$article_id;

	// Find the correct page group
	$query = 'SELECT `page_group`.`id` FROM
		`'.PAGE_GROUP_TABLE.'` `page_group`,
		`'.PAGE_TABLE.'` `page`,
		`'.NEWS_TABLE.'` `news` WHERE
		`news`.`id` = '.$article_id.' AND
		`news`.`page` = `page`.`id` AND
		`page`.`page_group` = `page_group`.`id`';
	$handle = $db->sql_query($query);
	if ($db->sql_num_rows($handle) != 1) {
		return false;
	}
	$page_group = $db->sql_fetch_assoc($handle);
	$id = $page_group['id'];
	unset($query);
	unset($handle);
	unset($page_group);
	return $id;
}
?>
