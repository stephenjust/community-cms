<?php
/**
 * Community CMS
 * $Id$
 *
 * @copyright Copyright (C) 2007-2009 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.main
 */

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

	// Check if page exists, and read title for log message.
	$page_info_query = 'SELECT `title` FROM `' . PAGE_TABLE .'`
		WHERE `id` = '.$id.' LIMIT 1';
	$page_info_handle = $db->sql_query($page_info_query);
	if ($db->error[$page_info_handle] === 1) {
		$debug->add_trace('Failure to read page information',true,'page_delete');
		return false;
	}
	if ($db->sql_num_rows($page_info_handle) != 1) {
		$debug->add_trace('Page not found',true,'page_delete');
		return false;
	}
	$page_info = $db->sql_fetch_assoc($page_info_handle);

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

function page_hide() {
	// FIXME: Stub
}

function page_unhide() {
	// FIXME: Stub
	// Could this be done by page_hide()?
}
?>
