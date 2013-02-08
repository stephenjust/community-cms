<?php
/**
 * Community CMS
 * @copyright Copyright (C) 2007-2013 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.main
 */
// Security Check
if (@SECURITY != 1) {
	die ('You cannot access this page directly.');
}

require_once(ROOT.'includes/content/CalLocation.class.php');

// ----------------------------------------------------------------------------

/**
 * Get information of an event
 * @global db $db
 * @param integer $id
 * @return array
 * @throws Exception 
 */
function event_get($id) {
	global $db;

	// Sanitize inputs
	$id = (int)$id;
	if ($id < 1)
		throw new Exception('An invalid event ID was given.');

	// Get event record
	$query = 'SELECT `id`,`category`,`category_hide`,`start`,`end`,`header`,
		`description`,`location`,`location_hide`,`author`,`image`,`hidden`
		FROM `'.CALENDAR_TABLE.'`
		WHERE `id` = '.$id.' LIMIT 1';
	$handle = $db->sql_query($query);
	if ($db->error[$handle] === 1)
		throw new Exception('Failed to retrieve calendar event.');
	if ($db->sql_num_rows($handle) == 0)
		throw new Exception('The requested event does not exist.');

	$event = $db->sql_fetch_assoc($handle);
	return $event;
}

// ----------------------------------------------------------------------------

// FIXME: This doesn't work yet
function monthcal_get_date($day,$month,$year,$template) {
	global $calendar_settings;
	global $db;
	global $debug;

	$dates_query = 'SELECT * FROM `' . CALENDAR_TABLE . '` `date`,
		`' . CALENDAR_CATEGORY_TABLE . '` `cat`
		WHERE `date`.`month` = \''.$month.'\' AND `date`.`year` = \''.$year.'\'
		AND `date`.`day` = \''.$day.'\' AND `date`.`category` =
		`cat`.`cat_id` ORDER BY `starttime` ASC';
	$dates_handle = $db->sql_query($dates_query);
	unset($dates_query);

	if ($db->error[$dates_handle] === 1) {
		$debug->addMessage('Failed to read date information',true);
		return 'Error';
	}
	if ($db->sql_num_rows($dates_handle) > 0) {
		$template->day_number = HTML::link('index.php?'.Page::$url_reference.'&view=day&m='.$month.'&y='.$year.'&d='.$day, $day, 'day_number');
	} else {
		$template->day_number = $day;
	}
	$dates = NULL;
	for ($i = 1; $i <= $db->sql_num_rows($dates_handle); $i++) {
		$day_info = $db->sql_fetch_assoc($dates_handle);
		if($day_info['colour'] == '') {
			$day_info['colour'] = 'red';
		}
		$dates .= '<a href="?'.Page::$url_reference.'&amp;view=event&amp;'
			.'a='.$day_info['id'].'" class="calendar_event">';
		if ($calendar_settings['month_show_cat_icons'] == 1) {
			$dates .= '<img src="<!-- $IMAGE_PATH$ -->icon_'.$day_info['colour'].'.png"'
			.' width="10px" height="10px" alt="'.$day_info['label'].'" border="0px" />';
		}
		if ($calendar_settings['month_show_stime'] == 1) {
			$stime_tmp = explode(':',$day_info['starttime']);
			$stime_tmp = mktime($stime_tmp[0],$stime_tmp[1]);
			$dates .= '<span class="calendar_event_starttime">'.date('g:ia',$stime_tmp).'</span> ';
		}
		$dates .= $day_info['header'].'</a><br />'."\n";
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
