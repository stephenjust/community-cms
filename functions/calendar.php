<?php
/**
 * Community CMS
 * $Id$
 * @copyright Copyright (C) 2007-2009 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.main
 */
// Security Check
if (@SECURITY != 1) {
	die ('You cannot access this page directly.');
}

/**
 * calendar_settings - Load calendar settings from the database
 * @global object $db
 * @global object $debug
 * @return array
 */
function calendar_settings() {
	global $db;
	global $debug;
	$query = 'SELECT * FROM `' . CALENDAR_SETTINGS_TABLE . '` LIMIT 1';
	$handle = $db->sql_query($query);
	if ($db->error[$handle] === 1) {
		$debug->add_trace('Failed to check calendar settings',true,'calendar_settings()');
		// Return default settings
		return array('default_view' => 'month',
					'month_show_stime' => 1,
					'month_show_cat_icons' => 1,
					'month_day_format' => 1);
	}
	if ($db->sql_num_rows($handle) != 1) {
		$debug->add_trace('No calendar settings. Please repair database.',true,'calendar_settings()');
		// Return default settings
		return array('default_view' => 'month',
					'month_show_stime' => 1,
					'month_show_cat_icons' => 1,
					'month_day_format' => 1);
	}
	return $db->sql_fetch_assoc($handle);
}

// ----------------------------------------------------------------------------

/**
 * delete_category - Delete a calendar category entry
 * @global object $db
 * @global object $debug
 * @param integer $id
 * @return boolean
 */
function delete_category($id) {
	global $db;
	global $debug;
	// Validate parameters
	if (!is_numeric($id)) {
		$debug->add_trace('Invalid ID given',true,'delete_category()');
		return false;
	}

	$check_if_last_query = 'SELECT * FROM `'.CALENDAR_CATEGORY_TABLE.'` LIMIT 2';
	$check_if_last_handle = $db->sql_query($check_if_last_query);
	if ($db->error[$check_if_last_handle] === 1) {
		$debug->add_trace('Failed to check if you are trying to delete the last category',false,'delete_category()');
		return false;
	}
	if ($db->sql_num_rows($check_if_last_handle) == 1) {
		$debug->add_trace('Cannot delete last entry',true,'delete_category()');
		return false;
	}

	$check_category_query = 'SELECT * FROM `'. CALENDAR_CATEGORY_TABLE .'`
		WHERE `cat_id` = '.$id.' LIMIT 1';
	$check_category_handle = $db->sql_query($check_category_query);
	if ($db->error[$check_category_handle] === 1) {
		$debug->add_trace('Failed to read category information. Does it exist?',false,'delete_category()');
		return false;
	}
	if ($db->sql_num_rows($check_category_handle) == 1) {
		$delete_category_query = 'DELETE FROM `'.CALENDAR_CATEGORY_TABLE.'`
			WHERE `cat_id` = '.$id;
		$delete_category = $db->sql_query($delete_category_query);
		if ($db->error[$delete_category] === 1) {
			$debug->add_trace('Failed to perform delete operation',true,'delete_category()');
			return false;
		} else {
			$check_category = $db->sql_fetch_assoc($check_category_handle);
			log_action('Deleted category \''.stripslashes($check_category['label']).'\'');
			return true;
		}
	} else {
		return false;
	}
}

// ----------------------------------------------------------------------------

/**
 * delete_date - Delete a calendar entry
 * @global object $db
 * @global object $debug
 * @param integer $id
 * @return boolean
 */
function delete_date($id) {
	global $db;
	global $debug;
	// Validate parameters
	if (!is_numeric($id)) {
		$debug->add_trace('Invalid ID given',true,'delete_date()');
		return false;
	}

	$read_date_info_query = 'SELECT * FROM ' . CALENDAR_TABLE . '
		WHERE `id` = '.$id;
	$read_date_info_handle = $db->sql_query($read_date_info_query);
	if ($db->error[$read_date_info_handle] === 1) {
		$debug->add_trace('Failed to read date information. Does it exist?',false,'delete_date()');
		return false;
	} else {
		$del_query = 'DELETE FROM ' . CALENDAR_TABLE . '
			WHERE `id` = '.$id;
		$del_handle = $db->sql_query($del_query);
		$read_date_info = $db->sql_fetch_assoc($read_date_info_handle);
		if ($db->error[$del_handle] === 1) {
			return false;
		} else {
			log_action('Deleted calendar date \''.$read_date_info['header'].'\'');
			return true;
		}
	}
}
?>
