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

// ----------------------------------------------------------------------------

// FIXME: This doesn't work yet
function monthcal_get_date($day,$month,$year,$template) {
	global $calendar_settings;
	global $db;
	global $debug;
	global $page;

	$dates_query = 'SELECT * FROM `' . CALENDAR_TABLE . '` `date`,
		`' . CALENDAR_CATEGORY_TABLE . '` `cat`
		WHERE `date`.`month` = \''.$month.'\' AND `date`.`year` = \''.$year.'\'
		AND `date`.`day` = \''.$day.'\' AND `date`.`category` =
		`cat`.`cat_id` ORDER BY `starttime` ASC';
	$dates_handle = $db->sql_query($dates_query);
	unset($dates_query);

	if ($db->error[$dates_handle] === 1) {
		$debug->add_trace('Failed to read date information',true,'monthcal_get_date');
		return 'Error';
	}
	if ($db->sql_num_rows($dates_handle) > 0) {
		$template->day_number = '<a href="?'.$page->url_reference
			.'&amp;view=day&amp;m='.$month.'&amp;y='.$year.'&amp;d='
			.$day.'" class="day_number">'.$day.'</a>';
	} else {
		$template->day_number = $day;
	}
	$dates = NULL;
	for ($i = 1; $i <= $db->sql_num_rows($dates_handle); $i++) {
		$day_info = $db->sql_fetch_assoc($dates_handle);
		if($day_info['colour'] == '') {
			$day_info['colour'] = 'red';
		}
		$dates .= '<a href="?'.$page->url_reference.'&amp;view=event&amp;'
			.'a='.$day_info['id'].'" class="calendar_event">';
		if ($calendar_settings['month_show_cat_icons'] == 1) {
			$dates .= '<img src="<!-- $IMAGE_PATH$ -->icon_'.$day_info['colour'].'.png"'
			.' width="10px" height="10px" alt="'.stripslashes($day_info['label']).'" border="0px" />';
		}
		if ($calendar_settings['month_show_stime'] == 1) {
			$stime_tmp = explode(':',$day_info['starttime']);
			$stime_tmp = mktime($stime_tmp[0],$stime_tmp[1]);
			$dates .= '<span class="calendar_event_starttime">'.date('g:ia',$stime_tmp).'</span> ';
		}
		$dates .= stripslashes($day_info['header']).'</a><br />'."\n";
	}
	$template->day_events = $dates;
	return (string)$template;
}

function monthcal_day_strings($template) {
	if (!is_object($template)) {
		return $template;
	}

	// Insert date labels
	// Settings:
	// calendar_month_day_format
	// 1 - Use full name
	// 0 - Use abbreviation
	if (get_config('calendar_month_day_format') == 1) {
		$template->cal_sunday = 'Sunday';
		$template->cal_monday = 'Monday';
		$template->cal_tuesday = 'Tuesday';
		$template->cal_wednesday = 'Wednesday';
		$template->cal_thursday = 'Thursday';
		$template->cal_friday = 'Friday';
		$template->cal_saturday = 'Saturday';
	} else {
		$template->cal_sunday = 'Sun';
		$template->cal_monday = 'Mon';
		$template->cal_tuesday = 'Tues';
		$template->cal_wednesday = 'Wed';
		$template->cal_thursday = 'Thurs';
		$template->cal_friday = 'Fri';
		$template->cal_saturday = 'Sat';
	}
	return $template;
}
?>
