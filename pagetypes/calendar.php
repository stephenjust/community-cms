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
global $page;
global $page_content_info;
global $site_info;
$day = (isset($_GET['d']) && $_GET['d'] >= 1 && $_GET['d'] <= 31) ? (int)$_GET['d'] : date('d');
$month = (isset($_GET['m']) && $_GET['m'] >= 0 && $_GET['m'] <= 13) ? (int)$_GET['m'] : date('n');
$year = (isset($_GET['y']) && $_GET['y'] >= 2000 && $_GET['y'] <= 9999) ? (int)$_GET['y'] : date('Y');
include(ROOT . 'pagetypes/calendar_class.php');
include(ROOT . 'functions/calendar.php');
$calendar_settings = calendar_settings();
if (!isset($_GET['view'])) {
	$_GET['view'] = $calendar_settings['default_view'];
}
if ($_GET['view'] != 'month' && $_GET['view'] != 'day' && $_GET['view'] != 'event') {
	$_GET['view'] = $calendar_settings['default_view'];
}
switch ($_GET['view']) {
	// MONTH VIEW
	case "month":
		$month_cal = new calendar($month,$year);
		unset($month);
		unset($year);
		$page_content = NULL;
		$template_month = new template;
		$template_month->load_file('calendar_month');

		// Add month and year to page title
		global $special_title;
		$special_title = date('F Y',$month_cal->first_day_ts).' - ';

		// Replace template placeholders that should not be altered
		// beyond this point
		$template_month->current_month_name = date('F Y',$month_cal->first_day_ts);
		$template_month->current_month = $month_cal->month;
		$template_month->current_year = $month_cal->year;
		$template_month->prev_month = $month_cal->prev_month;
		$template_month->prev_year = $month_cal->prev_year;
		$template_month->next_month = $month_cal->next_month;
		$template_month->next_year = $month_cal->next_year;

		// Insert day names according to chosen format
		if ($calendar_settings['month_day_format'] == 1) {
			$template_month->cal_sunday = 'Sunday';
			$template_month->cal_monday = 'Monday';
			$template_month->cal_tuesday = 'Tuesday';
			$template_month->cal_wednesday = 'Wednesday';
			$template_month->cal_thursday = 'Thursday';
			$template_month->cal_friday = 'Friday';
			$template_month->cal_saturday = 'Saturday';
		} else {
			$template_month->cal_sunday = 'Sun';
			$template_month->cal_monday = 'Mon';
			$template_month->cal_tuesday = 'Tues';
			$template_month->cal_wednesday = 'Wed';
			$template_month->cal_thursday = 'Thurs';
			$template_month->cal_friday = 'Fri';
			$template_month->cal_saturday = 'Sat';
		}

		// Week template
		$template_week = new template;
		$template_week->path = $template_month->path;
		$template_week->template = $template_month->get_range('week');

		// Extract templates for each type of day
		$template_empty_day = $template_month->get_range('empty_day');
		$template_day = $template_month->get_range('day');
		$template_today = $template_month->get_range('today');

		// Remove day templates
		$template_week->replace_range('empty_day','');
		$template_week->replace_range('day','<!-- $DAY$ -->');
		$template_week->replace_range('today','');

// ----------------------------------------------------------------------------

		// Start drawing the calendar
		$counter_dow = 0; // Day of week in loop, 0=Sunday, 1=Monday...6=Saturday
		$all_weeks = NULL;
		for ($counter_day = 1; $counter_day <= $month_cal->month_days; $counter_day++) {
			if ($counter_dow == 0) { // If it's the first day of the week
				$current_week_days = NULL; // Clear the week.
			}
			while ($counter_dow < $month_cal->first_day_dow && $counter_day == 1) {
				$current_week_days .= $template_empty_day;
				// On the first day of the month, insert blank cells to
				// make sure that we start on the right day of the week
				$counter_dow++;
			}
			unset($current_day);
			$current_day = new template;
			$current_day->path = $template_week->path;
			if ($counter_day == date('j') && $month_cal->month == date('n') && $month_cal->year == date('Y')) {
				$current_day->template = $template_today;
			} else {
				$current_day->template = $template_day;
			}
//			$current_week_days .= monthcal_get_date($counter_day,$month_cal->month,$month_cal->year,$current_day);
			$day_info_query = 'SELECT * FROM ' . CALENDAR_TABLE . ' date,
				' . CALENDAR_CATEGORY_TABLE . ' cat
				WHERE date.month = \''.$month_cal->month.'\' AND date.year = \''.$month_cal->year.'\'
				AND date.day = \''.$counter_day.'\' AND date.category =
				cat.cat_id ORDER BY `starttime` ASC';
			$day_info_handle = $db->sql_query($day_info_query);
			if ($db->error[$day_info_handle] === 1) {
				$content .= 'Failed to read dates from database.<br />';
			}
			if ($db->sql_num_rows($day_info_handle) > 0) {
				$current_day->day_number = '<a href="?'.$page->url_reference
					.'&amp;view=day&amp;m='.$month_cal->month.'&amp;y='.$month_cal->year.'&amp;d='
					.$counter_day.'" class="day_number">'.$counter_day.'</a>';
			} else {
				$current_day->day_number = $counter_day;
			}
			$dates = NULL;
			for ($i = 1; $i <= $db->sql_num_rows($day_info_handle); $i++) {
				$day_info = $db->sql_fetch_assoc($day_info_handle);
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
			$current_day->day_events = $dates;
			$current_week_days .= $current_day->template;
			$counter_dow++;
			while ($counter_dow < 7 && $counter_day == $month_cal->month_days) { // At the end of the month,
				$current_week_days .= $template_empty_day;                 // fill any empty calendar cells with empty cells
				$counter_dow++;
			}
			if ($counter_dow == 7) { // When you reach the end of the week...
				$current_week = new template;
				$current_week->template = $template_week->template;
				$current_week->path = $template_week->path;
				$current_week->day = $current_week_days;
				$counter_dow = 0; // Set the day back to Sunday,
				$all_weeks .= $current_week->template; // Add the full week to the page.
				unset($current_week);
			}
			unset($current_day);
			unset($day_info);
		}
		$template_month->replace_range('week',$all_weeks);
		$page_content .= $template_month;
		break;

// ----------------------------------------------------------------------------

// EVENT VIEW
	case "event":
		$page_content = NULL;
		if (!isset($_GET['a'])) {
			$_GET['a'] = NULL;
		}
		$event_id = (int)$_GET['a'];
		$event = new calendar_event;
		$event->get_event($event_id);
		$page_content .= $event;
		unset($event);
		break;

// ----------------------------------------------------------------------------

// DAY VIEW
	case "day":
		if ($year < 2000 || $year > 9999) { $year = 2000; } // Validate month and year values
		if ($month < 1 || $month > 12) { $month = 1; }
		if ($day < 1 || $day > 31) { $day = 1; }
		$page_content = NULL;
		// Get events for current day from database
		$day_events_query = 'SELECT * FROM ' . CALENDAR_TABLE . '
			WHERE year = '.$year.' AND month = '.$month.' AND day = '.$day.'
			ORDER BY starttime ASC';
		$day_events_handle = $db->sql_query($day_events_query);
		$page_content .= '<a href="?'.$page->url_reference.'&amp;view=month&amp;m='.$month.
			'&amp;y='.$year.'">Back to month view</a><br />'."\n";
		if ($db->error[$day_events_handle] === 1) {
			$page_content .= 'Failed to read list of events from the database.';
			break;
		}
		if ($db->sql_num_rows($day_events_handle) == 0) {
			header('HTTP/1.1 404 Not Found');
			$page_content .= 'There are no events to display.';
			break;
		}
		$day_template = load_template_file('calendar_day.html');
		$day_template_temp = explode('<!-- $EVENT_START$ -->',$day_template['contents']);
		$day_template_head = $day_template_temp[0];
		$day_template_temp = explode('<!-- $EVENT_END$ -->',$day_template_temp[1]);
		$event_template = $day_template_temp[0];
		$day_template_foot = $day_template_temp[1];
		unset($day_template);
		unset($day_template_temp);
		$page_content .= $day_template_head;
		unset($day_template_head);
		for ($i = 1; $db->sql_num_rows($day_events_handle) >= $i; $i++) {
			$day_events = $db->sql_fetch_assoc($day_events_handle);
			$event_stime = explode(':',$day_events['starttime']);
			$event_etime = explode(':',$day_events['endtime']);
			$event_start = mktime($event_stime[0],$event_stime[1],0,$month,$day,$year);
			$event_end = mktime($event_etime[0],$event_etime[1],0,$month,$day,$year);
			if ($event_start == $event_end) {
				$event_time = 'All day';
			} else {
				global $site_info;
				$event_time = date($site_info['time_format'],$event_start).' - '.date($site_info['time_format'],$event_end);
			}
			$current_event = $event_template;
			$current_event = str_replace('<!-- $EVENT_ID$ -->',$day_events['id'],$current_event);
			$current_event = str_replace('<!-- $EVENT_TIME$ -->',$event_time,$current_event);
			$current_event = str_replace('<!-- $EVENT_HEADING$ -->',stripslashes($day_events['header']),$current_event);
			$current_event = str_replace('<!-- $EVENT_DESCRIPTION$ -->',stripslashes(truncate(strip_tags($day_events['description']),100)),$current_event);
			$page_content .= $current_event;
		}
		$month_temp = $event_start;
		$page_content .= $day_template_foot;
		unset($day_template_foot);
		unset($event_template);
		unset($day_events_query);
		unset($day_events_handle);
		unset($day_events);
		global $special_title;
		$month_text = date('F',$event_start);
		$special_title = $month_text.' '.$day.', '.$year.' - ';
		break;
}
return $page_content;
?>