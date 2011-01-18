<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2011 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.main
 */

/**
 * Delete a contact entry from the database
 * @global object $acl Permission object
 * @global db $db Database object
 * @global object $log Logger object
 * @param integer $id Contact ID
 * @return boolean Success
 */
function delete_contact($id) {
	global $acl;
	global $db;
	global $log;

	// Pre-execution checks
	if (!$acl->check_permission('contact_delete')) {
		return false;
	}
	if (!is_numeric($id)) {
		return false;
	}
	$id = (int)$id;

	// Get info for log message
	$get_info_query = 'SELECT * FROM `'.CONTACTS_TABLE.'` WHERE
		`id` = '.$id.' LIMIT 1';
	$get_contact_info_handle = $db->sql_query($get_info_query);
	if ($db->error[$get_contact_info_handle] === 1) {
		return false;
	}
	if ($db->sql_num_rows($get_contact_info_handle) != 1) {
		return false;
	} else {
		$contact_info = $db->sql_fetch_assoc($get_contact_info_handle);
		unset($get_info_query);
		unset($get_contact_info_handle);
	}
	// Delete record
	$delete_query = 'DELETE FROM `' . CONTACTS_TABLE . '`
		WHERE `id` = '.$id;
	$delete_contact = $db->sql_query($delete_query);
	if ($db->error[$delete_contact] === 1) {
		return false;
	}
	$log->new_message('Deleted contact \''.stripslashes($contact_info['name']).'\'');
	return true;
}

function contact_add_to_list($contact_id,$list_id) {
	global $acl;
	global $db;

	// Check permissions
	if (!$acl->check_permission('contacts_edit_lists')) {
		return false;
	}

	// Check for invalid parameters
	if (!is_numeric($contact_id) || !is_numeric($list_id)) {
		return false;
	}

	// TODO: Add contact to list, document function
}
?>
