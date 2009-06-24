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
	if (!is_int($id)) {
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
		if (eregi(' ',$fields[$i])) {
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

	$page_info = page_get_info($id,array('title',' ','hidden'));
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
	log_action('Deleted page \''.$page_info['title'].'\'');
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
?>
